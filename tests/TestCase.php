<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Services\PublicTenantResolver;
use Modules\Core\Services\TenantContext;

abstract class TestCase extends BaseTestCase
{
    // A suite roda no Postgres `myisp_test`. Sem esta trait cada teste enxergaria
    // o que o anterior deixou no banco e as colunas unicas passariam a falhar.
    // Ela roda `migrate:fresh` uma vez e envolve cada teste em uma transacao,
    // entao nenhum teste ve os dados de outro.
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // O contexto de tenant guarda o resultado em estatico para nao resolver
        // a cada consulta. No processo unico do PHPUnit esse estatico atravessa
        // a fronteira entre testes: sem esquecer aqui, o teste seguinte herda a
        // empresa/filial do teste anterior e falha de forma dependente de ordem.
        TenantContext::forget();
        PublicTenantResolver::forget();
    }
}
