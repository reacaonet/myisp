# Git + acesso SSH (MyISP)

Referencia unica de como commitar/pushar e de como o ambiente e montado.
Se este arquivo existir e voce estiver em duvida, e isso aqui que vale.

## 1. Acesso ao repositorio (SSH - nao usar HTTPS)

O remote ja esta configurado via SSH no projeto. Nao precisa trocar para HTTPS.

URL SSH:      git@github.com:reacaonet/myisp.git
Dono/conta:   reacaonet  (GitHub)
Provedor:     GitHub
Acesso:       SSH (chave em ~/.ssh/id_*, sem senha por push)
Branch padrao: main

Ver remote configurado:
  git remote -v

Clone em outra maquina:
  git clone git@github.com:reacaonet/myisp.git myisp

Se o push pedir senha: a chave SSH nao esta registrada no GitHub (ver secao 4).

## 2. Identidade git (ja gravada no projeto, .git/config local)

  user.name  = Cristian Nunes Macena
  user.email = 6474584+reacaonet@users.noreply.github.com

O user.email usa o noreply do GitHub (nao expoe o email real, mas o GitHub
associa do mesmo jeito ao perfil reacaonet).

Consultar:   git config --get user.name / git config --get user.email

## 3. Comandos do dia a dia

  git add -A
  git commit -m "feat: descricao"
  git push            (origin main ja tem upstream definido)

Equivale com -C (fora da pasta):
  git -C <caminho> add -A
  git -C <caminho> commit -m "msg"
  git -C <caminho> push origin main

## 4. Se o push pedir senha / "could not read from remote repository"

  a) gerar chave:        ssh-keygen -t ed25519 -C "reacaonet"
  b) adicionar ao agent: ssh-add ~/.ssh/id_ed25519
  c) copiar a pub:       type ~/.ssh/id_ed25519.pub
  d) colar em GitHub > Settings > SSH and GPG keys > New SSH key
  e) testar:             ssh -T git@github.com   (deve responder "Hi reacaonet!")

## 5. Stack Docker (Rancher Desktop, nao Docker Desktop)

Engine = Rancher Desktop. docker.exe NAO esta no PATH; usar caminho completo:
  C:\Program Files\Rancher Desktop\resources\resources\win32\bin\docker.exe

Subir a stack:
  <docker> compose -f docker-compose.yml up -d --build

Servicos (docker-compose.yml):
  app       -> host 8000  (php artisan serve --port=8000, PHP 8.3 no container)
  postgres  -> host 5433  (imagem postgres:16-alpine)
  redis     -> host 6380  (imagem redis:7-alpine)
  container app:  myisp-app   (exec: <docker> exec -i myisp-app sh -lc '...')
  container postgres: myisp-postgres
  container redis:  myisp-redis

Laravel exige PHP ^8.3; XAMPP nativo e PHP 8.0.30 -> NAO roda no Apache host (500
"Composer detected issues"). Usar o container.

## 6. App rodando e primeira entrada

  URL editor:  http://localhost:8000/infra/ftth/editor
  login:       admin@myisp.com   senha original de tentativa: Myisp@2026! (resetada no banco p/ teste)
  (senha foi resetada no banco durante diagnostico; o ideal e trocar/seedar de novo)

## 7. Migracoes no container (quando faltar coluna/tabela)

  <docker> exec -i myisp-app php artisan migrate --force --no-interaction
  Migrations nao devem guardar credenciais reais; seeders so dev (tecnico123 etc).

## 8. Push: lembrete rapido

  1) git add -A
  2) git commit -m "msg"
  3) git push
  4) conferir: git log --oneline -3  e  git status --short --branch