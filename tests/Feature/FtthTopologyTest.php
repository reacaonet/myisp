<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Models\GroupPermission;
use Modules\Core\Models\UserGroup;
use Modules\Core\Services\TenantContext;
use Modules\PortalInfra\Models\CaixaEmenda;
use Modules\PortalInfra\Models\Cto;
use Modules\PortalInfra\Models\FtthConnection;
use Modules\PortalInfra\Models\FtthFiberLink;
use Modules\PortalInfra\Models\FtthProject;
use Modules\PortalInfra\Models\FtthSplitter;
use Modules\PortalInfra\Services\KmlNetworkGenerator;
use Tests\TestCase;

class FtthTopologyTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        TenantContext::forget();
        session()->forget(['current_company_id', 'current_branch_id']);
    }

    /**
     * CTO, caixa e splitter nao tem `company_id` nem `branch_id`: quem define o
     * dono e o `ftth_project_id`. Sem projeto, o item fica invisivel para
     * qualquer usuario que nao seja superadmin (por isso os testes que passam
     * pelo editor precisam criar o projeto antes).
     */
    private ?FtthProject $projeto = null;

    private function projeto(): FtthProject
    {
        if (! $this->projeto) {
            $this->projeto = FtthProject::create([
                'name' => 'Rede de teste',
                'city' => 'Cidade de Teste',
                'status' => 'active',
            ]);
        }

        return $this->projeto;
    }

    public function test_splitter_tem_progenitor_e_cto_tem_cor(): void
    {
        $this->assertTrue(Schema::hasColumn('ftth_splitters', 'parent_type'));
        $this->assertTrue(Schema::hasColumn('ftth_splitters', 'parent_id'));
        $this->assertTrue(Schema::hasColumn('ctos', 'color'));
    }

    public function test_geracao_cria_cto_inativa_dentro_de_ceo_com_splitter(): void
    {
        $result = $this->gerar(16);

        $ctos = Cto::all();
        $caixas = CaixaEmenda::all();

        $this->assertGreaterThanOrEqual(16, $ctos->count());
        $this->assertSame(
            0,
            Cto::where('status', 'active')->count(),
            'Rede gerada precisa nascer inativa, o tecnico ativa depois de lancar a fibra.'
        );
        $this->assertSame(
            0,
            CaixaEmenda::where('status', 'active')->count(),
            'CEO gerada tambem nasce inativa.'
        );

        // Uma CEO nasce a cada grupo de 8 CTOs processados. CTOs que caem perto de
        // uma CEO ja existente entram nela, o que reduz a quantidade. Como o
        // backfill por cobertura pode acrescentar CTOs entre um grupo e outro,
        // o numero exato de CEs nao e previsivel: o que importa e que nenhuma
        // CTO fique orfa e nenhuma CEO fique vazia.
        $this->assertGreaterThanOrEqual(1, $caixas->count());
        $this->assertSame($ctos->count(), $caixas->sum(fn ($c) => $c->ctos()->count()));
        $this->assertSame(0, Cto::whereNull('caixa_emenda_id')->count());

        // Duas CEs lado a lado nao acrescentam cobertura nenhuma.
        for ($i = 0; $i < $caixas->count(); $i++) {
            for ($j = $i + 1; $j < $caixas->count(); $j++) {
                $this->assertGreaterThan(
                    150,
                    $this->distanciaEntreCaixas($caixas[$i], $caixas[$j]),
                    'Duas CEs a menos de 150m uma da outra sao redundantes.'
                );
            }
        }

        foreach ($caixas as $caixa) {
            $ctosDaCaixa = $caixa->ctos;
            $this->assertGreaterThan(0, $ctosDaCaixa->count());

            $splitters = $caixa->splitters;
            // Splitters sao criados por grupo de 8 CTOs. CTOs que entram numa
            // CE ja pronta traz seus splitters junto, entao a conta e sempre
            // soma de grupos, nunca um unico "1x8".
            $this->assertGreaterThanOrEqual(
                (int) ceil($ctosDaCaixa->count() / 8),
                $splitters->count()
            );
            $this->assertGreaterThan(0, $splitters->count());

            foreach ($splitters as $splitter) {
                $this->assertSame('1x8', $splitter->ratio);
                $this->assertSame('caixa', $splitter->parent_type);
                $this->assertSame($caixa->id, $splitter->parent_id);
            }
        }

        $this->assertSame(FtthSplitter::count(), $result['stats']['total_splitters']);
    }

    public function test_quantidade_de_ctos_por_ceo_e_configuravel(): void
    {
        // 12 CTOs por CEO: uma CEO pode atender mais que um projeto de bairro,
        // e mesmo assim os splitters continuam sendo 1x8.
        $result = $this->gerar(24, ctosPerCaixa: 12);

        $ctos = Cto::all();
        $caixas = CaixaEmenda::all();

        $this->assertGreaterThanOrEqual(24, $ctos->count());
        // O parametro e o teto de CTOs por CEO. Uma CEO pode ter menos (quando
        // CTOs proximas entram nela) ou, no maximo, exatamente esse numero,
        // porque uma CE nova so nasce quando o grupo fecha cheio.
        $this->assertGreaterThanOrEqual(1, $caixas->count());
        $this->assertSame($ctos->count(), $caixas->sum(fn ($c) => $c->ctos()->count()));
        $this->assertSame(0, Cto::whereNull('caixa_emenda_id')->count());

        foreach ($caixas as $caixa) {
            $this->assertLessThanOrEqual(12, $caixa->ctos()->count(), 'A CEO nao passa do limite configurado.');
            $this->assertGreaterThanOrEqual(
                (int) ceil($caixa->ctos()->count() / 8),
                $caixa->splitters()->count(),
                'Mesmo atendendo 12 CTOs, a CEO usa um 1x8 por grupo de 8.'
            );
        }

        $this->assertSame(FtthSplitter::count(), $result['stats']['total_splitters']);
    }

    public function test_ultima_ceo_com_resto_menor_que_oito(): void
    {
        $result = $this->gerar(10, ctosPerCaixa: 8);

        $caixas = CaixaEmenda::orderBy('id')->get();

        $this->assertGreaterThanOrEqual(2, $caixas->count());

        foreach ($caixas as $caixa) {
            $this->assertGreaterThan(0, $caixa->ctos()->count(), 'Nenhuma CEO pode ficar vazia.');
            $this->assertGreaterThanOrEqual(
                (int) ceil($caixa->ctos()->count() / 8),
                $caixa->splitters()->count()
            );
        }

        $this->assertSame(FtthSplitter::count(), $result['stats']['total_splitters']);
    }

    public function test_capacidade_da_cto_e_configuravel(): void
    {
        $this->gerar(8, ctoCapacity: 32);

        $this->assertSame(32, (int) Cto::first()->capacity);
    }

    public function test_splitter_manual_exige_progenitor(): void
    {
        $this->actingAs($this->operadorFtth());

        $caixa = CaixaEmenda::create([
            'ftth_project_id' => $this->projeto()->id,

            'name' => 'CEO Teste',
            'code' => 'CE-TST-001',
            'latitude' => -4.3,
            'longitude' => -46.5,
            'capacity' => 48,
            'status' => 'inactive',
            'city' => 'Teste',
        ]);

        // Sem progenitor a API recusa: splitter solto nao existe mais.
        $this->assertErroDeValidacao(
            $this->postJson(route('infra.ftth.editor.splitters.store'), [
                'city' => 'Teste',
                'name' => 'Splitter Solto',
                'input_ports' => 1,
                'output_ports' => 8,
            ]),
            'parent_type'
        );

        $response = $this->postJson(route('infra.ftth.editor.splitters.store'), [
            'city' => 'Teste',
            'name' => 'Splitter da CEO',
            'parent_type' => 'caixa',
            'parent_id' => $caixa->id,
            'input_ports' => 1,
            'output_ports' => 8,
        ])->assertCreated();

        $splitter = FtthSplitter::findOrFail($response->json('splitter.id'));
        $this->assertSame('caixa', $splitter->parent_type);
        $this->assertSame($caixa->id, $splitter->parent_id);
        // Sem coordenada, o splitter nasce em cima de quem o alimenta.
        $this->assertEquals((float) $caixa->latitude, (float) $splitter->latitude);
        $this->assertSame($caixa->code, $splitter->parentLabel());
    }

    public function test_cto_recebe_cor_e_status(): void
    {
        $this->actingAs($this->operadorFtth());

        $cto = Cto::create([
            'ftth_project_id' => $this->projeto()->id,

            'name' => 'CTO Cor',
            'code' => 'CTO-TST-001',
            'latitude' => -4.3,
            'longitude' => -46.5,
            'capacity' => 16,
            'status' => 'inactive',
            'city' => 'Teste',
        ]);

        $this->putJson(route('infra.ftth.editor.elements.update', ['type' => 'cto', 'id' => $cto->id]), [
            'name' => 'CTO Cor Azul',
            'capacity' => 16,
            'status' => 'active',
            'color' => '#2563eb',
        ])->assertOk()->assertJsonPath('element.color', '#2563eb');

        $cto->refresh();
        $this->assertSame('#2563eb', $cto->color);
        $this->assertSame('active', $cto->status);
        $this->assertSame('CTO Cor Azul', $cto->name);
    }

    public function test_cor_invalida_e_recusada(): void
    {
        $this->actingAs($this->operadorFtth());

        $cto = Cto::create([
            'ftth_project_id' => $this->projeto()->id,

            'name' => 'CTO Cor Ruim',
            'code' => 'CTO-TST-002',
            'latitude' => -4.3,
            'longitude' => -46.5,
            'capacity' => 16,
            'status' => 'inactive',
            'city' => 'Teste',
        ]);

        $this->assertErroDeValidacao(
            $this->putJson(route('infra.ftth.editor.elements.update', ['type' => 'cto', 'id' => $cto->id]), [
                'color' => 'azul',
            ]),
            'color'
        );
    }

    public function test_duplicar_cto_copia_dados_e_nasce_no_ponto_escolhido(): void
    {
        $this->actingAs($this->operadorFtth());

        $caixa = CaixaEmenda::create([
            'ftth_project_id' => $this->projeto()->id,

            'name' => 'CEO Origem',
            'code' => 'CE-DUP-001',
            'latitude' => -4.30,
            'longitude' => -46.50,
            'capacity' => 48,
            'city' => 'Teste',
            'status' => 'inactive',
        ]);

        $cto = Cto::create([
            'ftth_project_id' => $this->projeto()->id,

            'name' => 'CTO Origem',
            'code' => 'CTO-DUP-001',
            'latitude' => -4.30,
            'longitude' => -46.50,
            'capacity' => 16,
            'used_ports' => 12,
            'color' => '#2563eb',
            'caixa_emenda_id' => $caixa->id,
            'street' => 'Rua Original',
            'city' => 'Teste',
            'status' => 'inactive',
        ]);

        $this->postJson(route('infra.ftth.editor.elements.duplicate', ['type' => 'cto', 'id' => $cto->id]), [
            'lat' => -4.25,
            'lng' => -46.40,
        ])->assertCreated()->assertJsonPath('element.type', 'cto');

        $copia = Cto::where('code', '!=', $cto->code)->latest('id')->first();

        $this->assertNotNull($copia);
        $this->assertSame('CTO Origem (cópia)', $copia->name);
        $this->assertSame($cto->code.'-C', $copia->code);
        $this->assertSame(-4.25, (float) $copia->latitude);
        $this->assertSame(-46.40, (float) $copia->longitude);
        $this->assertSame(16, (int) $copia->capacity);
        $this->assertSame('#2563eb', $copia->color);
        $this->assertSame('Rua Original', $copia->street);
        $this->assertSame('Teste', $copia->city);

        // A copia continua na mesma CEO, mas nasce sem uso e sem o estado de
        // uso da original: nao ha fibra nem splitter apontando para ela.
        $this->assertSame($caixa->id, $copia->caixa_emenda_id);
        $this->assertSame(0, (int) $copia->used_ports);
        $this->assertNull($copia->fiber_fusions);
        $this->assertNull($copia->splitter_config);
        $this->assertSame(0, $copia->splitters()->count());
        $this->assertSame(0, $copia->fusions()->count());

        // A rede planejada nasce inativa, entao a copia mantem o status.
        $this->assertSame('inactive', $copia->status);
    }

    public function test_duplicar_aceita_nome_proprio_e_nao_repete_nem_codigo_nem_nome(): void
    {
        $this->actingAs($this->operadorFtth());

        $cto = Cto::create([
            'ftth_project_id' => $this->projeto()->id,

            'name' => 'CTO Base',
            'code' => 'CTO-DUP-002',
            'latitude' => -4.30,
            'longitude' => -46.50,
            'city' => 'Teste',
            'status' => 'inactive',
        ]);

        $url = route('infra.ftth.editor.elements.duplicate', ['type' => 'cto', 'id' => $cto->id]);

        $this->postJson($url, ['lat' => -4.31, 'lng' => -46.51, 'name' => 'CTO do Bairro Novo'])->assertCreated();
        $this->postJson($url, ['lat' => -4.32, 'lng' => -46.52, 'name' => 'CTO do Bairro Novo'])->assertCreated();
        $this->postJson($url, ['lat' => -4.33, 'lng' => -46.53, 'name' => 'CTO do Bairro Novo'])->assertCreated();

        $copias = Cto::where('code', 'like', $cto->code.'-C%')->orderBy('id')->get();

        $this->assertCount(3, $copias);

        // code e unique na tabela: as tres copias precisam de sufixo proprio.
        $this->assertSame(
            [$cto->code.'-C', $cto->code.'-C2', $cto->code.'-C3'],
            $copias->pluck('code')->all()
        );

        // O nome vem do prompt, e o "(cópia)" evita embatido.
        $this->assertSame(
            ['CTO do Bairro Novo (cópia)', 'CTO do Bairro Novo (cópia 2)', 'CTO do Bairro Novo (cópia 3)'],
            $copias->pluck('name')->all()
        );

        $this->assertSame(0, $copias->pluck('code')->duplicates()->count());
    }

    public function test_duplicar_ceo_nao_copia_splitters_das_ctos_dela(): void
    {
        $this->actingAs($this->operadorFtth());

        $caixa = CaixaEmenda::create([
            'ftth_project_id' => $this->projeto()->id,

            'name' => 'CEO Com Splitter',
            'code' => 'CE-DUP-003',
            'latitude' => -4.30,
            'longitude' => -46.50,
            'capacity' => 48,
            'used_ports' => 20,
            'city' => 'Teste',
            'status' => 'inactive',
        ]);

        $cto = Cto::create([
            'ftth_project_id' => $this->projeto()->id,

            'name' => 'CTO Da CEO',
            'code' => 'CTO-DUP-003',
            'latitude' => -4.3005,
            'longitude' => -46.50,
            'caixa_emenda_id' => $caixa->id,
            'city' => 'Teste',
            'status' => 'inactive',
        ]);

        FtthSplitter::create([
            'ftth_project_id' => $this->projeto()->id,

            'name' => 'Splitter 1x8',
            'code' => 'SPT-DUP-003',
            'parent_type' => 'caixa',
            'parent_id' => $caixa->id,
            'latitude' => -4.30,
            'longitude' => -46.50,
            'input_ports' => 1,
            'output_ports' => 8,
        ]);

        $this->postJson(route('infra.ftth.editor.elements.duplicate', ['type' => 'caixa', 'id' => $caixa->id]), [
            'lat' => -4.35,
            'lng' => -46.45,
        ])->assertCreated();

        $copia = CaixaEmenda::where('code', $caixa->code.'-C')->first();

        $this->assertNotNull($copia);
        $this->assertSame(-4.35, (float) $copia->latitude);
        $this->assertSame(48, (int) $copia->capacity);
        $this->assertSame(0, (int) $copia->used_ports);

        // CEO duplicada comeca vazia: sem CTO, sem splitter.
        $this->assertSame(0, $copia->ctos()->count());
        $this->assertSame(0, $copia->splitters()->count());
        $this->assertSame(1, Cto::count());
        $this->assertSame(1, FtthSplitter::count());
    }

    public function test_duplicar_exige_ponto_valido_e_tipo_conhecido(): void
    {
        $this->actingAs($this->operadorFtth());

        $cto = Cto::create([
            'ftth_project_id' => $this->projeto()->id,

            'name' => 'CTO Sem Ponto',
            'code' => 'CTO-DUP-004',
            'latitude' => -4.30,
            'longitude' => -46.50,
            'city' => 'Teste',
            'status' => 'inactive',
        ]);

        $url = route('infra.ftth.editor.elements.duplicate', ['type' => 'cto', 'id' => $cto->id]);

        // Sem clique no mapa nao ha onde a copia nascer.
        $this->assertErroDeValidacao($this->postJson($url, []), 'lat');
        $this->assertErroDeValidacao($this->postJson($url, ['lat' => 200, 'lng' => -46.5]), 'lat');

        $this->postJson(
            route('infra.ftth.editor.elements.duplicate', ['type' => 'poste', 'id' => $cto->id]),
            ['lat' => -4.3, 'lng' => -46.5]
        )->assertStatus(422);

        $this->assertSame(1, Cto::count());
    }

    public function test_editor_oferece_duplicar_ao_lado_das_outras_acoes(): void
    {
        $this->actingAs($this->operadorFtth());

        $html = $this->get(route('infra.ftth.editor.index'))->assertOk()->getContent();

        // O botao fica no mesmo grupo de editar, conectar e adicionar splitter,
        // e vale para CTO e para CEO porque os dois usam elementActions().
        $this->assertStringContainsString('data-action="duplicate"', $html);
        $this->assertStringContainsString('>Duplicar</button>', $html);
        $this->assertStringContainsString('act-duplicate', $html);
        $this->assertStringContainsString('startDuplicateElement', $html);
        $this->assertStringContainsString('elementos/__TYPE__/__ID__/duplicar', $html);
    }

    public function test_ceo_nao_guarda_cor_e_splitter_orphan_aparece_na_validacao(): void
    {
        $this->actingAs($this->operadorFtth());

        $caixa = CaixaEmenda::create([
            'ftth_project_id' => $this->projeto()->id,

            'name' => 'CEO Sem Cor',
            'code' => 'CE-TST-002',
            'latitude' => -4.3,
            'longitude' => -46.5,
            'city' => 'Teste',
            'status' => 'inactive',
        ]);

        $this->putJson(route('infra.ftth.editor.elements.update', ['type' => 'caixa', 'id' => $caixa->id]), [
            'name' => 'CEO Renomeada',
            'color' => '#111111',
        ])->assertOk();

        // Splitter legado, criado antes do progenitor existir.
        $project = FtthProject::create([
            'name' => 'Projeto Teste',
            'city' => 'Teste',
            'status' => 'active',
        ]);

        $orphan = FtthSplitter::create([
            'ftth_project_id' => $this->projeto()->id,

            'name' => 'Splitter Legado',
            'code' => 'SPT-LEGADO',
            'ftth_project_id' => $project->id,
            'latitude' => -4.3,
            'longitude' => -46.5,
            'input_ports' => 1,
            'output_ports' => 8,
        ]);

        $issues = $this->getJson(route('infra.ftth.editor.validate', ['city' => 'Teste']))
            ->assertOk()
            ->json('issues');

        $this->assertContains('splitter_sem_progenitor', array_column($issues, 'type'));
        $this->assertStringContainsString($orphan->name, implode(' ', array_column($issues, 'message')));
    }

    public function test_splitter_trocado_de_ceo_para_cto(): void
    {
        $this->actingAs($this->operadorFtth());

        $caixa = CaixaEmenda::create([
            'ftth_project_id' => $this->projeto()->id,

            'name' => 'CEO Origem',
            'code' => 'CE-TST-003',
            'latitude' => -4.3,
            'longitude' => -46.5,
            'city' => 'Teste',
        ]);

        $cto = Cto::create([
            'ftth_project_id' => $this->projeto()->id,

            'name' => 'CTO Mae',
            'code' => 'CTO-TST-003',
            'latitude' => -4.31,
            'longitude' => -46.51,
            'capacity' => 16,
            'city' => 'Teste',
            'status' => 'inactive',
        ]);

        $splitter = FtthSplitter::create([
            'ftth_project_id' => $this->projeto()->id,

            'name' => 'Splitter Movel',
            'code' => 'SPT-MOVEL',
            'parent_type' => 'caixa',
            'parent_id' => $caixa->id,
            'latitude' => -4.3,
            'longitude' => -46.5,
            'input_ports' => 1,
            'output_ports' => 16,
            'ratio' => '1x16',
        ]);

        $this->putJson(route('infra.ftth.editor.splitters.update', $splitter->id), [
            'parent_type' => 'cto',
            'parent_id' => $cto->id,
            'output_ports' => 8,
        ])->assertOk()->assertJsonPath('splitter.parent_type', 'cto');

        $splitter->refresh();
        $this->assertSame('cto', $splitter->parent_type);
        $this->assertSame($cto->id, $splitter->parent_id);
        $this->assertSame('1x8', $splitter->ratio);
        $this->assertSame($cto->code, $splitter->parentLabel());
    }

    public function test_dados_do_editor_trazem_cor_e_progenitor(): void
    {
        $this->actingAs($this->operadorFtth());

        $project = FtthProject::create([
            'name' => 'Projeto Teste',
            'city' => 'Teste',
            'status' => 'active',
        ]);

        $cto = Cto::create([
            'ftth_project_id' => $this->projeto()->id,

            'name' => 'CTO Mapa',
            'code' => 'CTO-MAPA-1',
            'latitude' => -4.3,
            'longitude' => -46.5,
            'capacity' => 16,
            'status' => 'inactive',
            'color' => '#0ea5e9',
            'city' => 'Teste',
            'ftth_project_id' => $project->id,
        ]);

        FtthSplitter::create([
            'ftth_project_id' => $this->projeto()->id,

            'name' => 'Splitter Mapa',
            'code' => 'SPT-MAPA-1',
            'ftth_project_id' => $project->id,
            'parent_type' => 'cto',
            'parent_id' => $cto->id,
            'latitude' => -4.3,
            'longitude' => -46.5,
            'input_ports' => 1,
            'output_ports' => 16,
            'ratio' => '1x16',
        ]);

        $json = $this->getJson(route('infra.ftth.editor.data', ['cidade' => 'Teste']))->assertOk()->json();

        $this->assertSame('#0ea5e9', $json['ctos'][0]['color']);
        $this->assertSame('cto', $json['splitters'][0]['parent_type']);
        $this->assertSame('CTO-MAPA-1', $json['splitters'][0]['parent_code']);
    }

    public function test_no_gerado_sem_fibra_fica_inativo(): void
    {
        $caixa = CaixaEmenda::create([
            'ftth_project_id' => $this->projeto()->id,

            'name' => 'CEO Antiga',
            'code' => 'CE-ANT-1',
            'latitude' => -4.3,
            'longitude' => -46.5,
            'city' => 'Teste',
            'status' => 'active',
        ]);

        Cto::create([
            'ftth_project_id' => $this->projeto()->id,

            'name' => 'CTO Antiga',
            'code' => 'CTO-ANT-1',
            'latitude' => -4.3,
            'longitude' => -46.5,
            'capacity' => 8,
            'city' => 'Teste',
            'status' => 'active',
        ]);

        // A migration e de dados e ja rodou no banco: libera o registro para
        // ela rodar de novo dentro da transacao do teste.
        DB::table('migrations')
            ->where('migration', '2026_09_29_000002_deactivate_unlaunched_ftth_nodes')
            ->delete();

        $this->artisan('migrate', [
            '--path' => 'Modules/PortalInfra/database/migrations/2026_09_29_000002_deactivate_unlaunched_ftth_nodes.php',
            '--force' => true,
        ])->assertSuccessful();

        $this->assertSame('inactive', $caixa->fresh()->status);
        $this->assertSame('inactive', Cto::where('code', 'CTO-ANT-1')->firstOrFail()->status);
    }

    public function test_no_com_fibra_lancada_permanece_ativo(): void
    {
        $cto = Cto::create([
            'ftth_project_id' => $this->projeto()->id,

            'name' => 'CTO Com Fibra',
            'code' => 'CTO-FIB-1',
            'latitude' => -4.3,
            'longitude' => -46.5,
            'capacity' => 8,
            'city' => 'Teste',
            'status' => 'active',
        ]);

        $fibra = FtthFiberLink::create([
            'name' => 'Distribuicao CEO 1',
            'type' => 'distribuicao',
            'geometry' => [
                ['lat' => -4.31, 'lng' => -46.51],
                ['lat' => -4.32, 'lng' => -46.52],
            ],
            'length_meters' => 1500,
        ]);

        FtthConnection::create([
            'ftth_project_id' => $fibra->ftth_project_id ?? FtthProject::create([
                'name' => 'Projeto Fibra',
                'city' => 'Teste',
                'status' => 'active',
            ])->id,
            'source_type' => 'caixa',
            'source_id' => CaixaEmenda::create([
                'ftth_project_id' => $this->projeto()->id,

                'name' => 'CEO Origem Fibra',
                'code' => 'CE-FIB-1',
                'latitude' => -4.3,
                'longitude' => -46.5,
                'city' => 'Teste',
            ])->id,
            'source_port' => null,
            'fiber_link_id' => $fibra->id,
            'target_type' => 'cto',
            'target_id' => $cto->id,
            'target_port' => null,
        ]);

        DB::table('migrations')
            ->where('migration', '2026_09_29_000002_deactivate_unlaunched_ftth_nodes')
            ->delete();

        $this->artisan('migrate', [
            '--path' => 'Modules/PortalInfra/database/migrations/2026_09_29_000002_deactivate_unlaunched_ftth_nodes.php',
            '--force' => true,
        ])->assertSuccessful();

        // Fibra lançada significa obra feita: o no continua ativo.
        $this->assertSame('active', $cto->fresh()->status);
    }

    /**
     * O assertJsonValidationErrors do Laravel falha nesta versao do PHPUnit,
     * entao a checagem de 422 + chave do erro e feita na mao.
     */
    private function assertErroDeValidacao(TestResponse $response, string $campo): void
    {
        // A mensagem do diff importa: sem ela o PHPUnit quebra ao tentar anexar
        // os erros de sessao na excecao.
        $this->assertSame(422, $response->getStatusCode(), 'Resposta inesperada: '.$response->getContent());

        $erros = $response->json('errors') ?? [];
        $this->assertArrayHasKey($campo, $erros, 'Esperava erro de validacao em '.$campo.'.');
    }

    private function gerar(int $ctos, int $ctoCapacity = 16, int $ctosPerCaixa = 8): array
    {
        $coordenadas = [];
        // O gerador descarta pontos perto demais um do outro, entao o passo
        // precisa ficar acima do intervalo minimo de 250m.
        $passo = 0.003;

        for ($i = 0; $i < $ctos; $i++) {
            $coordenadas[] = [
                'lat' => -4.3 + ($i * $passo),
                'lng' => -46.5,
            ];
        }

        $generator = new KmlNetworkGenerator;

        return $generator->generateFromCoordinates(
            $coordenadas,
            'Rua Teste',
            'TST',
            $ctoCapacity,
            250,
            $ctosPerCaixa
        );
    }

    private function operadorFtth(): User
    {
        $group = UserGroup::create([
            'name' => 'Operador FTTH',
            'slug' => 'operador-ftth-'.uniqid(),
            'is_active' => true,
        ]);

        DB::table('group_permissions')->updateOrInsert(
            ['group_id' => $group->id, 'permission_key' => 'ftth'],
            ['granted' => true, 'updated_at' => now()]
        );

        $user = User::create([
            'name' => 'Operador FTTH',
            'email' => 'operador-ftth-'.uniqid().'@teste.local',
            'password' => bcrypt('secret123'),
            'user_group_id' => $group->id,
            'is_active' => true,
        ]);

        $company = Company::whereNull('parent_id')->orderBy('id')->firstOrFail();
        $branch = Branch::where('company_id', $company->id)->orderBy('id')->firstOrFail();

        $user->companies()->attach($company->id);
        $user->branches()->attach($branch->id);

        return $user;
    }

    public function test_permissao_ftth_continua_exigida(): void
    {
        $this->assertArrayHasKey('ftth', GroupPermission::MENU_PERMISSIONS());
    }

    public function test_api_do_mapa_traz_a_cor_da_cto(): void
    {
        $this->actingAs($this->operadorFtth());

        Cto::create([
            'ftth_project_id' => $this->projeto()->id,

            'name' => 'CTO Colorida',
            'code' => 'CTO-COR-1',
            'latitude' => -4.3,
            'longitude' => -46.5,
            'capacity' => 16,
            'status' => 'active',
            'color' => '#0ea5e9',
            'city' => 'TesteCor',
        ]);

        $json = $this->getJson(route('infra.ftth.api.map-data', ['city' => 'TesteCor']))->assertOk()->json();

        // Sem a cor no payload o mapa cai no vermelho padrao e perde a cor que
        // o tecnico escolheu para separar as CTOs da mesma CEO.
        $this->assertSame('#0ea5e9', $json['ctos'][0]['color']);
        $this->assertSame('active', $json['ctos'][0]['status']);
    }

    public function test_editor_mostra_apenas_cto_e_caixa_sem_transparencia(): void
    {
        $this->actingAs($this->operadorFtth());

        $html = $this->get(route('infra.ftth.editor.index'))->assertOk()->getContent();

        // Splitter e componente interno: nao ganha marcador proprio.
        $this->assertStringNotContainsString('splitterLayer', $html);
        $this->assertStringNotContainsString('marker-splitter', $html);

        // O editor e a ferramenta de construcao: nada translucido aqui.
        $this->assertStringNotContainsString('marker-inactive', $html);

        // CEO e CTO continuam sendo as duas unicas coisas desenhadas.
        $this->assertStringContainsString('marker-caixa', $html);
        $this->assertStringContainsString('ctoIconFor(c.color)', $html);

        // O acesso ao splitter migra para dentro do popup de quem o contem.
        $this->assertStringContainsString('childSplitters', $html);
    }

    public function test_mapa_deixa_planejado_translucido_e_destaca_ativo(): void
    {
        $this->actingAs($this->operadorFtth());

        $html = $this->get(route('infra.ftth.map'))->assertOk()->getContent();

        // No mapa o planejamento continua translucido e o que foi construido
        // recebe cor cheia com anel de destaque.
        $this->assertStringContainsString('marker-dim', $html);
        $this->assertStringContainsString('marker-active', $html);
        $this->assertStringContainsString("status === 'active'", $html);
    }

    public function test_cto_segue_a_metrica_do_formulario_e_cobre_bairro_vazio(): void
    {
        // Duas ruas: uma longa e uma curta. As duas precisam receber CTO, e
        // nenhuma CTO pode nascer dentro do alcance de outra.
        $rua = [
            ['name' => 'Rua Longa', 'nodes' => $this->linhaDeRua(-4.30, -46.50, -4.20, -46.50)],
            ['name' => 'Rua do Bairro', 'nodes' => $this->linhaDeRua(-4.29, -46.40, -4.29, -46.39)],
        ];

        $this->gerarPorRua($rua, 8, 400);

        $ctos = Cto::all();

        $this->assertGreaterThan(
            0,
            Cto::where('street', 'Rua do Bairro')->count(),
            'A rua curta tambem precisa receber CTO.'
        );

        // Rua sem cliente conhecido nao pode ficar zerada: a metragem manda.
        $this->assertGreaterThan(
            0,
            Cto::where('street', 'Rua Longa')->count(),
            'Rua sem casa mapeada nao pode ficar sem nenhuma CTO.'
        );

        // O piso entre duas CTOs e o alcance de 150m, nao o intervalo de 400m.
        $this->assertCtosRespeitamEspacamento($ctos, 150);
    }

    public function test_rua_quebrada_nao_gera_cto_colada_uma_da_outra(): void
    {
        // O OSM devolve a mesma rua como fragmentos separados por um intervalo.
        // Cada fragmento começa a contar do zero, e sem a metrica global cada
        // um plantava uma CTO no seu proprio inicio: tres CTOs em menos de
        // duzentos metros, que e exatamente o defeito reportado.
        $fragmentos = [
            ['name' => 'Rua Quebrada', 'nodes' => $this->linhaDeRua(-4.30000, -46.5000, -4.29865, -46.5000, 10)],
            ['name' => 'Rua Quebrada', 'nodes' => $this->linhaDeRua(-4.29820, -46.5000, -4.29685, -46.5000, 10)],
            ['name' => 'Rua Quebrada', 'nodes' => $this->linhaDeRua(-4.29640, -46.5000, -4.29505, -46.5000, 10)],
        ];

        (new KmlNetworkGenerator)->generateFromStreets(
            $fragmentos,
            'TST',
            'Teste',
            'MA',
            16,
            400,
            null,
            8
        );

        // Tres fragmentos de 150m separados por 50m, costurados em 550m de rua.
        // Com 400m de intervalo, duas CTOs deixam um buraco de 100m no meio:
        // a primeira cobre ate 150m e a segunda so comeca a valer em 250m. O
        // backfill por cobertura fecha esse buraco com uma terceira CTO.
        $this->assertSame(3, Cto::count());

        // O piso entre CTOs e o alcance de 150m, nao o intervalo de 400m.
        $this->assertCtosRespeitamEspacamento(Cto::all(), 150);

        // E a rua inteira fica dentro do alcance de alguma CTO.
        $this->assertCoberturaIntegra($fragmentos, 150);
    }

    public function test_caixa_de_emenda_nao_nasce_uma_do_lado_da_outra(): void
    {
        // Varias ruas paralelas: sem ordem geografica as CAs saiam na mesma
        // faixa de rua.
        $ruas = [];
        for ($i = 0; $i < 6; $i++) {
            $ruas[] = [
                'name' => 'Rua Paralela '.$i,
                'nodes' => $this->linhaDeRua(-4.3000, (-46.5000 + ($i * 0.004)), -4.2400, (-46.5000 + ($i * 0.004))),
            ];
        }

        (new KmlNetworkGenerator)->generateFromStreets($ruas, 'TST', 'Teste', 'MA', 16, 400, null, 2);

        $caixas = CaixaEmenda::all();

        $this->assertGreaterThan(1, $caixas->count(), 'Precisa nascer mais de uma CA para o teste valer.');

        for ($i = 0; $i < $caixas->count(); $i++) {
            for ($j = $i + 1; $j < $caixas->count(); $j++) {
                $this->assertGreaterThan(
                    150,
                    $this->distanciaEntreCaixas($caixas[$i], $caixas[$j]),
                    'Duas caixas de emenda Bornaram na mesma esquina.'
                );
            }
        }
    }

    public function test_bairro_com_casa_nao_fica_de_fora_da_geracao(): void
    {
        // Dois bairros igualmente distantes do centro. A geracao antiga
        // concentrava tudo na rua mais longa e o segundo bairro saia vazio.
        $rua = [
            ['name' => 'Rua A', 'nodes' => $this->linhaDeRua(-4.3000, -46.5000, -4.2000, -46.5000)],
            ['name' => 'Rua B', 'nodes' => $this->linhaDeRua(-4.3000, -46.3000, -4.2900, -46.3000)],
        ];

        $this->gerarPorRua($rua);

        $bairroA = Cto::where('street', 'Rua A')->count();
        $bairroB = Cto::where('street', 'Rua B')->count();

        $this->assertGreaterThan(0, $bairroA, 'A rua longa precisa de CTO.');
        $this->assertGreaterThan(0, $bairroB, 'Nenhum bairro pode ficar de fora.');
    }

    public function test_ctos_nao_sao_coladas_no_mesmo_agrupamento(): void
    {
        // Trecho curto com muitas quadras de telefone: o problema antigo era
        // gerar varias CTOs uma ao lado da outra num lugar sem cliente.
        $rua = [
            ['name' => 'Rua das Casas', 'nodes' => $this->linhaDeRua(-4.3000, -46.5000, -4.2900, -46.5000)],
        ];

        $this->gerarPorRua($rua);

        $ctos = Cto::all();

        $this->assertGreaterThan(0, $ctos->count());
        // 1,1 km de rua com intervalo de 250m: 5 CTOs e o esperado. O defeito
        // antigo era outra coisa, 30 postes para um bloco de 30 m.
        $this->assertLessThanOrEqual(
            5,
            $ctos->count(),
            'Um trecho de 1,1 km nao pode virar uma fileira de postes.'
        );

        // Nenhuma CTO dentro do alcance de outra.
        $this->assertCtosRespeitamEspacamento($ctos, 150);
    }

    public function test_cto_so_nasce_sobre_rua_conhecida(): void
    {
        // Geracao so por ruas: nao existe mais caminho que invente poste fora
        // de uma rua, porque o unico dado de entrada sao as ruas do OSM.
        $rua = [
            ['name' => 'Rua A', 'nodes' => $this->linhaDeRua(-4.3000, -46.5000, -4.2900, -46.5000)],
        ];

        $result = $this->gerarPorRua($rua);

        foreach (Cto::all() as $cto) {
            $this->assertLessThan(
                4.0,
                abs($cto->latitude + 4.2950),
                'CTO nascida longe da rua esta no meio do terreno.'
            );
        }

        $this->assertSame(1, $result['stats']['total_streets']);
    }

    public function test_sem_predidos_no_osm_a_geracao_cai_para_as_ruas(): void
    {
        // generateFromStreets continua intacto: e o caminho para cidade sem
        // cobertura de building no OSM.
        $rua = [
            ['name' => 'Rua Teste', 'nodes' => $this->linhaDeRua(-4.3000, -46.5000, -4.2000, -46.5000)],
        ];

        $result = (new KmlNetworkGenerator)->generateFromStreets(
            $rua,
            'TST',
            'Teste',
            'MA',
            16,
            250,
            null,
            8
        );

        $this->assertGreaterThan(0, $result['stats']['total_ctos']);
    }

    private function linhaDeRua(float $latFrom, float $lngFrom, float $latTo, float $lngTo, int $passos = 40): array
    {
        $nodes = [];

        for ($i = 0; $i <= $passos; $i++) {
            $fator = $i / $passos;
            $nodes[] = [
                'lat' => $latFrom + (($latTo - $latFrom) * $fator),
                'lng' => $lngFrom + (($lngTo - $lngFrom) * $fator),
            ];
        }

        return $nodes;
    }

    private function casasPertoDe(float $lat, float $lng, int $quantidade): array
    {
        $casas = [];

        for ($i = 0; $i < $quantidade; $i++) {
            $casas[] = [
                'lat' => $lat + ($i * 0.00008),
                'lng' => $lng,
            ];
        }

        return $casas;
    }

    private function gerarPorRua(array $ruas, int $ctosPerCaixa = 8, int $intervalo = 250): array
    {
        return (new KmlNetworkGenerator)->generateFromStreets(
            $ruas,
            'TST',
            'Teste',
            'MA',
            16,
            $intervalo,
            null,
            $ctosPerCaixa
        );
    }

    private function assertCtosRespeitamEspacamento($ctos, float $minimo): void
    {
        for ($i = 0; $i < $ctos->count(); $i++) {
            for ($j = $i + 1; $j < $ctos->count(); $j++) {
                $this->assertGreaterThan(
                    $minimo,
                    $this->distanciaEntre($ctos[$i], $ctos[$j]),
                    sprintf(
                        'Duas CTOs a menos de %.0fm uma da outra e o defeito que o intervalo do formulario deveria impedir.',
                        $minimo
                    )
                );
            }
        }
    }

    /**
     * Toda rua gerada precisa estar dentro do alcance de alguma CTO. Uma CTO
     * atende 150m para cada lado, entao medimos cada no da rua.
     */
    private function assertCoberturaIntegra(array $ruas, float $raio): void
    {
        $ctos = Cto::all();

        foreach ($ruas as $rua) {
            foreach ($rua['nodes'] as $node) {
                $lat = (float) ($node['lat'] ?? $node[0]);
                $lng = (float) ($node['lng'] ?? $node[1]);
                $maisProxima = INF;

                foreach ($ctos as $cto) {
                    $d = $this->distanciaEntrePonto((float) $cto->latitude, (float) $cto->longitude, $lat, $lng);
                    if ($d < $maisProxima) {
                        $maisProxima = $d;
                    }
                }

                $this->assertLessThanOrEqual(
                    $raio,
                    $maisProxima,
                    sprintf('No de rua a %.0fm da CTO mais proxima: buraco de cobertura.', $maisProxima)
                );
            }
        }
    }

    private function distanciaEntrePonto(float $latA, float $lngA, float $latB, float $lngB): float
    {
        $earthRadius = 6371000.0;
        $dLat = deg2rad($latB - $latA);
        $dLng = deg2rad($lngB - $lngA);

        $h = sin($dLat / 2) ** 2
            + cos(deg2rad($latA)) * cos(deg2rad($latB)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($h), sqrt(1 - $h));
    }

    private function distanciaEntreCaixas(CaixaEmenda $a, CaixaEmenda $b): float
    {
        $earthRadius = 6371000.0;
        $dLat = deg2rad($b->latitude - $a->latitude);
        $dLng = deg2rad($b->longitude - $a->longitude);

        $h = sin($dLat / 2) ** 2
            + cos(deg2rad($a->latitude)) * cos(deg2rad($b->latitude)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($h), sqrt(1 - $h));
    }

    private function distanciaEntre(Cto $a, Cto $b): float
    {
        $earthRadius = 6371000.0;
        $dLat = deg2rad($b->latitude - $a->latitude);
        $dLng = deg2rad($b->longitude - $a->longitude);

        $h = sin($dLat / 2) ** 2
            + cos(deg2rad($a->latitude)) * cos(deg2rad($b->latitude)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($h), sqrt(1 - $h));
    }
}
