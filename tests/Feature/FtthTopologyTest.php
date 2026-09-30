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

        // Uma CEO para cada grupo de 8 CTOs, com um splitter 1x8 dentro dela.
        $this->assertSame((int) ceil($ctos->count() / 8), $caixas->count());
        $this->assertSame($caixas->count(), FtthSplitter::count());

        foreach ($caixas as $caixa) {
            $ctosDaCaixa = $caixa->ctos;
            $this->assertLessThanOrEqual(8, $ctosDaCaixa->count(), 'A CEO atende no maximo 8 CTOs.');
            $this->assertGreaterThan(0, $ctosDaCaixa->count());

            $splitters = $caixa->splitters;
            $this->assertCount((int) ceil($ctosDaCaixa->count() / 8), $splitters, 'A CEO concentra um 1x8 por grupo de 8 CTOs.');

            foreach ($splitters as $splitter) {
                $this->assertSame('1x8', $splitter->ratio);
                $this->assertSame('caixa', $splitter->parent_type);
                $this->assertSame($caixa->id, $splitter->parent_id);
            }
        }

        $this->assertSame($caixas->count(), $result['stats']['total_splitters']);
    }

    public function test_quantidade_de_ctos_por_ceo_e_configuravel(): void
    {
        // 12 CTOs por CEO: uma CEO pode atender mais que um projeto de bairro,
        // e mesmo assim os splitters continuam sendo 1x8.
        $result = $this->gerar(24, ctosPerCaixa: 12);

        $ctos = Cto::all();
        $caixas = CaixaEmenda::all();

        $this->assertGreaterThanOrEqual(24, $ctos->count());
        $this->assertSame((int) ceil($ctos->count() / 12), $caixas->count());

        foreach ($caixas as $caixa) {
            $this->assertLessThanOrEqual(12, $caixa->ctos()->count());
            $this->assertSame(
                (int) ceil($caixa->ctos()->count() / 8),
                $caixa->splitters()->count(),
                'Mesmo atendendo 12 CTOs, a CEO usa um 1x8 por grupo de 8.'
            );
        }

        $this->assertSame(
            $caixas->sum(fn ($caixa) => (int) ceil($caixa->ctos()->count() / 8)),
            $result['stats']['total_splitters']
        );
    }

    public function test_ultima_ceo_com_resto_menor_que_oito(): void
    {
        $result = $this->gerar(10, ctosPerCaixa: 8);

        $caixas = CaixaEmenda::orderBy('id')->get();

        $this->assertGreaterThanOrEqual(2, $caixas->count());

        $ultima = $caixas->last();
        $this->assertLessThan(8, $ultima->ctos()->count(), 'A ultima CEO fica com o resto.');
        $this->assertSame(1, $ultima->splitters()->count());
        $this->assertSame('1x8', $ultima->splitters()->first()->ratio);
        $this->assertSame($caixas->count(), $result['stats']['total_splitters']);
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

    public function test_ceo_nao_guarda_cor_e_splitter_orphan_aparece_na_validacao(): void
    {
        $this->actingAs($this->operadorFtth());

        $caixa = CaixaEmenda::create([
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
            'name' => 'CEO Origem',
            'code' => 'CE-TST-003',
            'latitude' => -4.3,
            'longitude' => -46.5,
            'city' => 'Teste',
        ]);

        $cto = Cto::create([
            'name' => 'CTO Mae',
            'code' => 'CTO-TST-003',
            'latitude' => -4.31,
            'longitude' => -46.51,
            'capacity' => 16,
            'city' => 'Teste',
            'status' => 'inactive',
        ]);

        $splitter = FtthSplitter::create([
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
            'name' => 'CEO Antiga',
            'code' => 'CE-ANT-1',
            'latitude' => -4.3,
            'longitude' => -46.5,
            'city' => 'Teste',
            'status' => 'active',
        ]);

        Cto::create([
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
        // Duas ruas: uma longa e sem casa, outra curta com uma quadra de
        // casas. As duas precisam receber CTO, e nenhuma CTO pode nascer fora
        // da metrica pedida no formulario.
        $rua = [
            ['name' => 'Rua Longa', 'nodes' => $this->linhaDeRua(-4.30, -46.50, -4.20, -46.50)],
            ['name' => 'Rua do Bairro', 'nodes' => $this->linhaDeRua(-4.29, -46.40, -4.29, -46.39)],
        ];

        $casas = $this->casasPertoDe(-4.29, -46.395, 3);

        $this->gerarPorDemanda($rua, $casas, 8, 400);

        $ctos = Cto::all();

        $this->assertGreaterThan(
            0,
            Cto::where('street', 'Rua do Bairro')->count(),
            'A quadra com casa tem que receber CTO.'
        );

        // Bairro sem casa nao pode ficar zerado: rua longa ganha cobertura.
        $this->assertGreaterThan(
            0,
            Cto::where('street', 'Rua Longa')->count(),
            'Rua sem casa nao pode ficar sem nenhuma CTO.'
        );

        // A metrica do formulario manda: com 400m, nenhuma CTO pode nascer
        // colada a outra.
        $this->assertCtosRespeitamEspacamento($ctos, 400 * 0.85);
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

        // Tres fragmentos de 150m separados por 50m. Cada fragmento comeca a
        // contar do zero e plantava uma CTO no seu inicio, entao o codigo
        // antigo saia com tres CTOs a cada 200m. Com 400m de intervalo sobra
        // uma no comeco e outra no fim.
        $this->assertSame(2, Cto::count());
        $this->assertCtosRespeitamEspacamento(Cto::all(), 400 * 0.85);
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

        $casas = array_merge(
            $this->casasPertoDe(-4.2500, -46.4998, 4),
            $this->casasPertoDe(-4.2950, -46.3000, 4)
        );

        $this->gerarPorDemanda($rua, $casas);

        $bairroA = Cto::where('street', 'Rua A')->count();
        $bairroB = Cto::where('street', 'Rua B')->count();

        $this->assertGreaterThan(0, $bairroA, 'O bairro com casas precisa de CTO.');
        $this->assertGreaterThan(0, $bairroB, 'Nenhum bairro com casa pode ficar de fora.');
    }

    public function test_ctos_nao_sao_coladas_no_mesmo_agrupamento(): void
    {
        $rua = [
            ['name' => 'Rua das Casas', 'nodes' => $this->linhaDeRua(-4.3000, -46.5000, -4.2900, -46.5000)],
        ];

        // 30 casas praticamente no mesmo ponto: o problema antigo era gerar
        // varias CTOs uma ao lado da outra em um lugar sem cliente.
        $casas = [];
        for ($i = 0; $i < 30; $i++) {
            $casas[] = ['lat' => -4.2950 + ($i * 0.00001), 'lng' => -46.5000];
        }

        $this->gerarPorDemanda($rua, $casas);

        $ctos = Cto::all();

        $this->assertGreaterThan(0, $ctos->count());
        $this->assertLessThanOrEqual(
            3,
            $ctos->count(),
            '30 casas no mesmo ponto nao podem virar uma fileira de CTOs.'
        );

        // Nenhuma CTO pode ficar a menos de 100m da outra.
        for ($i = 0; $i < $ctos->count(); $i++) {
            for ($j = $i + 1; $j < $ctos->count(); $j++) {
                $this->assertGreaterThan(
                    100,
                    $this->distanciaEntre($ctos[$i], $ctos[$j]),
                    'Duas CTOs coladas sao exatamente o que a geracao precisa evitar.'
                );
            }
        }
    }

    public function test_casa_sem_rua_perto_nao_gera_cto_no_terreno(): void
    {
        // Rua no bairro A e casa isolada a kilometres de qualquer rua.
        $rua = [
            ['name' => 'Rua A', 'nodes' => $this->linhaDeRua(-4.3000, -46.5000, -4.2900, -46.5000)],
        ];

        $casas = array_merge(
            $this->casasPertoDe(-4.2950, -46.5000, 3),
            [['lat' => -4.0000, 'lng' => -46.0000]]
        );

        $result = $this->gerarPorDemanda($rua, $casas);

        foreach (Cto::all() as $cto) {
            $this->assertLessThan(
                4.0,
                abs($cto->latitude + 4.2950),
                'CTO nascida longe da rua e no meio do terreno.'
            );
        }

        $this->assertSame(1, $result['stats']['skipped_no_street'], 'A casa sem rua precisa ser descartada, nao inventada.');
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

    private function gerarPorDemanda(array $ruas, array $casas, int $ctosPerCaixa = 8, int $intervalo = 250): array
    {
        return (new KmlNetworkGenerator)->generateFromDemand(
            $casas,
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
