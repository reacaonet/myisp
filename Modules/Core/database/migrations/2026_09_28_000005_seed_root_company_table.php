<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Models\SystemSetting;
use Modules\Core\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        if (Company::exists()) {
            return;
        }

        $keys = [
            'company_name',
            'company_fantasy',
            'company_document',
            'company_state_registration',
            'company_municipal_registration',
            'company_phone',
            'company_cellphone',
            'company_email',
            'company_website',
            'company_address',
            'company_city',
            'company_state',
            'company_zip',
        ];

        $settings = SystemSetting::whereIn('key', $keys)->pluck('value', 'key');

        $name = $settings['company_fantasy'] ?: ($settings['company_name'] ?: 'Franqueadora');
        $slug = Str::slug($name);
        if ($slug === '') {
            $slug = 'franqueadora';
        }
        $slug = Str::limit($slug, 60, '');

        $company = Company::create([
            'parent_id' => null,
            'name' => $name,
            'slug' => $slug,
            'code' => 'MAT',
            'is_active' => true,
            'is_franchise' => false,
            'document' => $settings['company_document'] ?? null,
            'state_registration' => $settings['company_state_registration'] ?? null,
            'municipal_registration' => $settings['company_municipal_registration'] ?? null,
            'phone' => $settings['company_phone'] ?? null,
            'cellphone' => $settings['company_cellphone'] ?? null,
            'email' => $settings['company_email'] ?? null,
            'website' => $settings['company_website'] ?? null,
            'address' => $settings['company_address'] ?? null,
            'city' => $settings['company_city'] ?? null,
            'state' => $settings['company_state'] ?? null,
            'zip' => $settings['company_zip'] ?? null,
        ]);

        $branch = Branch::create([
            'company_id' => $company->id,
            'parent_id' => null,
            'code' => 'MAT',
            'name' => 'Matriz',
            'is_active' => true,
        ]);

        $userIds = User::pluck('id');

        $company->users()->sync($userIds);
        $branch->users()->sync($userIds);
    }

    public function down(): void
    {
        Company::query()->delete();
    }
};
