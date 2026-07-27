# Plano de Migracao — Modulo PortalInfra

## Objetivo

Criar o modulo `PortalInfra` unificando toda infraestrutura (MikroTik, FTTH, Equipamentos, Monitoramento, Provisionamento, Backups, Site Blocking) em um unico modulo, separado do CRM. O sidebar sera reorganizado em 4 secoes: **CRM**, **Infraestrutura**, **Suporte**, **Sistema**.

---

## 1. Itens que migram para PortalInfra

### 1.1 Do modulo CRM (controllers, views, rotas)

| Categoria | Controllers | Views | Rotas |
|-----------|-------------|-------|-------|
| **MikroTik Servers** | `MikrotikServerController` | `mikrotik-servers/` | `crm.mikrotik-servers.*` |
| **MikroTik Operations** | `MikrotikController`, `IpPoolController`, `FirewallController`, `InterfaceController`, `ArpController`, `MikrotikScriptController`, `LogsController` | `mikrotik/` | `crm.mikrotik.*` |
| **MikroTik Backups** | `MikrotikBackupController` | `mikrotik-backups/` | `crm.mikrotik-backups.*` |
| **Provisionamento** | `ProvisionController` | `provisioning/` | `crm.provisioning.*` |
| **Uptime** | `UptimeController` | `uptime/` | `crm.uptime.*` |
| **Monitoramento** | `NetworkMonitorController` | `network-monitor/` | `crm.network-monitor.*` |
| **Site Blocking** | `SiteBlockingController` | `site-blocking/` | `crm.site-blocking.*` |
| **Equipamentos** | `EquipmentController` | `equipment/` | `crm.equipment.*` |
| **Fabricantes** | `ManufacturerController` | `manufacturers/` | `crm.manufacturers.*` |
| **Cupons Hotspot** | `HotspotCouponController` | `hotspot-coupons/` | `crm.hotspot-coupons.*` |

### 1.2 Do modulo Ftth (migrar completamente)

| Item | Arquivos |
|------|----------|
| Controller | `FtthController.php` (547 linhas, 22 metodos) |
| Models | `Cto.php`, `CaixaEmenda.php` |
| Services | `KmlNetworkGenerator.php` |
| Migrations | 3 arquivos (ja rodados, nao mexer) |
| Views | 13 blade templates (dashboard, ctos/, caixas/, map, generate, export-kml) |
| Rotas | 24 rotas customizadas (`ftth.*`) |

### 1.3 Total de arquivos movidos: ~45 arquivos

---

## 2. Itens que permanecem no CRM

| Categoria | Controllers | Views |
|-----------|-------------|-------|
| Dashboard | `DashboardController` | `dashboard/` |
| Clientes | `ClientController` | `clients/` |
| Planos | `PlanController` | `plans/` |
| Contratos | `ContractController` | `contracts/` |
| Ordens de Servico | `ServiceOrderController` | `service_orders/` |
| Fornecedores | `SupplierController` | `suppliers/` |
| Tecnicos (admin) | `TechnicianController` | `technicians/` |
| Tickets | `TicketController` | `tickets/` |
| Newsletter | `NewsletterController` | `newsletter/` |
| Portal Cliente | `PortalController` | `portal/` |
| Portal Tecnico | `TechnicianPortalController` | `technician/` |

---

## 3. Nova estrutura do Sidebar

```
CRM
├── Dashboard           (crm.dashboard)              — dashboard
├── Clientes            (crm.clients.index)           — clients
├── Planos              (crm.plans.index)             — plans
├── Contratos           (crm.contracts.index)         — contracts
├── Ordens de Servico   (crm.service-orders.index)    — service_orders
└── Fornecedores        (crm.suppliers.index)         — suppliers

Infraestrutura
├── MikroTik
│   ├── Servidores      (infra.mikrotik-servers.index)  — mikrotik_servers
│   ├── Sessoes Ativas  (infra.mikrotik.pppoe-active)   — mikrotik_servers
│   ├── IP Pools        (infra.mikrotik.ip-pools)       — mikrotik_servers
│   ├── NAT / Firewall  (infra.mikrotik.nat-rules)      — mikrotik_servers
│   ├── Address List    (infra.mikrotik.address-list)   — mikrotik_servers
│   ├── Interfaces      (infra.mikrotik.interfaces)     — mikrotik_servers
│   ├── Tabela ARP      (infra.mikrotik.arp)            — mikrotik_servers
│   ├── Gerador Scripts (infra.mikrotik.scripts)        — mikrotik_servers
│   ├── Logs            (infra.mikrotik.logs)           — mikrotik_servers
│   └── Backups         (infra.mikrotik-backups.index)  — backups
├── FTTH
│   ├── Dashboard FTTH  (infra.ftth.dashboard)        — (sempre visivel)
│   ├── CTOs            (infra.ftth.ctos.index)       — (sempre visivel)
│   ├── Caixas Emenda   (infra.ftth.caixas.index)     — (sempre visivel)
│   ├── Mapa da Rede    (infra.ftth.map)              — (sempre visivel)
│   ├── Gerar Manual    (infra.ftth.generate)         — (sempre visivel)
│   ├── Gerar por Cidade(infra.ftth.generate.city)    — (sempre visivel)
│   └── Exportar KML    (infra.ftth.export.kml)       — (sempre visivel)
├── Equipamentos        (infra.equipment.index)       — equipment
├── Fabricantes         (infra.manufacturers.index)   — manufacturers
├── Cupons Hotspot      (infra.hotspot-coupons.index) — hotspot_coupons
├── Provisionamento     (infra.provisioning.index)    — provisioning
├── Uptime              (infra.uptime.index)           — uptime
├── Monitoramento Rede  (infra.network-monitor.index) — network_monitor
└── Bloqueio Sites      (infra.site-blocking.index)   — site_blocking

Suporte
├── Portal do Cliente   (crm.portal.login)            — [sempre]
├── Portal Tecnico      (technician.portal.dashboard)  — [sempre]
├── Chamados            (crm.tickets.index)           — tickets
└── Newsletter          (crm.newsletter.index)        — newsletter

Financeiro
├── Faturas             (billing.invoices.index)      — invoices
├── Pagamentos          (billing.invoices.index?status=paid) — invoices
├── Livro Caixa         (billing.cash-book.index)     — cash_book
├── Relatorios          (billing.reports.index)       — reports
├── Boletos             (billing.boleto.index)         — boleto
└── Gateways            (billing.gateways.index)      — [sempre]

Sistema
├── Usuarios            (core.users.index)            — settings
├── Grupos              (core.user-groups.index)      — settings
└── Configuracoes       (core.settings.index)         — settings
```

---

## 4. Estrutura do modulo PortalInfra

```
Modules/PortalInfra/
├── composer.json
├── module.json
│
├── app/
│   └── Http/
│       └── Controllers/
│           └── Web/
│               ├── DashboardController.php       (novo — resumo de infra)
│               ├── MikrotikServerController.php  (movido do CRM)
│               ├── MikrotikController.php        (movido do CRM)
│               ├── MikrotikScriptController.php  (movido do CRM)
│               ├── MikrotikBackupController.php  (movido do CRM)
│               ├── IpPoolController.php          (movido do CRM)
│               ├── FirewallController.php        (movido do CRM)
│               ├── InterfaceController.php       (movido do CRM)
│               ├── ArpController.php             (movido do CRM)
│               ├── LogsController.php            (movido do CRM)
│               ├── ProvisionController.php       (movido do CRM)
│               ├── UptimeController.php          (movido do CRM)
│               ├── NetworkMonitorController.php  (movido do CRM)
│               ├── SiteBlockingController.php    (movido do CRM)
│               ├── EquipmentController.php       (movido do CRM)
│               ├── ManufacturerController.php    (movido do CRM)
│               ├── HotspotCouponController.php   (movido do CRM)
│               └── FtthController.php            (movido do Ftth)
│
├── app/
│   └── Models/
│       ├── Cto.php                              (movido do Ftth)
│       └── CaixaEmenda.php                      (movido do Ftth)
│
├── app/
│   └── Services/
│       └── KmlNetworkGenerator.php              (movido do Ftth)
│
├── app/
│   └── Providers/
│       ├── PortalInfraServiceProvider.php
│       └── RouteServiceProvider.php
│
├── config/
│   └── config.php
│
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── master.blade.php                 (copia do CRM, adaptado)
│       ├── dashboard/
│       │   └── index.blade.php
│       ├── mikrotik-servers/                    (movido do CRM)
│       ├── mikrotik/                            (movido do CRM)
│       ├── mikrotik-backups/                    (movido do CRM)
│       ├── provisioning/                        (movido do CRM)
│       ├── uptime/                              (movido do CRM)
│       ├── network-monitor/                     (movido do CRM)
│       ├── site-blocking/                       (movido do CRM)
│       ├── equipment/                           (movido do CRM)
│       ├── manufacturers/                       (movido do CRM)
│       ├── hotspot-coupons/                     (movido do CRM)
│       ├── ctos/                                (movido do Ftth)
│       ├── caixas/                              (movido do Ftth)
│       ├── dashboard-ftth.blade.php             (movido do Ftth)
│       ├── map.blade.php                        (movido do Ftth)
│       ├── generate.blade.php                   (movido do Ftth)
│       ├── generate-city.blade.php              (movido do Ftth)
│       ├── generate-cities.blade.php            (movido do Ftth)
│       └── export-kml.blade.php                 (movido do Ftth)
│
├── routes/
│   └── web.php                                  (todas as rotas migradas com prefixo /infra)
│
└── database/
    └── migrations/                              (vazio — migrations ja existem)
```

---

## 5. Plano de Execucao (por etapas)

### Etapa 1: Criar estrutura basica do modulo
- Criar pasta `Modules/PortalInfra/` com subpastas
- Criar `composer.json`, `module.json`
- Criar `PortalInfraServiceProvider.php` e `RouteServiceProvider.php`
- Criar `routes/web.php` vazio

### Etapa 2: Migrar Controllers do CRM
- Copiar os 17 controllers listados na secao 1.1
- Alterar namespace de `Modules\CRM\Http\Controllers\Web` para `Modules\PortalInfra\Http\Controllers\Web`
- Alterar referencias de views de `crm::` para `infra::`
- Ajustar model imports se necessario

### Etapa 3: Migrar Controllers do Ftth
- Copiar `FtthController.php`
- Copiar models (`Cto.php`, `CaixaEmenda.php`)
- Copiar `KmlNetworkGenerator.php`
- Alterar namespaces

### Etapa 4: Migrar Views
- Copiar todas as pastas de views listadas na secao 1.1 e 1.2
- Criar `layouts/master.blade.php` proprio (copia do CRM com ajustes)
- Ajustar todas as referencias `crm::` para `infra::`
- Ajustar rotas nas views (`crm.*` para `infra.*`)

### Etapa 5: Migrar Rotas
- Criar `routes/web.php` do PortalInfra com todas as rotas
- Trocar prefixo de `/crm` para `/infra`
- Trocar nomes de `crm.*` para `infra.*`
- Manter middleware `auth` + `group.permission`

### Etapa 6: Atualizar Sidebar
- Remover secao "MikroTik" do sidebar do CRM
- Remover secao "Rede FTTH" do sidebar do CRM
- Remover secao "Infraestrutura" (network-monitor) do sidebar do CRM
- Criar secao "Infraestrutura" com subsecoes MikroTik e FTTH no sidebar do CRM
- Remover itens que migraram (equipment, manufacturers, hotspot-coupons, provisioning, uptime, site-blocking)

### Etapa 7: Atualizar permissoes
- Nenhuma mudanca necessaria nas chaves de permissao
- As mesmas chaves (`mikrotik_servers`, `provisioning`, `uptime`, etc.) continuam sendo usadas
- Apenas as rotas e controllers mudam de modulo

### Etapa 8: Limpeza
- Deletar controllers migrados do modulo CRM
- Deletar views migradas do modulo CRM
- Deletar rotas migradas do modulo CRM
- Deletar modulo `Modules/Ftth/` (todo migrado)
- Deletar modulo `Modules/Network/` (scaffold vazio)
- Remover use statements e imports nao utilizados

### Etapa 9: Testar
- Verificar todas as rotas com `php artisan route:list`
- Testar login admin e acessar cada pagina migrada
- Testar permissoes (superadmin deve ver tudo)
- Verificar que o CRM continua funcionando
- Testar Portal do Tecnico e Portal do Cliente

---

## 6. Riscos e Consideracoes

| Risco | Mitigacao |
|-------|-----------|
| Quebrar rotas existentes | Manter as mesmas chaves de permissao, apenas trocar prefixo |
| Models com namespace antigo | Buscar todos os `use Modules\CRM\Models\` nos controllers movidos |
| Views com links hardcoded | Buscar todas as referencias `route('crm.*')` nas views movidas |
| Migrations do Ftth | Nao mexer — ja rodadas, tables existem |
| Servicos Ftth | `KmlNetworkGenerator` usa models `Cto` e `CaixaEmenda` — ajustar imports |

---

## 7. Estimativa de Esforco

| Etapa | Arquivos | Complexidade |
|-------|----------|--------------|
| 1. Estrutura basica | ~6 | Baixa |
| 2. Controllers CRM | ~17 | Media |
| 3. Controllers Ftth | ~3 | Media |
| 4. Views | ~30 | Alta (muitos ajustes de rota) |
| 5. Rotas | 1 | Media |
| 6. Sidebar | 1 | Baixa |
| 7. Permissoes | 1 | Baixa |
| 8. Limpeza | ~50 | Baixa |
| 9. Testes | - | Alta |
| **Total** | **~110** | **Alta** |
