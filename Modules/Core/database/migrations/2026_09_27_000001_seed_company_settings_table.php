<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected function fields(): array
    {
        return [
            'company_name' => 'text',
            'company_fantasy' => 'text',
            'company_document' => 'text',
            'company_state_registration' => 'text',
            'company_municipal_registration' => 'text',
            'company_phone' => 'text',
            'company_cellphone' => 'text',
            'company_email' => 'text',
            'company_website' => 'text',
            'company_address' => 'text',
            'company_city' => 'text',
            'company_state' => 'text',
            'company_zip' => 'text',
        ];
    }

    public function up(): void
    {
        // Escrita pelo query builder, e nao pelo model: o hook `creating` do
        // SystemSetting resolve a empresa corrente via TenantContext, que
        // consulta a tabela `companies` — ela so e criada em 2026_09_28_000001,
        // depois desta migration. Numa instalacao nova isso quebrava com
        // `relation "companies" does not exist`.
        //
        // Estas linhas sao o template da raiz (company_id NULL, colonne criada
        // aditiva em 2026_09_28_000008), entao nao precisam de contexto de tenant.
        foreach ($this->fields() as $key => $type) {
            $this->seedSetting($key, '', $type, 'company');
        }
    }

    public function down(): void
    {
        DB::table('system_settings')
            ->where('group', 'company')
            ->whereIn('key', array_keys($this->fields()))
            ->delete();
    }

    /**
     * Cria a chave somente se ainda nao existir, preservando o valor de quem
     * ja configurou. Equivale ao firstOrCreate do model, sem passar por ele.
     */
    private function seedSetting(string $key, string $value, string $type, string $group): void
    {
        if (DB::table('system_settings')->where('key', $key)->exists()) {
            return;
        }

        DB::table('system_settings')->insert([
            'key' => $key,
            'value' => $value,
            'type' => $type,
            'group' => $group,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};