# Auditoria de escopo por tenant — controllers ainda sem filtro

Levantamento feito em 29/09/2026, depois de fechar os vazamentos de `landing_banners`,
`plans` (web) e da resolucao publica por host. Cobre os models que tem `company_id`
(18 tabelas) e os controllers que os acessam.

## Resumo

- O padrao dominante nao e "nada escopado": e **lista escopada, item nao escopado**.
  As queries de index/create ja usam `forCompany()`/`scoped()`, mas o acesso ao
  registro por id continua global. Quem tem a permissao do modulo e digita a URL
  alheia alcanca o registro.
- Tres controllers nao tem escopo nem na listagem (Prioridade 1).
- Models sem `company_id` nao entram no escopo por coluna; herdarem do cliente e o
  caminho, entao o filtro do pai precisa estar certo (secao "Fora de escopo").

Ja fechado nesta serie (sem acesso por id sem escopo): `LandingBanner`, `Plan`
(controller web), `MikrotikServer`, `HotspotCoupon`, `UptimeMonitor`, `MikrotikBackup`.

## Prioridade 1 — APIs sem escopo algum

| Arquivo | Linha | Problema |
|---|---|---|
| `Modules/CRM/app/Http/Controllers/Api/PlanController.php` | 12 | `Plan::paginate()` sem filtro: qualquer usuario com a permissao `plans` le todos os planos de todas as franchises. `store` tambem nao valida tenant. |
| `Modules/Billing/app/Http/Controllers/Api/PaymentController.php` | 13 | `Payment::with('invoice.client')->paginate()` cruza a rede inteira (valores + clientes). `store` aceita `invoice_id` de qualquer empresa e ainda marca a fatura como paga. |
| `Modules/Billing/app/Http/Controllers/Api/InvoiceController.php` | 51, 77 | `show`/`update` por id sem filtro. |

Rotas: `routes/api.php` de cada modulo, todas com `group.permission` — ou seja, a
permissao existe, o filtro nao.

## Prioridade 2 — Financeiro (web)

| Arquivo | Linhas | Observacao |
|---|---|---|
| `Modules/Billing/app/Http/Controllers/Web/InvoiceController.php` | 122, 155, 165, 217, 281 | show/edit/update/destroy globais (index ja e escopado). |
| `Modules/Billing/app/Http/Controllers/Web/BoletoController.php` | 118, 146, 174, 199, 236 | Pior caso: a fatura e achada sem filtro, mas o gateway e filtrado por `$invoice->company_id`. A fatura alheia aparece e ainda pode ser paga pelo fluxo normal. |
| `Modules/Billing/app/Http/Controllers/Web/CashBookController.php` | 98, 105, 126 | Lancamentos de outra empresa. |
| `Modules/Billing/app/Http/Controllers/Web/PaymentGatewayController.php` | 65, 72, 105, 119 | Credenciais (segredos) de gateway de outra empresa. |

## Prioridade 3 — CRM e estoque

| Arquivo | Linhas |
|---|---|
| `Modules/CRM/app/Http/Controllers/Web/ClientController.php` | 117, 184 |
| `Modules/CRM/app/Http/Controllers/Api/ClientController.php` | 57, 81, 89 |
| `Modules/CRM/app/Http/Controllers/Web/StockItemController.php` | 75, 83, 102 |
| `Modules/CRM/app/Http/Controllers/Web/StockLocationController.php` | 65, 73, 93 |
| `Modules/CRM/app/Http/Controllers/Api/PlanController.php` | 49, 78 |

O ultimo item e a mesma correcao de 2 linhas que ja foi validada no controller web
(`Plan::findScopedOrFail`) — o `Plan::scoped()` ja existe no model.

## Prioridade 4 — PortalInfra

| Arquivo | Linhas | Observacao |
|---|---|---|
| `Modules/PortalInfra/app/Http/Controllers/Web/OltController.php` | 59, 66, 99 | OLT de outra empresa. |
| `Modules/PortalInfra/app/Http/Controllers/Web/FtthController.php` | 406, 413, 432 | `FtthProject` de outra empresa. |
| `Modules/PortalInfra/app/Http/Controllers/Web/ProvisionController.php` | 120, 133 | `Client::find()` com validacao `exists:clients,id`: aceita cliente de outra empresa e escreve o nome dele no equipamento. |
| `Modules/PortalInfra/app/Http/Controllers/Web/ProvisionController.php` | 202, 342, 377 | `ProvisioningRecord` nao tem `company_id`; o escopo precisa vir do servidor (`whereHas('mikrotikServer')`). |

## Fora de escopo (sem `company_id`)

`Contract`, `ServiceOrder`, `Ticket`, `Payment`, `Address`, `Equipment`, `Cto`,
`CaixaEmenda`, `FtthSplitter`, `FtthFiberLink`, `FtthFusion`, `FtthConnection`,
`Supplier`, `Manufacturer`, `StockCategory`, `User`, `UserGroup`.

O plano trata esses como derivados do cliente ("sem duplicar escopo"), entao a
correcao e no pai: enquanto `Client` e `Invoice` nao estiverem filtrados, os filhos
herdam o mesmo problema.

## Decisoes pendentes (precisam de definicao sua)

1. **CRUD de filial.** `BranchController` lista todas as filiais, `store` aceita
   qualquer `company_id` e `edit`/`destroy` usam `findOrFail` global. Se a gestao de
   filiais e exclusiva da franqueadora, o certo e restringir por superadmin; se nao,
   escopar como os demais. `ContextController` ja valida a filial contra as filiais
   do usuario, entao o seletor de contexto nao esta no grupo de risco.
2. **`provisioning_records.company_id`.** Escopar pela relacao com o servidor
   (recomendado: sem migration) ou criar a coluna com backfill.
3. **Global scope no `BelongsToTenant`.** Continua em aberto. O obstacle e o portal do
   cliente, que autentica no guard `client` e nao passa pelo `TenantContext` de
   usuario: um scope ingenuo esconderia as faturas do proprio cliente de uma
   franquia. Se for adotado, precisa de bypass explicito para o guard `client` e
   teste do portal — a suite atual tem 88 testes e cobre pouco desse caminho.

## Ordem de ataque sugerida

1. **APIs de planos, pagamentos e faturas** — maior ganho, zero risco no portal.
2. **Financeiro web** — boleto, invoice, cash book, gateways (segredos).
3. **CRM** — clients, estoque.
4. **PortalInfra** — OLT, projetos FTTH, provisioning.
5. **Filiais** — depois da decisao 1.

Teste padrao por modulo (o mesmo formato usado em `PublicTenantResolutionTest`):
operador da empresa A recebe 404 em edit/update/destroy de um id da empresa B e nao
encontra o registro da B no index.
