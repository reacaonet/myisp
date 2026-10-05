<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Separa "ver planos" de " mexer em planos".
 *
 * O catalogo de planos e da rede: os 6 planos do banco estao gravados na
 * Matriz e valem para todas as lojas. Por isso `plans` continua concedido ao
 * franqueado, que precisa do plano para fechar contrato na propria filial.
 *
 * Alterar preco, velocidade ou disponibilidade do produto, esse e caso de
 * negocio da rede, nao da loja. A chave nova `plans_manage` cobre create, edit,
 * update e destroy, e fica com os grupos que ja administravam o catalogo.
 */
return new class extends Migration
{
    public function up(): void
    {
        $key = 'plans_manage';

        // Superadmin nao depende da tabela: User::hasPermission() sempre retorna
        // true para ele. Fica registrado mesmo assim, para a tela de grupos nao
        // mostrar a chave como nao concedida e persuasionar alguem a clicar nela.
        foreach (['superadmin', 'admin', 'gerente'] as $slug) {
            $groupId = DB::table('user_groups')->where('slug', $slug)->value('id');

            if ($groupId) {
                $this->put($key, (int) $groupId, true);
            }
        }

        // O grupo do franqueado mantem `plans` e ganha o `plans_manage` negado de
        // forma explicita: sem essa linha a ausencia da chave ja produz 403, mas
        // a intencao fica registrada e sobrevive a uma mudanca de perfil.
        $franqueados = DB::table('user_groups')->where('slug', 'franqueados')->value('id');

        if ($franqueados) {
            $this->put($key, (int) $franqueados, false);
        }

        // Qualquer outro grupo que administrava planos mantem o acesso.
        foreach (DB::table('group_permissions')->where('permission_key', 'plans')->where('granted', true)->get() as $permission) {
            if ((int) $permission->group_id === (int) $franqueados) {
                continue;
            }

            $this->put($key, (int) $permission->group_id, true);
        }
    }

    public function down(): void
    {
        DB::table('group_permissions')->where('permission_key', 'plans_manage')->delete();
    }

    private function put(string $key, int $groupId, bool $granted): void
    {
        DB::table('group_permissions')->updateOrInsert(
            ['group_id' => $groupId, 'permission_key' => $key],
            ['granted' => $granted, 'updated_at' => now(), 'created_at' => now()]
        );
    }
};
