<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `landing_banners` passa a ser escopada por empresa. `company_id` nulo
     * significa "banner da matriz": a marca da franqueadora, reutilizada
     * pelas franquias que nao definirem os seus.
     */
    public function up(): void
    {
        if (! Schema::hasTable('landing_banners')) {
            return;
        }

        if (! Schema::hasColumn('landing_banners', 'company_id')) {
            Schema::table('landing_banners', function (Blueprint $table) {
                $table->foreignId('company_id')->nullable()->after('id');
            });
        }

        if (Schema::hasTable('companies')) {
            $rootCompanyId = DB::table('companies')->whereNull('parent_id')->orderBy('id')->value('id');

            if ($rootCompanyId) {
                DB::table('landing_banners')->whereNull('company_id')->update(['company_id' => $rootCompanyId]);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('landing_banners') || ! Schema::hasColumn('landing_banners', 'company_id')) {
            return;
        }

        Schema::table('landing_banners', function (Blueprint $table) {
            $table->dropColumn('company_id');
        });
    }
};
