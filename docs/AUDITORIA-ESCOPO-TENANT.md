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
(controller web), `MikrotikServer`, `HotspotCoupon`, `UptimeMonitor`, `MikrotikBackup`,
`Client` (show/edit/update/history/destroy), `ServiceOrder`, `Contract` (API),
`Payment` (API), `Invoice` (web e API), `Boleto`, `CashBookEntry` e `Olt`
(edit/update/destroy).

Duas familias de bug apareceram juntas, e as duas importam:

1. **Item nao escopado.** A lista usava `forCompany()`/`scoped()`, mas o acesso por
   id continuava global.
2. **Escopo pela metade.** `forCompany()` e o suficiente entre empresas diferentes,
   mas **nao** dentro de uma empresa so. Como o banco real tem uma empresa com
   matriz e filiais, o filtro antigo devolvia a operação da rede inteira para o
   usuario de uma loja. Por isso os models declarais de corte tem
   `tenantBranchColumn()`, e o funil correto e `forTenant()`, nao `forCompany()`.

O `Olt` merece nota: a listagem ja usava `forCompany()`, mas `edit`, `update` e
`destroy` faziam `Olt::findOrFail($id)` sem escopo. Como o grupo `franqueados`
recebe a permissao `olts`, um franqueado abria, alterava e apagava OLT de outra
loja apenas pelo id. Agora passam por `findScoped()`, que responde 404.

O financeiro tinha os dois problemas somados. Pior: o **saldo anterior** do livro
caixa (`entry_date < $startDate`) nao passava por filtro nenhum, entao o valor do
topo da tela era a soma da rede inteira, e a API de faturas nao tinha escopo algum.

## Multiempresa — vinculo de usuario

`users` nao tem `company_id`/`branch_id`: o vinculo vive em `company_user` e
`branch_user`. Um usuario pode (e deve) estar em varias empresas ao mesmo tempo,
porque o dono do negocio compra mais de uma loja e o investidor pode ter
participacao em mais de uma.

Consequencias praticas:

- `sync([$companyId])` era um bug de modelagem: gravava uma empresa so e
  apagava as demais a cada edicao. `UserController` agora grava os conjuntos
  `company_ids[]` e `branch_ids[]`.
- Marcar uma filial traz a empresa dela junto. Marcar uma empresa sem filial
  significa "todas as filiais desta loja", e nao "nenhuma filial" — forcar a
  matriz esconderia as demais unidades.
- Somente superadmin define os vinculos. Para nao-superadmin o escopo nao e
  sobrescrito e nenhuma empresa e removida por accidento ao editar outro campo.
- O contexto ativo vive na sessao (`current_company_id`/`current_branch_id`) e
  e trocado em `core.context.switch`, com o seletor no topo do layout. Trocar de
  empresa invalida a filial e o contexto cai na primeira filial liberada.

Estado estatico em `TenantContext` atravessa a fronteira entre testes no mesmo
processo do PHPUnit; por isso `tests/TestCase.php` chama `TenantContext::forget()`
e `PublicTenantResolver::forget()` no `setUp()`. Sem isso os testes passam
isolados e falham na suite inteira, dependendo da ordem.

## Prioridade 1 — APIs sem escopo algum

| Arquivo | Linha | Problema |
|---|---|---|
| `Modules/CRM/app/Http/Controllers/Api/PlanController.php` | 12 | `Plan::paginate()` sem filtro: qualquer usuario com a permissao `plans` le todos os planos de todas as franchises. `store` tambem nao valida tenant. |
| `Modules/Billing/app/Http/Controllers/Api/PaymentController.php` | 13 | `Payment::with('invoice.client')->paginate()` cruza a rede inteira (valores + clientes). `store` aceita `invoice_id` de qualquer empresa e ainda marca a fatura como paga. |
| `Modules/Billing/app/Http/Controllers/Api/InvoiceController.php` | 51, 77 | `show`/`update` por id sem filtro. |

Rotas: `routes/api.php` de cada modulo, todas com `group.permission` — ou seja, a
permissao existe, o filtro nao.

## Prioridade 2 — Financeiro (web)

**Corrigido.** Todos os caminhos passaram a usar `forTenant()` (corta por filial) em
vez de `forCompany()` (so empresa) e `findOrFail()` cru.

| Arquivo | Correcao |
|---|---|
| `Modules/Billing/app/Models/Invoice.php` | `tenantBranchColumn()` = `branch_id`; `scoped()`, `findScoped()`, `findScopedOrFail()`. |
| `Modules/Billing/app/Models/CashBookEntry.php` | Entrou no opt-in de filial: `tenantBranchColumn()` = `branch_id`, com `scoped()` e `findScopedOrFail()`. |
| `Modules/Billing/app/Http/Controllers/Web/InvoiceController.php` | Listagem, cards de total, `create` e as 7 acoes por id (`show`, `edit`, `update`, `destroy`, `payment`, `block`, `unblock`). |
| `Modules/Billing/app/Http/Controllers/Web/BoletoController.php` | Listagem e acoes por fatura (`print`, gerar boleto/pix, sincronizar, cancelar, excluir pagamento). |
| `Modules/Billing/app/Http/Controllers/Web/CashBookController.php` | Lista, resumo do periodo, `show`, `edit`, `update`, `destroy` e a lista de categorias. **O saldo anterior (`entry_date < $startDate`) estava sem filtro nenhum** e somava a rede inteira no topo da tela. |
| `Modules/Billing/app/Http/Controllers/Api/InvoiceController.php` | Listagem e CRUD estavam 100% sem escopo. `client_id` tambem aceitava cliente de qualquer filial; agora valida com `Client::scoped()`. |
| `Modules/Billing/app/Http/Controllers/Api/PaymentController.php` | `scopedInvoices()` filtrava so por `company_id`; trocado por `forTenant()`. |
| `Modules/Billing/app/Services/InvoiceListFilter.php` | `branches()` devolvia as filiais da empresa inteira, entao o filtro oferecia a Matriz para o usuario da loja. |

Observacoes:

- `bulkDelete` ja era seguro (filtra por `forTenant` no model). O contrato dele e
  redirect com `error` "Nenhuma fatura selecionada", **nao** 404.
- `PaymentGateway` e configuracao da rede (credenciais do servidor), nao da loja.
  Continua por empresa e so para grupos com `gateways`, que o `franqueados` nao tem.
- `ReportController` segue sem escopo e continua bloqueado para o franqueado.

Verificado no banco dev, logado como o Carlos (filial 3): 2 faturas na rede,
1 visivel; fatura da matriz por URL retorna 404; total do card R$ 49,90.

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

## Prioridade 4 — PortalInfra (corrigido)

Os quatro itens abaixo foram fechados. O recorte adotado foi: **todo registro que
tem `company_id` ou `branch_id` passa a cortar por filial** (`forTenant()`), e nao
so por empresa. Modelos que so tem `company_id` continuam por empresa — sao
configuracao da rede (planos, gateways, cotacoes de uptime, backups), que e
compartilhada de proposito.

| Arquivo | Correcao |
|---|---|
| `Modules/CRM/app/Models/MikrotikServer.php` | `tenantBranchColumn() = branch_id`; `scoped()` agora usa `forTenant()`. O equipamento pertence a filial que o opera. |
| `Modules/CRM/app/Models/Olt.php` | `tenantBranchColumn() = branch_id` + `scoped()`/`findScopedOrFail()`. |
| `Modules/CRM/app/Models/StockLocation.php` | `tenantBranchColumn() = branch_id` + `scoped()`. |
| `Modules/PortalInfra/app/Http/Controllers/Web/MikrotikServerController.php` | `branchOptions()` combina `where('company_id')` com `forTenant()`, entao o seletor de filial nao oferece mais a Matriz para quem so opera uma loja. |
| `Modules/PortalInfra/app/Http/Controllers/Web/OltController.php` | `scopedQuery()` virou `Olt::scoped()`. |
| `Modules/PortalInfra/app/Http/Controllers/Web/ProvisionController.php` | `ProvisioningRecord` nao tem `company_id`: novos helpers `scopedRecords()`/`findScopedRecord()` recortam por `mikrotik_server_id IN (MikrotikServer::scoped())`. `edit`, `update`, `destroy` e `block` aceitaram qualquer id antes — o usuario de uma filial desligava o acesso de cliente das outras lojas pelo id. `clientForServer()` passou a usar `Client::scoped()`. |
| `Modules/PortalInfra/app/Http/Controllers/Web/DashboardController.php` | Indicadores por tenant em vez do filtro manual so por empresa. |

### FTTH: quem e o dono da rede

O filtro antigo era so por empresa, entao um usuario da filial via e editava as
redes da Matriz. Em FTTH o dono **nao pode ser lido de `company_id` nos filhos**:
`ctos`, `caixas_emenda`, `ftth_splitters`, `ftth_fiber_links`, `ftth_connections` e
`ftth_fusions` nao tem `company_id` nem `branch_id`. O vinculo e
`ftth_project_id`, e o projeto e quem carrega `branch_id`.

- `FtthProject` passou a declarar `tenantBranchColumn() = branch_id` e a expor
  `scoped()`/`findScopedOrFail()`.
- Novo trait `Modules/PortalInfra/app/Models/Concerns/ScopedByFtthProject.php`
  (`scopeVisibleToUser`, `scoped`, `findScopedOrFail`) traduz o escopo do projeto
  para os filhos: superadmin ve tudo; os demais veem apenas projetos liberados e
  **nao veem item sem `ftth_project_id`** (orfao nao pertence a ninguem).
- Aplicado aos 6 modelos filhos.
- `FtthController` e `FtthEditorController` passaram a usar `scoped()` /
  `findScopedOrFail()` em listagens, mapa, exports, CRUD, bulk delete e nos 17
  endpoints do editor. O editor trabalhava por `city`, que e global: bastava pedir
  a cidade da Matriz para abrir e alterar o desenho dela.
- **Excecao deliberada:** o gerador (`runGenerate`/`runGenerateCity`) continua
  aplicando updates sem `whereIn` de projeto. Ele opera sobre itens recem-criados
  que ainda estao com `ftth_project_id` nulo — nao ha project id para filtrar.

### Cobertura

`tests/Feature/InfraTenantScopeTest.php::test_infra_da_matriz_fica_invisivel_para_o_usuario_da_filial`
cobre o cenario completo com matriz e filial na mesma empresa: lista e `edit` de
Mikrotik e OLT, projeto e CTO de FTTH, `editor/data` por cidade, `PUT` de elemento
alheio (404, sem efeito no banco) e `provisioning.edit`.

### Dados reais (dev) — pendencia operacional

Nenhum registro tem `branch_id` nulo, entao nada some por omissao de dado. Mas os
**5 projetos FTTH estao todos na filial 1 (Matriz)**, e o usuario de prova
(Carlos) esta na filial 3. Depois do corte ele passa a ver **zero** redes ate os
projetos serem reatribuidos. Isso e dado, nao codigo: a reatribuicao precisa ser
feita com o servidor, nao por script.

## Portal do tecnico — corrigido

O tecnico nao e um `User` com guard proprio: `config/auth.php` aponta o guard
`technician` para a **mesma** tabela `users`, distinguido por `group->slug === 'tecnico'`.
Duas consequencias:

1. **`TenantContext` nao enxergava o guard `technician`.** Ele resolvia o usuario com
   `Auth::user()`, que so olha o guard default (`web`). No portal do tecnico caia no
   ramo anonimo, que resolve para a empresa raiz — sem filtro de filial. Todo
   `scoped()` do portal (CTO, caixa, fusao) era bypass. Agora existe
   `TenantContext::authenticatedUser()`, que checa `web` e depois `technician`. O guard
   `client` segue de fora de proposito (ver decisao 3).
2. **Os 9 endpoints FTTH do portal nao filhavam nada.** `ftthNetwork`, `ftthCtoShow`,
   `ftthCaixaShow`, `ftthCtoActivate`, `ftthCaixaActivate`, `ftthCtoUpdateNotes`,
   `ftthCaixaUpdateNotes`, `ftthFusionUpdate` e `ftthFusionDone` usavam `findOrFail`
   global: o tecnico da filial 3 abria e alterava a rede da matriz pelo id na URL. Todos
   passaram a `scoped()`/`findScopedOrFail()`.

Junto disso, `TechnicianController` (admin) aceitava qualquer id de `users` em
`edit`/`update`/`destroy`: quem tivesse `group.permission:technicians` desligava ou
removia o superadmin. Agora o CRUD e recortado pelo grupo `tecnico`, e o tecnico nao
pode remover a propria conta. A rota `crm.technicians.show` — que apontava para um
metodo inexistente e devolvia 500 — saiu do resource.

### Cobertura

`tests/Feature/TechnicianPortalScopeTest.php` (7 testes) fixa o cenario com matriz e
filial na mesma empresa: listagem, `show`, `ativar`, `notas` e `fusoes` da rede alheia
respondem 404 sem efeito no banco, as mesmas operacoes funcionam na rede do tecnico, e
o CRUD de tecnico recusa usuario de outro grupo.

Validado por mutacao: voltar o `authenticatedUser()` para so `web` faz os 4 testes de
FTTH falharem, e so eles — o que confirma que o teste mede o corte e nao o controller.

## Mudanca de endereco pelo portal do cliente — corrigido

O cliente nao podia corrigir o proprio endereco, e o admin nao tinha como aprovar a
correcao. A solucao adopted foi **chamado com endereco proposto**, nao edicao
direta: `tickets.proposed_address` (`jsonb`) guarda o pedido, e o endereco so passa a
valer depois que um usuario do grupo com permissao `tickets` aplica.

Regras que o fluxo impoe:

- **Endereco canonico** e o primeiro registro de `client.addresses()`. Aplicar o
  endereco proposed atualiza esse registro; se o cliente nao tinha nenhum, cria.
  `contracts.install_*` **nao** e sobrescrito — e o local historico onde o servico
  foi instalado, nao a morada atual.
- **Um pedido por vez.** Chamado com `proposed_address` em `open`/`in_progress`
  bloqueia um novo pedido; os demais chamados do cliente seguem liberados.
- **Aplicar duas vezes nao muda nada.** O segundo toque no botao cai fora pelo
  status `resolved` e nao duplica a mensagem de auditoria.
- **Escopo pela matriz do pedido**, nao pela URL: as acoes de aplicacao e recusa
  localizam o chamado por `whereHas('client', Client::scoped())`, entao um agente de
  outra franquia recebe 404 e nao consegue nem recusar.

Um bug proprio do fluxo merece registro: ao abrir o pedido, `addressChangeStore`
lia `$validated['reason']` direto. O campo e `nullable`, entao quando o cliente
omitia a justificativa o acesso a chave inexistente virou erro 500 e **nenhum chamado
era criado** — a tela quebrava depois do envio. Agora e `$validated['reason'] ?? null`.

### Cobertura

`tests/Feature/ClientAddressChangeTest.php` (16 testes) fixa: perfil mostra o
endereco atual; o pedido vira chamado; o segundo pedido em aberto e barrado; o perfil
mostra o pedido pendente; chamado de outro cliente e recusado; campos obrigatorios;
aplicacao resolve o chamado; aplicacao cria o primeiro endereco quando o cliente nao
tinha nenhum; recusa mantem o endereco; reaplicar nao surte efeito; agente de outra
franquia recebe 404; chamado comum nao expoe o formulario de aprovacao; chamado sem
endereco proposto nao e aplicavel; a tela do chamado mostra o confronto; `contract_id`
de outro cliente e recusado (IDOR); e o endereco sai em uma linha so.

O teste cross-tenant cria o chamado direto no banco, sem antes autenticar como o
cliente. Isso e deliberado: o `TenantContext` e estado estatico dentro do processo, e
autenticar como o cliente antes deixaria o contexto resolvido para a matriz — o teste
passaria a medir o cache em vez do corte entre franchises.

## Perfis web e dois models `User`

Existe **dois** models `User` na mesma tabela `users`: `App\Models\User` (usado pelos
guards `web` e `technician`) e `Modules\Core\Models\User` (usado por
`UserController`, `CompanyController` e varios seeders). Nao ha heranca entre eles.

O layout `Modules/Core/resources/views/layouts/master.blade.php` chama
`auth()->user()->avatarUrl()`, e `auth()` pode devolver qualquer um dos dois. Com o
metodo so no `App\Models\User`, toda tela que autenticava pelo model do modulo
caía em 500 (`BadMethodCallException`) — 3 suites inteiras quebradas
(`CompanySettingsScopeTest`, `MultiCompanyTest`, `OrphanRelationsTest`).

A correcao foi extrair para `Modules/Core/app/Models/Concerns/HasAvatar.php` e usar
o trait nos dois models, em vez de duplicar o metodo. `Modules\Core\Models\User`
tambem passou a ter `city`, `state` e `avatar` no `$fillable`, para que o mesmo
cadastro de perfil funcione nos dois caminhos.

## Fora de escopo (sem `company_id` nem `branch_id`)

`Contract`, `ServiceOrder`, `Ticket`, `Payment`, `Address`, `Equipment`,
`Manufacturer`, `Supplier`, `User`, `UserGroup` — e os itens FTTH, que passam a
herdar do `ftth_project_id`.

O plano trata esses como derivados do cliente ("sem duplicar escopo"), entao a
correcao e no pai: enquanto `Client` e `Invoice` nao estiverem filtrados, os filhos
herdam o mesmo problema.

## Decisoes pendentes (precisam de definicao sua)

1. **CRUD de filial.** `BranchController` lista todas as filiais, `store` aceita
   qualquer `company_id` e `edit`/`destroy` usam `findOrFail` global. Se a gestao de
   filiais e exclusiva da franqueadora, o certo e restringir por superadmin; se nao,
   escopar como os demais. `ContextController` ja valida a filial contra as filiais
   do usuario, entao o seletor de contexto nao esta no grupo de risco.
2. **`provisioning_records.company_id`.** Resolvido sem migration: o escopo vem do
   servidor Mikrotik (`ProvisionController::scopedRecords()`). So migrar se uma
   consulta passar a precisar do tenant sem passar pelo servidor.
3. **Global scope no `BelongsToTenant`.** Continua em aberto. O obstacle e o portal do
   cliente, que autentica no guard `client` e nao passa pelo `TenantContext` de
   usuario: um scope ingenuo esconderia as faturas do proprio cliente de uma
   franquia. O guard `technician` ja foi resolvido (ver secao do portal do tecnico) e
   o `client` continua de fora de proposito. Se o scope for adotado, precisa de bypass
   explicito para o guard `client` e teste do portal.
4. **Reatribuicao dos 5 projetos FTTH da Matriz.** Codigo pronto; falta a decisao
   de qual filial e dona de cada rede.
5. **`reports` concedido ao grupo `franqueados`.** O `ReportController` ainda roda
    consultas globais. Ou o controller e escopado, ou a permissao e removida do
    grupo — decisao de produto.
6. **`TicketController` (admin) ainda usa consulta global.** As acoes novas de
   endereco foram escopadas, mas `index`, `show`, `updateStatus`, `reply` e
   `destroy` continuam em `Ticket::findOrFail()` / query crua. Um usuario com a
   permissao `tickets` ainda abre e responde chamado de outra franquia pelo id. E o
   mesmo padrao ja corrigido nos outros modulos ("lista escopada, item nao
   escopado"), entao a correcao e direta: `Ticket` nao tem `company_id`, o corte
   precisa ser por `whereHas('client', Client::scoped())`, como no fluxo de
   endereco. Falta decidir tambem se `reply` deve recusar chamado de outro cliente.
7. **`situacao=A` com dois nomes.** O admin mostra "Aprovado" e o tecnico mostra
   "Em Andamento" para o mesmo valor. Precisa de definicao de produto: sao o mesmo
   estado com dois nomes, ou `A` esta sendo reaproveitado para duas etapas?

## Ordem de ataque sugerida

1. **APIs de planos, pagamentos e faturas** — maior ganho, zero risco no portal. **feito**
2. **Financeiro web** — boleto, invoice, cash book, gateways (segredos). **feito**
3. **CRM** — clients, estoque. **feito**
4. **PortalInfra** — OLT, projetos FTTH, provisioning. **feito**
5. **Filiais** — depois da decisao 1. **pendente**

Teste padrao por modulo (o mesmo formato usado em `PublicTenantResolutionTest`):
operador da empresa A recebe 404 em edit/update/destroy de um id da empresa B e nao
encontra o registro da B no index.
