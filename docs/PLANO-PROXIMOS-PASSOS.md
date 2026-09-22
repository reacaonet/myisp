# Plano — próximos passos (MyISP)

> Referência única de **o que falta implementar**. Se tiver dúvida do que vem
> depois, é isto aqui que vale.
> Tudo abaixo vive **dentro deste repositório** (nada de pastas paralelas).

## 0. Caminho canônico do projeto (NÃO duplicar)

```
C:\Users\USER\Documents\Docs\Projetos\myisp
```

- É o único. Não existe `Docs\Projetos\...` dentro de outro `Docs\Projetos`.
- Repo SSH:      `git@github.com:reacaonet/myisp.git`
- user.name:      Cristian Nunes Macena
- user.email:     6474584+reacaonet@users.noreply.github.com
- Tudo novo (módulo/subsistema/doc) nasce **dentro** desta árvore:
  - `Modules/<Modulo>/`       → módulo nwidart/laravel-modules
  - `docs/`                   → documentação markdown
  - `database/migrations,seeders` → banco (também `Modules/*/database/...`)

## 1. Estado atual (commitado + pushado em `main`)

- Editor de rede FTTH: mapa Leaflet full + tabela de indicadores acima do mapa + CRUD fibras/splitters/conexões + export KML/CSV.
- Rotas `infra.ftth.editor.*`, controller `FtthEditorController`, views editor.
- Stack Docker (Rancher Desktop, NÃO Docker Desktop): app :8000, postgres :5433 (healthy), redis :6380 (healthy).
- Docs: `docs/GIT-SSH-ACESSO.md`, `docs/roadmap-ftth.md`, screenshot do editor.

## 2. Prioridade ATUAL — funcionalidades do sistema (monitoramento fica por último)

### P1 — Funcionalidades do editor FTTH (continuar aqui)
- [ ] **Mover/arrastar CTOs/CTXs já persistidas** no mapa (edit → mudar posição → salvar posição REAL no banco, não só em `ftth_connections`).
- [ ] **Persistir camadas do projeto**: KML ↔ tabelas `ftth_fiber_links`, `ftth_splitters`, `ftth_connections` com rollback e sem duplicar CTOs (dedup por chain contígua — base já pronta).
- [ ] **Multi-projeto por cidade** (separar conexões por `ftth_project_id`) + histórico de mudanças.
- [ ] CRUD de **OLTs** no editor (posição geográfica, nome, modelo, vlan de gerência) — ligar CTOs à OLT no mapa.

### P2 — Funcionalidades de suporte ao ISP (sistema em si)
- [ ] **Login com 2FA (TOTP)** no admin — senha + código do app autenticador (o usuário é o dono da rede, precisa de segurança real).
- [ ] **Portal do cliente/assinante** (consulta de plano, fatura, suporte) — separado do admin.
- [ ] **Fila de atendimento/tickets** ligando cliente ↔ equipamento (CTO/região) — evita atendimento às cegas.
- [ ] **Backup do Postgres** (`pg_dump` datado, fora do volume, com retenção) e do Redis (rdb) — rotina pronta antes de crescer.

### P3 — ÚLTIMA etapa: Monitoramento de rede (Grafana/Prometheus/Zabbix)
> Atras de propósito: **sem OLT/ONU física não dá para testar**. Quando houver
> equipamento em rede para emular/testar, implementar:
- [ ] Adicionar `prometheus` + `snmp_exporter` + `grafana` + `alertmanager` ao `docker-compose.yml`.
- [ ] SNMP para OLTs GPON/EPON: coletar ONU offline/online, potência RX/TX, erros de frame, UAS.
- [ ] Dashboard Grafana: mapa de calor de potência por cidade, OLTs fora, ONUs offline, fibras degradadas (RX ≤ −27 dBm).
- [ ] Alerta (Alertmanager → Telegram/e-mail): RX ≤ −30 dBm = risco de queda (preventivo antes do rompimento).
- [ ] Correlação no Laravel: queda/degradação × clientes ativos da região → fila de atendimento prioritária.
- [ ] (Futuro, se necessário) Zabbix para inventário/trap de equipamentos além do SNMP.

## 3. Regras de operação (não esquecer nunca)

- Git: `git add -A && git commit -m "..." && git push` — sempre SSH (`git@github.com:reacaonet/myisp.git`), nunca HTTPS.
- Nunca commitar: `.env`, `public/storage`, senhas reais, lixo de teste (`NUL`, `cookies.txt`).
- Tudo novo → dentro do repo (seção 0). Nada de caminho alternativo.
- Rancher Desktop (não Docker Desktop). Executáveis fora do PATH: ver `docs/GIT-SSH-ACESSO.md`.
