# MyISP

Plataforma de gestao para provedores de internet (ISP) desenvolvida em Laravel.

## Stack

- **Laravel** 13.20
- **PHP** 8.3
- **PostgreSQL**
- **Tailwind CSS**

## Modulos

| Modulo | Descricao |
|--------|-----------|
| **Core** | Usuarios, grupos, permissoes, configuracoes do sistema |
| **CRM** | Clientes, contratos, planos, ordens de servico, tecnicos, tickets |
| **Billing** | Faturas, pagamentos, boletos, livro-caixa, gateways de pagamento |
| **Network** | Servidores Mikrotik, provisioning, monitoramento de uptime |
| **FTTH** | CTOS, caixas de emenda, infraestrutura de fibra optica |
| **FieldService** | Gestao de campo e visitas tecnicas |
| **PortalCliente** | Portal do cliente (faturas, contratos, chamados, perfil) |
| **PortalTecnico** | Portal do tecnico (ordens de servico, perfil) |

## Autenticacao e Permissoes

O sistema utiliza **grupos de usuario** com permissoes por chave. Grupos padrao:

| Grupo | Descricao |
|-------|-----------|
| `superadmin` | Acesso total a todas as funcionalidades |
| `admin` | Acesso administrativo geral |
| `gerente` | Acesso gerencial com relatorios |
| `tecnico` | Acesso ao Portal do Tecnico (OS atribuidas) |
| `operador` | Acesso operacional ao CRM |

Cada grupo possui permissoes de menu (ex: `dashboard`, `clients`, `invoices`). A verificacao e feita via middleware `group.permission` e no Blade com `$user->hasPermission()`.

## Portais

- **Admin**: `/crm` — painel administrativo completo
- **Portal do Cliente**: `/crm/portal/login` — faturas, contratos, chamados
- **Portal do Tecnico**: `/tecnico/login` — ordens de servico atribuidas

### Credenciais de teste

| Usuario | Email | Senha | Grupo |
|---------|-------|-------|-------|
| Administrador | admin@myisp.com | admin | superadmin |
| Operador | operador@myisp.com | 123456 | operador |
| Carlos Alberto | carlos@myisp.com | tecnico123 | tecnico |
| Fernanda Lima | fernanda@myisp.com | tecnico123 | tecnico |

## Instalacao

```bash
git clone https://github.com/reacaonet/myisp.git
cd myisp
composer install
cp .env.example .env
php artisan key:generate
```

Configurar o banco de dados no `.env`:

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=myisp
DB_USERNAME=postgres
DB_PASSWORD=senha
```

Rodar migrations e seeders:

```bash
php artisan migrate:fresh --seed
```

Iniciar o servidor:

```bash
php artisan serve
```

## Estrutura de Pastas

```
Modules/
  Core/          — Modelo User, UserGroup, GroupPermission, migrations, seeders
  CRM/           — Client, Contract, Plan, ServiceOrder, Ticket, Technician portal
  Billing/       — Invoice, Payment, BillingSetting, CashBookEntry
  Network/       — Server, ProvisioningRecord, UptimeMonitor
  Ftth/          — CTO, CaixaEmenda
  FieldService/  — Gestao de campo
  PortalCliente/ — Portal do cliente
  PortalTecnico/ — Portal do tecnico
```

## Licenca

MIT
