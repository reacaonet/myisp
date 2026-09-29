<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;

return new class extends Migration
{
    public function up(): void
    {
        $root = Company::query()->whereNull('parent_id')->orderBy('id')->first();
        if (! $root) {
            return;
        }

        $matrix = Branch::query()->where('company_id', $root->id)->whereNull('parent_id')->orderBy('id')->first();
        $matrixId = $matrix?->id;

        $addScopeColumns = function (string $table, bool $withBranch = true) {
            Schema::table($table, function (Blueprint $t) use ($withBranch) {
                $t->foreignId('company_id')->nullable()->after('id')->constrained('companies');

                if ($withBranch) {
                    $t->foreignId('branch_id')->nullable()->after('company_id')->constrained('branches');
                    $t->index(['company_id', 'branch_id'], "{$t->getTable()}_company_branch_index");
                } else {
                    $t->index('company_id', "{$t->getTable()}_company_index");
                }
            });
        };

        // --- Tables raiz com company_id + branch_id ---
        $addScopeColumns('clients');
        $addScopeColumns('plans');
        $addScopeColumns('mikrotik_servers');
        $addScopeColumns('invoices');
        $addScopeColumns('stock_locations');
        $addScopeColumns('olts');
        $addScopeColumns('ftth_projects');
        $addScopeColumns('cash_book_entries');

        // --- payment_gateways e stock_items: apenas company_id ---
        $addScopeColumns('payment_gateways', withBranch: false);
        $addScopeColumns('stock_items', withBranch: false);

        // --- Backfill: tudo para a raiz + matriz ---
        foreach (['clients', 'plans', 'mikrotik_servers', 'invoices', 'stock_locations', 'olts', 'ftth_projects', 'cash_book_entries'] as $table) {
            DB::table($table)->update(['company_id' => $root->id, 'branch_id' => $matrixId]);
        }

        foreach (['payment_gateways', 'stock_items'] as $table) {
            DB::table($table)->update(['company_id' => $root->id]);
        }

        // --- NOT NULL onde o plano exige (clientes e depositos) ---
        DB::statement('ALTER TABLE "clients" ALTER COLUMN "company_id" SET NOT NULL');
        DB::statement('ALTER TABLE "clients" ALTER COLUMN "branch_id" SET NOT NULL');
        DB::statement('ALTER TABLE "stock_locations" ALTER COLUMN "company_id" SET NOT NULL');
        DB::statement('ALTER TABLE "stock_locations" ALTER COLUMN "branch_id" SET NOT NULL');

        // --- Uniques compostas (substituem as globais) ---
        $dropUnique = function (string $table, string $constraint) {
            DB::statement('ALTER TABLE "'.$table.'" DROP CONSTRAINT IF EXISTS "'.$constraint.'"');
            DB::statement('DROP INDEX IF EXISTS "'.$constraint.'"');
        };

        $dropUnique('clients', 'clients_document_unique');
        $dropUnique('clients', 'clients_login_unique');
        $dropUnique('invoices', 'invoices_invoice_number_unique');
        $dropUnique('plans', 'plans_slug_unique');
        $dropUnique('payment_gateways', 'payment_gateways_slug_unique');
        $dropUnique('stock_items', 'stock_items_sku_unique');

        Schema::table('clients', function (Blueprint $t) {
            $t->unique(['company_id', 'branch_id', 'document'], 'clients_company_branch_document_unique');
            $t->unique(['company_id', 'branch_id', 'login'], 'clients_company_branch_login_unique');
        });

        Schema::table('invoices', function (Blueprint $t) {
            $t->unique(['company_id', 'invoice_number'], 'invoices_company_number_unique');
        });

        Schema::table('plans', function (Blueprint $t) {
            $t->unique(['company_id', 'slug'], 'plans_company_slug_unique');
        });

        Schema::table('payment_gateways', function (Blueprint $t) {
            $t->unique(['company_id', 'slug'], 'payment_gateways_company_slug_unique');
        });

        Schema::table('stock_items', function (Blueprint $t) {
            $t->unique(['company_id', 'sku'], 'stock_items_company_sku_unique');
        });
    }

    public function down(): void
    {
        foreach (['clients', 'plans', 'mikrotik_servers', 'invoices', 'stock_locations', 'olts', 'ftth_projects', 'cash_book_entries', 'payment_gateways', 'stock_items'] as $table) {
            DB::statement('ALTER TABLE "'.$table.'" DROP COLUMN IF EXISTS "branch_id", DROP COLUMN IF EXISTS "company_id" CASCADE');
        }
    }
};
