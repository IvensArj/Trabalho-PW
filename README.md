# Trabalho-PW

Aplicacao CRUD em PHP com MySQL, pronta para rodar com Docker.

## Rodando com Docker

1. Instale o Docker e o Docker Compose.
2. Na raiz do projeto, inicie os containers:

```bash
docker compose up --build
```

3. Abra a aplicacao em:

```text
http://localhost:8080
```

## No GitHub Codespaces

1. Abra o repositorio em um Codespace.
2. No terminal do Codespace, rode:

```bash
docker compose up --build
```

3. Aguarde o MySQL terminar a inicializacao na primeira execucao.
4. Abra a porta 8080 no painel de portas do Codespace.
5. Use a URL encaminhada para acessar a aplicacao.

## Observacoes

- O arquivo `banco.sql` e carregado automaticamente na criacao inicial do banco.
- As credenciais do ambiente sao valores de desenvolvimento e podem ser sobrescritas por variaveis de ambiente ou por um arquivo `.env`.
- Se precisar recriar o banco do zero, pare os containers e remova os volumes:

```bash
docker compose down -v
```
