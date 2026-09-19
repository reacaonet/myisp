# Cronograma de Melhoramento da Rede FTTH

Plano incremental para evoluir o módulo `PortalInfra` (FTTH) do atual gerador
automático via OpenStreetMap para um **editor geográfico interativo** onde a
equipe possa ajustar, lançar e conectar a rede de fibra óptica.

## Problema atual conhecido

- **Metragem desrespeitada pelo formulário**: em `KmlNetworkGenerator.php` o
  dedup usa o **nome da rua global** (`streetCtoCoords[$streetName]`). Quando o
  OSM modela uma via dupla como dois `way`s paralelos de mesmo nome (ou a mesma
  rua em trechos diferentes), as CTOs da segunda via são descartadas por "too
  close" e o espaçamento real vira ~2x o intervalo configurado.
  - **Correção**: dedup por **chain contígua** (grupo costurado na
    `mergeStreetsByContinuity`), não por nome global. `processStreet` já
    espaça corretamente ao longo da própria chain em `KmlNetworkGenerator.php:702`.

## Fases

### Fase 1 — Corrigir metragem do gerador ✅
**Objetivo**: o gerador respeitar exatamente o `cto_interval` do form.

- **Concluído (commit `1ee8469` depois):** dedup em 2 camadas em
  `KmlNetworkGenerator.php` — `chainCtoCoords[$chainIndex]` (por chain contígua
  da `mergeStreetsByContinuity`) + `streetCtoCoords[$streetName]` (cross-chain,
  evita CTOs de ruas paralelas de mesmo nome colidirem), ambas com margem
  `STREET_SEGMENT_JOIN_METERS` (30m), não o intervalo.
- Validado: via reta 1110m@250m → 4 CTOs exatos; ruas paralelas @40m → 8 CTOs;
  Santo Antonio dos Lopes 70 CTOs/skips 2; Tuntum 152 CTOs/skips 0; vias
  individuais ~150m de espaçamento.

**Entrega**: commit isolado + relatório de metragem por cidade.

### Fase 2 — Schema de topologia (editoráveis) ✅
**Objetivo**: persistir elementos de rede editáveis, independentes da geração.

- Tabela `ftth_fiber_links`: traçado de fibra lançada como **polilinha**
  geográfica (lista de `{lat,lng}`), `length_meters` calculado, tipo
  (tronco/distribuição/drop residencial), atribuição a `ftth_project_id` (nullable).
- Tabela `ftth_splitters`: splitter com posição (`latitude`/`longitude`),
  `input_ports` (1) e `output_ports` (N: 8/16/32), ratio, referência ao projeto (nullable).
- Tabela `ftth_connections`: ligações entre portas e fibras:
  - `source_type/source_id/source_port` (CE, CTO, splitter, OLT, ponto final);
  - `fiber_link_id` (opcional) para amarrar a conexão ao traçado;
  - `target_type/target_id/target_port`.
- Models `FtthFiberLink`, `FtthSplitter`, `FtthConnection` + relacionamentos
  com `FtthProject`, `Cto`, `CaixaEmenda`.

**Entrega**: 3 migrations (`2026_09_17_100000..100002`) + 3 models + relacionamentos.

### Fase 3 — Editor visual no mapa (Leaflet) ✅
**Objetivo**: manipular a rede diretamente no mapa.

- **Base por cidade** (decisão): CTOs/caixas reais são agrupados por cidade sem
  projeto (`ftth_project_id` NULL) e os projetos existentes foram soft-deleted.
  O editor lista cidades (`Cto.distinct city`), carrega CTOs/CEs vivos da
  cidade. Splitters e fibras pertencem a um **projeto automático por cidade**
  (`ensureCityProject`, cria "Rede {cidade}" no primeiro save).
- **Drag n'drop** de CTO/CE/splitter → `PUT /api/mover/{type}/{id}` atualiza
  `latitude`/`longitude`.
- **Desenho de polilinha** de fibra: cliques sucessivos, preview com metragem
  acumulada em tempo real (haversine no front), Desfazer ponto, cancelar e
  salvar como `ftth_fiber_link` (`POST /api/fibras`; comprimento recalculado e
  confiável no servidor via haversine).
- **CRUD**: `POST/PUT/DELETE /api/fibras[/{id}]`, `POST/DELETE /api/splitters[/{id}]`.
- Modos satélite/terreno/padrão já disponíveis (commit `1ee8469`).
- **Fixes** durante implementação: relação `FtthFiberLink::connections()` usa
  FK real `fiber_link_id` (não a convenção `ftth_fiber_link_id`).

**Entrega**: tela `Editor de Rede` no PortalInfra (`FtthEditorController`,
  rotas `infra.ftth.editor.*`, view `editor/index.blade.php`, link no menu).

### Fase 4 — Splitters e conexões lógicas ✅
**Objetivo**: modelar o cabeamento entre OLT → CE → splitters → CTOs.

- **CRUD de splitters** completo no editor (criar clicando no mapa, arrastar,
  excluir) — Fase 3.
- **Conexões lógicas** (`ftth_connections`) com validação de capacidade:
  - `POST /api/conexoes`: origem/destino tipo+id+porta, `fiber_link_id`
    opcional; `POST`/`DELETE /api/conexoes/{id}`.
  - **Entrada do splitter (porta 0)** aceita apenas 1 conexão; **portas de
    saída** devem ser ≤ `output_ports` e não duplicadas → retorna 422
    ("Porta X já está ocupada" / "Entrada já está conectada").
  - `assertElement()` garante que cto/caixa/splitter existam.
- **UI**: botão "Conectar" abre modal com selects de origem (splitter/CE/CTO/
  OLT/ponto), fibra opcional, destino e porta; conexões renderizadas no mapa
  como linhas tracejadas com popup de remoção.
- **Fix**: relação `FtthConnection::fiberLink()`/`FtthFiberLink::connections()`
  agora usam a FK real `fiber_link_id`.

**Entrega**: endpoints + modal + render de conexões no Editor de Rede.

### Fase 5 — Relatórios, exportação e validação ✅
- **Relatório por cidade** (`GET /editor/api/relatorio/{city}`): contadores de
  CTOs (portas/ocupação), CE, splitters (saídas usadas), total de fibra lançada
  (m), conexões, fibra por tipo; painel visual na tela do editor.
- **Exportação KML editado** (`GET /editor/exportar-kml/{city}`): incorpora
  CTOs, caixas, splitters (estilo próprio), fibras como `LineString`
  (polilinhas com metragem) e conexões (linhas entre elementos); content-type
  `application/vnd.google-earth.kml+xml`.
- **Validação topológica** (`GET /editor/api/validar/{city}`): conexões órfãs
  (apontam para elementos soft-deleted), fibras inexistentes, portas de
  splitter duplicadas e entradas excedidas (>1 no porta 0). UI: botão "Validar topologia".

**Entrega**: painel de relatório + exportação KML + validação no Editor de Rede.

## Roadmap de dependências

Todas as fases foram **concluídas** e entregues:

```
Fase 1 (metragem) ──► Fase 2 (schema) ──► Fase 3 (editor) ──► Fase 4 (conexões) ──► Fase 5 (relatórios)
      ✅                   ✅                  ✅                  ✅                   ✅
```

## Evoluções futuras sugeridas
- Exportação CSV da topologia editada (CTOs/CEs/splitters/fibras por cidade).
- Edição de metadados de fibra no mapa (fiber_count, tube_color) pelo popup.
- Histórico/auditoria de movimentações no editor.
- Integrar splitters/fibras ao fluxo de geração automática por cidade.