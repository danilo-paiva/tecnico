# Roteiro de testes e apresentação — 3º bimestre de PAW

Ordem sugerida (bate com os 5 itens da ficha, 2,0 cada).

## Preparar

1. `mysql -u root < docs/banco.sql` (banco antigo: `docs/migracao-admin.sql`)
2. `composer install`
3. `composer start`
4. `composer test` → esperado: **OK (40 testes, 70 assertions)**

## 1. Login com JWT (2,0)

1. Abrir `http://localhost:8080/login.html`.
2. Entrar com `ana@email.com` / `123456` → vai para o painel, mostra
   "Olá, Ana Souza (administrador)".
3. Abrir o DevTools (F12 → Application → Local Storage): existe `token` e `participante`.
4. Aba Network: o `POST /login` devolveu `{"participante", "token"}` e os `GET .../count`
   enviaram o header `Authorization: Bearer ...`.

## 2. Recurso sem token é bloqueado (2,0)

- Sem login: `curl http://localhost:8080/locais` → `401 Acesso não autorizado`.
- Com token errado: `curl http://localhost:8080/locais -H "Authorization: Bearer xxx"` → `401`.
- Insomnia: pasta **Auth (JWT)** → **Login** → copiar `data.token` para a variável
  `token` do ambiente; as demais requisições já mandam o Bearer.

## 3. Cadastro via interface (POST nas entidades) (2,0)

Em cada página (`locais.html`, `eventos.html`, `ingressos.html`,
`participantes.html`, `compras.html`), preencher o formulário do topo e clicar
**Salvar** → mensagem "Cadastro realizado com sucesso!" e a linha aparece na tabela.

## 4. Listagem (GET de todas as entidades) (2,0)

- `dashboard.html` mostra os 5 contadores (`GET .../count` com JWT).
- Cada página tem a tabela completa (`GET /recurso` com JWT).

## 5. Atualizar e excluir (2,0)

- **Editar**: botão na linha → abre o modal de edição → **Salvar alterações**
  (`PUT /recurso/{id}`) → mensagem de sucesso e a tabela recarrega.
- **Excluir**: botão na linha → confirma → linha some (`DELETE /recurso/{id}`).
- Regras de negócio continuam valendo e aparecem como mensagem: local com evento,
  evento com ingresso, ingresso/participante com compra não excluem; compra sem
  estoque é recusada; excluir compra devolve o estoque.

## 6. Perfil administrador x comum (extra da aula paw03x01)

- Sair e entrar com `bruno@email.com` / `123456` → topo mostra "(comum)",
  formulários e botões somem (menos o de compra), tabelas continuam listando.
- Como comum, tentar `POST /locais` (Insomnia ou curl) → `403 Acesso negado`.
- Como comum, comprar em `compras.html` → funciona (`POST /compras` liberado).
- Como admin, `participantes.html` permite trocar o `perfil` de alguém.
