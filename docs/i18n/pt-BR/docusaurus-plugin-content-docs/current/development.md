---
sidebar_position: 8
title: Desenvolvimento
description: Execute os testes, valide a documentação, contribua ou reporte um problema de segurança.
---

[← Referência da API](api-reference.md) · [Voltar ao README](https://github.com/TheDragonCode/laravel-model-settings#readme)

# Desenvolvimento

## Verificações do pacote

Instale as dependências PHP:

```bash
composer install
```

Execute a suíte de testes ou gere a cobertura:

```bash
composer test
composer test:coverage
```

Aplique o estilo de código configurado:

```bash
composer style
```

As suítes Pest cobrem contratos diferentes:

| Suíte | Cobertura |
|-------|-----------|
| `tests/Feature` | Padrões, sobrescritas, exclusão, dados ausentes e propriedade |
| `tests/Unit/Casts` | JSON padrão, conversões personalizadas, morph maps e Laravel Data |
| `tests/Unit/KeyTypes` | Chaves string, inteiras, backed enum e pure unit enum |
| `tests/Unit/PrimaryKeyTypes` | Identificadores pais inteiros, UUID e ULID |
| `tests/Unit/QueryCount` | Contagens de consultas de leitura e escrita, incluindo carregamento antecipado |
| `tests/Architecture` | Namespaces, tipos, rigor e regras de arquitetura do Laravel |

## Verificações da documentação

O site Docusaurus exige Node.js 20 ou mais recente. Instale as dependências no diretório `docs`:

```bash
npm ci
```

| Tarefa | Comando |
|--------|---------|
| Iniciar o site local | `npm run start` |
| Verificar o TypeScript | `npm run typecheck` |
| Verificar as traduções | `npm run check:i18n` |
| Criar uma build de produção | `npm run build` |

A build de produção valida os links internos de cada locale configurado.

Mantenha as páginas de documentação em `docs/docs`. Cada página usa front matter para a ordem na
barra lateral, uma linha de navegação no início, links relativos entre os guias e uma seção
`Veja também` no final.

Mantenha cada locale que não seja o padrão em
`docs/i18n/<locale>/docusaurus-plugin-content-docs/current`. Cada locale deve conter os mesmos caminhos
de página que `docs/docs`. O comando `npm run check:i18n` verifica isso antes da build de produção.

## Contribuição

Siga o [guia de contribuição](https://github.com/TheDragonCode/.github/blob/main/CONTRIBUTING.md)
antes de abrir um pull request.

## Segurança

Reporte problemas de segurança de forma privada para
[helldar@dragon-code.pro](mailto:helldar@dragon-code.pro).

## Créditos

Criado por [Andrey Helldar](https://github.com/andrey-helldar) e pelos
[colaboradores do projeto](https://github.com/TheDragonCode/laravel-model-settings/graphs/contributors).

## Veja também

- [Primeiros passos](getting-started.md) — instale o pacote em uma aplicação Laravel.
- [Configuração](configuration.md) — entenda os arquivos publicados pelo pacote.
- [Referência da API](api-reference.md) — revise a API pública antes de alterar o comportamento.
