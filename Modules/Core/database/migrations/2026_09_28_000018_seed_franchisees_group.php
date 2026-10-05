<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Cria o grupo global de franqueados.
 *
 * O grupo e unico na plataforma: o que separa uma franquia da outra e o vinculo
 * do usuario com a empresa em company_user, resolvido pelo TenantContext. Por
 * isso este grupo concede apenas o painel do franqueado, e nada de configuracoes
 * ou gestao de usuarios.
 */
return new class extends Migration
{
    private const SLUG = 'franqueados';

    /**
     * Apenas o painel. A permissao `clients` NAO entra aqui de proposito: ela
     * libera o resource inteiro do CRM (criar, editar, excluir), e o franqueado
     * so precisa ler os dados da propria empresa, o que o painel ja faz.
     * Quem quiser o atalho para a tela completa concede `clients` na mao.
     */
    private const GRANTS = ['franchisees'];

    public function up(): void
    {
        // Idempotente: um grupo homonimo criado a mao na tela de grupos nao
        // pode derrubar a migration em uma instalacao que ja existe.
        $groupId = DB::table('user_groups')->where('slug', self::SLUG)->value('id');

        if (! $groupId) {
            $groupId = DB::table('user_groups')->insertGetId([
                'name' => 'Franqueados',
                'slug' => self::SLUG,
                'description' => 'Acesso ao painel da propria franquia. O escopo de dados vem do vinculo com a empresa.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach (self::GRANTS as $key) {
            // Garante o grant padrao sem tocar no que o superadmin ja ajustou
            // a mao: se a permissao existe, ela e preservada como esta.
            $exists = DB::table('group_permissions')
                ->where('group_id', $groupId)
                ->where('permission_key', $key)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('group_permissions')->insert([
                'group_id' => $groupId,
                'permission_key' => $key,
                'granted' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $groupId = DB::table('user_groups')->where('slug', self::SLUG)->value('id');

        if (! $groupId) {
            return;
        }

        DB::table('group_permissions')->where('group_id', $groupId)->delete();
        DB::table('user_groups')->where('id', $groupId)->delete();
    }
};