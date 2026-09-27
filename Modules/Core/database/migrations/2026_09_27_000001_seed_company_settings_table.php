<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Models\SystemSetting;

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
        foreach ($this->fields() as $key => $type) {
            SystemSetting::firstOrCreate(
                ['key' => $key],
                ['value' => '', 'type' => $type, 'group' => 'company']
            );
        }
    }

    public function down(): void
    {
        SystemSetting::where('group', 'company')
            ->whereIn('key', array_keys($this->fields()))
            ->delete();
    }
};