# Plano - Suporte MultiFilial e Franquias (myisp)

> Status: **planejamento** (nada de codigo aplicado ainda)
> Decisoes de negocio ja conversadas com o cliente.

## Contexto

O sistema hoje foi feito para **1 provedor de internet** (uma empresa, uma operacao).
Nao existe nenhuma coluna de filial/empresa em qualquer tabela (varredura por
`company_id|filial|branch_id|empresa_id|unit_id` = zero hits estruturais).

Alem de filiais da propria empresa, ha demanda de **franquias**: outras pessoas
operam a mesma plataforma como provedores independentes, usando a marca.

## Conceitos (triplo nivel)

```
FRANQUEADORA (root / dono do sistema)
   |-- COMPANIA (franquia / tenant)  -> isolamento duro (multi-tenant)
        |   |--> BRANCH (filial)     -> escopo operacional dentro da compania
        |   `--> PLANOS, CLIENTES, MIKROTIKS, FATURAS, ...
        `-- FRANQUIA 2 (outro tenant)
```

| Nivel | O que e | Isolamento |
|---|---|---|
| **Tenant (franquia/compania)** | Empresa que opera o provedor. A franqueadora e o tenant raiz; franquias = tenants filhos | Duro: cada franquia ve SO os seus dados |
| **Branch (filial)** | Unidade operacional DENTRO de uma compania (mesma empresa, pode compartilhar CNPJ) | Suave: operador da compania pode ver 1..N filiais |
| **Dados** | Planos, clientes, contratos, faturamento, rede, estoque, FTTH | Escopados por tenant; opcionalmente por branch |

## Decisoes de negocio (definidas)

1. **Fiscal flexivel**: filial/franquia pode ter CNPJ proprio ou compartilhar o da
   empresa/raiz. Campos fiscais _nullable_ com **heranca na cadeia** (filha -> pai -> raiz).
2. **Cliente por filial**: cada cliente pertence a UMA filial de uma compania.
   Uniques compostas: `(company_id, branch_id, document)` e `(company_id, branch_id, login)`.
3. **Operador multi-filiais**: usuario atende 1..N filiais da MESMA compania
   (pivot `branch_user`), com `default_branch_id`, global scope e seletor de filial.
4. **Franquias multi-tenant com visao do franqueador**: cada franquia = tenant
   isolado na mesma instalacao. A franqueadora (superadmin) ve todas as franquias
   (cross-tenant), as franquias NAO veem umas as outras.
5. **Marca unica, personalizavel**: todas as franquias usam a marca/landing padrao
   da franqueadora, personalizavel por franquia (conteudo/base herda da raiz).

## Estado atual que bloqueia multifilial/franquia

- `system_settings.key` e UNIQUE global (razao social/CNPJ/landing em uma chave so).
  `BillingSetting::get()` e `static::first()` singleton.
- Uniques globais que quebram com 2+ filiais/franquias: `clients.login`,
  `clients.document`, `invoices.invoice_number`, `users.email` (email OK: operador global).
- Permissoes binarias (grupo -> permissao de menu bool), SEM escopo de dados.
- Dashboards agregam tudo: receita consolidada, CPU/memoria de TODOS os Mikrotiks,
  sem nenhum filtro.
- `Contract::provisionedMikrotikServer()` faz fallback **por IP** (IP overlap entre
  filiais resolve pra filial errada). `plans`/`contracts` apontam para DUAS tabelas
  de rede: `servers` (legada) e `mikrotik_servers`.
- `provisioning_records` nao tem `contract_id` (ambiguidade cliente x filial).
- Boleto/portal leem `company_name`/`company_document` de setting **global**.
- Seguranca: `group.permission` protege so `usuarios`/`grupos` (routes/core/web.php);
  os demais modulos usam apenas `auth` — o gating de menu e so "esconder link".
- NAO existe nenhum `addGlobalScope`/`booted()` em `Modules/` (escopo de tenant
  tera de ser criado do zero).

## Arquitetura proposta

### 1. Tabelas de estrutura

**`companies` (tenant / franquia)**
- `id`, `parent_id` (FK -> companies, nullable; raiz = franqueadora), `name`,
  `slug` (unique por nivel), `code`, `is_active`, `is_franchise`, timestamps.
- Campos fiscais nullable (herda de `parent_id`): `document`, `state_registration`,
  `municipal_registration`, `phone`, `cellphone`, `email`, `website`, `address`,
  `city`, `state`, `zip`.
- Razao de "fiscal flexivel": preenchido na franquia, senao herda do pai (cadeia ate a raiz).

**`branches` (filial dentro da compania)**
- `id`, `company_id` FK, `parent_id` (nullable, = matriz daquela compania), `code`,
  `name`, `is_active`, timestamps.
- Sem dados fiscais proprios quando a compania usa CNPJ unico (herda via compania).

### 2. Escopo (dois global scopes)

- **Scope de tenant (`company_id`)**: isolamento duro. Contexto =
  `Auth::user()->companies()` via pivot `company_user`. Superadmin da franqueadora
  ve todas as franquias.
- **Scope de branch (`branch_id`)**: so dentro das filiais da compania do usuario.
  Operador pode ver 1..N filiais (pivot `branch_user`).
- Cache das filiais/franquias do usuario (evitar N queries por request).

### 3. Tabelas que ganham escopo

| Tabela | `company_id` | `branch_id` | Comportamento |
|---|---|---|---|
| `clients` | obrigatorio | obrigatorio | Uniques compostas `(company_id, branch_id, document)` e `(company_id, branch_id, login)` |
| `plans` | obrigatorio | nullable (null = vale a compania toda) | Catalogo por franquia |
| `mikrotik_servers` | obrigatorio | nullable (null = equipamento da matriz) | — |
| `invoices` | obrigatorio | preenchido na geracao (herda contrato/cliente) | `invoice_number` unique composta `(company_id, invoice_number)` |
| `payment_gateways` | obrigatorio | — | Cada franquia tem seus gateways |
| `system_settings` | obrigatorio (null = template da raiz) | — | Landing/brand por franquia com heranca da raiz |
| `billing_settings` | obrigatorio | — | Regras de bloqueio por franquia, fallback pra raiz |
| `stock_locations` | obrigatorio | obrigatorio | Deposito/filial (ja e multi-local por design) |
| `olts` | obrigatorio | nullable | — |
| `ftth_projects` | obrigatorio | nullable (agrega city/state) | Base do inventario FTTH |
| `cash_book_entries` | obrigatorio | deriva da invoice | — |
| `users` | via pivot `company_user` | via pivot `branch_user`; `default_branch_id` | Operador pode ver varias filiais/companias |

Contratos, ordens de servico, tickets, payments: derivam a filial/compania do
`client_id` (sem duplicar escopo).

### 4. Settings e marca

- Campos `company_*` **migram do `system_settings` para colunas de `companies`**.
  Boleto/portal leem a franquia do contrato (com heranca de pai ate a raiz).
- `system_settings` ganha `company_id` para dados globais por franquia
  (landing, paginas de bloqueio/aviso, portal) com **template herdado da raiz**
  (marca unica personalizavel): franquia so sobrescreve o que quiser.
- `BillingSetting::get()` vira contextual por compania, com fallback para a raiz.

### 5. Acesso e permissao

- Pivot `company_user` (multi-tenant) + pivot `branch_user` (filiais).
- `users.default_branch_id` e `users.default_company_id`.
- Global scopes (tenant + branch) + seletor de filial/franquia no header.
- Superadmin da franqueadora: acesso cross-tenant (dashboard consolidado da rede).
- **Pre-requisito de seguranca**: aplicar `group.permission` nas rotas de TODOS
  os modulos (hoje so `usuarios`/`grupos`), para o escopo nao ser contornado
  digitando URL.

### 6. Rede / provisioning

- Consolidar `servers` -> `mikrotik_servers` (ou mapear a relacao).
- Adicionar `contract_id` em `provisioning_records` (remove a ambiguidade
  cliente x contrato x filial).
- Eliminar o fallback por IP (`provisionedMikrotikServer`) ou escopa-lo por tenant.
- Scoping nos ~15 controllers do PortalInfra (`MikrotikServer::findOrFail($id)`
  deve validar a compania/filial do usuario).

### 7. Franquias (operacional)

- Cadastro de franquia: cria `companies` (filha da raiz), branch "Matriz" da franquia,
  clona settings/brand base da raiz como defaults (personalizavel).
- Convite do primeiro administrador da franquia (user + acesso `company_user`).
- Cobranca/licenca de franquia fica para fase futura (fora do escopo atual;
  deixar campo de "plano/licenca" reservado em `companies`).

### 8. Landing page (marca unica) - navegacao por publico

A landing hoje tem menu fixo: `SAC`, `Planos`, `VOD Stream`, `Cobertura`, `Sobre`
(`core::landing.partials.header`). Com franquias, o menu passa a ser por **publico**:

- **Para Voce** (residencial): Planos, VOD Stream, Cobertura, SAC — conteudo atual
  agrupado sob esse publico (sub-menu / ancoras `#planos`, `#vod`, `#cobertura`).
- **Para Sua Empresa** (empresarial): secao dedicada com planos empresariais e
  contato comercial. `plans` ganha `segment` (`residencial`/`empresarial`) para
  filtrar a exibicao por publico (catalogo continua unico por filial).
- **Investidores** (franquias): pagina dedicada `GET /investidores`
  (`LandingController@investors` + view `landing.investors`, mesmo padrao da `landing.sac`),
  com conteudo editavel via settings do grupo `landing_investors_*`:
  - hero + beneficios de abrir franquia, investimento estimado/retorno (ROI),
    modelo de negocio, passo a passo, depoimentos, CTA de contato com a franqueadora.
  - Em rede de franquias, o conteudo **herda da raiz** (marca unica personalizavel):
    a franquia pode sobrescrever o proprio convite de franqueados, se quiser.

Destaques de implementacao:
- header/rodape: menu com os 3 publicos + sub-menus (e manter "Area do Cliente").
- novas chaves de settings (grupo `landing`/`landing_investors`) com seeds, labels,
  editor de texto rico (padrao Quill existente) e link "Ver pagina" nas configuracoes.
- rota `landing.investors` publica (mesma regra `landing_enabled` das demais).

## Fases de implementacao

### Fase 0 - Fundacao (structure)
- Migration `create_companies_table` + `create_branches_table` (hierarquia + fiscais).
- Migration `create_company_user` + `create_branch_user` (pivots).
- Migration retrofit: cria a **Franqueadora** (a partir dos settings `company_*`
  atuais) e sua **Matriz**; aponta os dados existentes para elas. Backup antes.
- CRUD de companias (franquias) e filiais (com heranca de dados fiscais).
- Seletor de filial/franquia no header.

### Fase 1 - Scoping por tenant + filial
- `company_id` + `branch_id` + FK + indice nas tabelas da secao 3.
- Uniques compostas (clients, invoices).
- Numeracao independente de fatura por compania.
- Global scopes (tenant + branch) nos models principais.
- Filtro dos dashboards (CRM + PortalInfra) por compania/filial.
- Dashboard consolidado da franqueadora (cross-tenant, superadmin).

### Fase 2 - Settings e marca
- Migrar `company_*` para `companies`; dados fiscais da franquia no boleto/portal.
- `system_settings.company_id` + template herdado da raiz (brand unica personalizavel).
- `billing_settings.company_id` + `BillingSetting::get()` contextual.
- Pagina de configuracoes por franquia (landing global-basico, novos campos por franquia).
- Landing por publico: menu "Para Voce" / "Para Sua Empresa" / "Investidores",
  secao empresarial (`plans.segment`) e pagina dedicada `landing.investors`
  (settings `landing_investors_*`, rota publica, herdada da raiz).

### Fase 3 - Acesso / permissoes
- Pivots `company_user`/`branch_user`, `default_company_id`/`default_branch_id`.
- Aplicar `group.permission` nas rotas de todos os modulos (seguranca).
- Cadastro de franquia + convite do admin (bonus de onbarding).
- Seletor de filial/franquia consolidado no header.

### Fase 4 - Rede / provisioning
- Consolidar `servers` -> `mikrotik_servers`.
- `contract_id` em `provisioning_records`.
- Remover/escapular fallback por IP.
- Scoping dos controllers do PortalInfra.

### Fase 5 - Franquias (futuro)
- Reservar em `companies`: campos de plano/licenca/vencimento (ex.: `plan_slug`,
  `trial_ends_at`, `subscription_status`) para cobranca de franquias.

## Riscos / atencao

- `system_settings.key` UNIQUE: fazer unique composta `(company_id, key)` ou migrar
  tudo para o template da raiz; nao misturar chaves globais com por-franquia.
- `users.email` UNIQUE: operador e global mesmo em multi-tenant (OK, nao muda).
- Ordem das migrations: retrofit antes de qualquer uso de `company_id`.
- Backup do banco antes das migrations de retrofit/unique.
- Global scopes precisam de cache de companias/filiais do usuario (nao N queries).
- Inconsistencia: escopo de fatura/contrato SEMPRE derivado do cliente, nunca editado
  livremente.
- Fallback fiscal e de brand deve ser resolvido numa unica funcao (ex.:
  `Company::fiscal($key)` com memoizacao), para nao espalhar logica.
- Superadmin cross-tenant deve ser opt-in explicito (nao vazamento acidental entre
  franquias).