---
sidebar_position: 1
slug: /
title: Laravel Model Settings
description: Valores padrão compartilhados e sobrescritas por modelo para modelos Laravel Eloquent.
---

[Voltar ao README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Primeiros passos →](getting-started.md)

# Laravel Model Settings

O Laravel Model Settings armazena valores padrão compartilhados e sobrescritas por modelo em uma
tabela separada do banco de dados. Use-o quando todos os modelos precisarem começar com o mesmo
valor, mas registros individuais puderem sobrescrevê-lo.

O pacote não adiciona colunas de configuração às tabelas dos modelos. As configurações permanecem
independentes do esquema do modelo e são agrupadas pela classe morph do Eloquent.

## Quando usar

| Requisito | Comportamento do pacote |
|-----------|-------------------------|
| Dar o mesmo valor inicial a todos os modelos persistidos | Armazenar um valor padrão no nível da classe |
| Alterar o valor de um modelo | Armazenar uma sobrescrita para esse modelo |
| Remover uma sobrescrita | Voltar a expor o valor padrão da classe |
| Ler vários modelos | Carregar antecipadamente uma relação com valores padrão e sobrescritas |

## Ordem de resolução

Ao ler uma configuração, o pacote retorna o primeiro valor disponível:

1. A sobrescrita do modelo persistido.
2. O valor padrão da classe desse modelo.
3. `null`.

| Origem | `timezone` |
|--------|------------|
| Valor padrão de `User` | `UTC` |
| Sobrescrita do usuário 123 | `Europe/Paris` |
| Valor efetivo do usuário 123 | `Europe/Paris` |
| Valor efetivo de outro usuário persistido | `UTC` |

Remover uma sobrescrita volta a expor o valor padrão. Isso não exclui o valor padrão.

## Operações principais

```php
(new User)->defaultSettings()->set('timezone', 'UTC');

$user->settings()->set('timezone', 'Europe/Paris');

$timezone = $user->settings()->get('timezone');
$settings = $user->settings()->all();

$user->settings()->forget('timezone');
```

`get()` retorna um valor efetivo. `all()` retorna uma `Illuminate\Support\Collection` com os valores
padrão combinados com as sobrescritas.

Valores padrão e sobrescritas usam as mesmas quatro operações: `all()`, `get()`, `set()` e `forget()`.

## Limites do armazenamento

Cada linha é identificada por três valores:

| Valor | Significado |
|-------|-------------|
| `item_type` | Classe morph do modelo pai ou alias do morph map |
| `item_id` | Chave primária do modelo pai ou o valor reservado `0` para padrões da classe |
| `key` | Nome da configuração |

Isso mantém os valores padrão independentes para cada classe de modelo. Um padrão de `User` nunca se
torna um padrão de `Post`, mesmo quando as duas classes usam a mesma chave de configuração.

## Modelos compatíveis

O pacote aceita modelos Eloquent com chaves primárias inteiras, string, UUID ou ULID. Os modelos
também podem usar um morph map do Laravel.

Configurações por modelo pertencem a modelos persistidos. Um modelo não persistido não herda os
valores padrão: `get()` retorna `null`, e `all()` retorna uma coleção vazia. Chamar `set()` ou
`forget()` para um proprietário não persistido lança `InvalidSettingsOwnerException` antes de uma
consulta ao armazenamento.

Os payloads são armazenados como JSON. Sem uma conversão configurada, as leituras retornam arrays
decodificados ou valores escalares. As [conversões de payload](payload-casts.md) podem retornar
objetos específicos da aplicação.

## Veja também

- [Primeiros passos](getting-started.md) — instale o pacote e configure um modelo.
- [Trabalhando com configurações](settings.md) — gerencie padrões, sobrescritas, chaves e valores.
- [Referência da API](api-reference.md) — consulte todos os métodos públicos e tipos de retorno.
