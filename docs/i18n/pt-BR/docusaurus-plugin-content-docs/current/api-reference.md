---
sidebar_position: 7
title: Referência da API
description: Métodos públicos da trait, do serviço e da relação fornecidos pelo Laravel Model Settings.
---

[← Conversões de payload](payload-casts.md) · [Voltar ao README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Desenvolvimento →](development.md)

# Referência da API

## Trait HasSettings

| Método | Retorna | Finalidade |
|--------|---------|------------|
| `settings()` | `SettingsService` | Acessar as configurações efetivas deste modelo |
| `defaultSettings()` | `SettingsService` | Acessar os valores padrão compartilhados desta classe de modelo |
| `modelSettings()` | `Relation` do Eloquent | Carregar valores padrão e sobrescritas como uma relação |

Use a relação `modelSettings` somente com `with()`, `load()` ou `loadMissing()` e como a propriedade
carregada resultante. Não use a consulta da relação como uma API alternativa de leitura ou CRUD. Use
os dois métodos de serviço para ler ou alterar valores. Em tempo de execução, a relação é uma
`SettingsRelation` do pacote, baseada na relação `MorphMany` do Laravel.

## SettingsService

| Método | Retorna | Comportamento |
|--------|---------|---------------|
| `all()` | `Collection` | Retorna os valores padrão combinados com as sobrescritas do modelo |
| `get(int\|string\|UnitEnum $key)` | `mixed` | Retorna uma sobrescrita, seu valor padrão ou `null` |
| `set(int\|string\|UnitEnum $key, mixed $value)` | `void` | Cria, substitui ou remove uma configuração vazia |
| `setMany(iterable $values)` | `void` | Faz upsert de valores preenchidos e remove valores vazios em um lote limitado |
| `forget(int\|string\|UnitEnum $key)` | `void` | Remove uma configuração se ela existir |
| `forgetMany(iterable $keys)` | `void` | Remove as chaves informadas do escopo atual |
| `purge()` | `void` | Remove todas as configurações armazenadas no escopo atual |

Os métodos com chave aceitam backed enums e pure unit enums. O Laravel converte backed enums para
seu valor subjacente e pure unit enums para o nome do case.

`SettingsService` não tem um parâmetro de valor alternativo fornecido pelo chamador em `get()` nem
um método `has()` separado. Use `all()->has($key)` para testar se uma chave efetiva existe.

## Matriz de resolução

| Sobrescrita do modelo | Padrão da classe | Resultado de `get()` | Incluído em `all()` |
|-----------------------|------------------|----------------------|---------------------|
| Presente | Presente | Sobrescrita | Sobrescrita |
| Presente | Ausente | Sobrescrita | Sobrescrita |
| Ausente | Presente | Padrão | Padrão |
| Ausente | Ausente | `null` | Nenhuma entrada |

Para um modelo não persistido, `get()` retorna `null` e `all()` retorna uma coleção vazia. Somente
modelos persistidos herdam os valores padrão da classe.

## all

```php
$settings = $user->settings()->all();

$timezone = $settings->get('timezone');
$hasTimezone = $settings->has('timezone');
```

O resultado é uma `Illuminate\Support\Collection` indexada pela chave da configuração. Para
configurações de modelo, as sobrescritas substituem os valores padrão com a mesma chave.

## get

```php
$timezone = $user->settings()->get('timezone');
```

O resultado é o valor efetivo decodificado ou convertido. Quando a sobrescrita não existe, o valor
padrão é usado. Se nem a sobrescrita nem o padrão existirem, o resultado será `null`. A assinatura
intencionalmente não aceita um segundo argumento de valor alternativo.

## set

```php
$user->settings()->set('timezone', 'Europe/Paris');
```

O método valida o proprietário e então executa uma operação update-or-create para o tipo do modelo,
o identificador e a chave. Um valor considerado vazio pelo Laravel remove a linha. A validação ocorre
antes da seleção do caminho de valor vazio. Em ambos os caminhos, a relação `modelSettings` carregada
é limpa para que a próxima leitura não reutilize dados desatualizados.

## setMany

```php
$user->settings()->setMany([
    'timezone' => 'Europe/Paris',
    'locale' => 'fr',
    'obsolete' => null,
]);
```

As chaves do iterable usam a mesma normalização de `set()`. Se várias chaves de entrada forem
normalizadas para a mesma string, o último valor vence. Valores preenchidos usam um upsert nativo do
banco; valores vazios usam uma exclusão. Quando os dois grupos existem, ambas as operações são
executadas em uma transação. O método valida o proprietário antes de consumir o iterable e limpa
`modelSettings` uma vez depois do sucesso.

## forget

```php
$user->settings()->forget('timezone');
```

Para um proprietário válido, o método é seguro quando a chave não existe. Remover uma sobrescrita
não remove seu valor padrão compartilhado. A relação carregada é limpa depois da exclusão.

## forgetMany

```php
$user->settings()->forgetMany(['timezone', 'locale']);
```

O método normaliza e elimina duplicatas do iterable, então remove somente essas chaves do escopo
atual com uma exclusão. Chaves ausentes não têm efeito. Ele retorna `void` e limpa a relação carregada
depois de uma chamada bem-sucedida, inclusive quando o iterable está vazio.

## purge

```php
$user->settings()->purge();
```

Em `settings()`, o método exclui todas as sobrescritas pertencentes ao proprietário persistido. Ele
nunca exclui valores padrão da classe nem sobrescritas de outro proprietário. Em
`defaultSettings()`, exclui todos os valores padrão dessa classe de modelo e mantém as sobrescritas
dos modelos. Ele retorna `void` e limpa uma relação carregada depois do sucesso.

## defaultSettings

O serviço retornado por `defaultSettings()` tem os mesmos sete métodos:

```php
$defaults = (new User)->defaultSettings();

$defaults->set('timezone', 'UTC');
$defaults->setMany(['timezone' => 'UTC', 'locale' => 'en']);
$timezone = $defaults->get('timezone');
$all = $defaults->all();
$defaults->forget('timezone');
$defaults->forgetMany(['timezone', 'locale']);
$defaults->purge();
```

## Exceções

`DragonCode\LaravelModelSettings\Exceptions\InvalidSettingsOwnerException` estende a classe PHP
`DomainException`. Toda alteração por meio de `settings()` lança essa exceção antes de uma consulta
ao armazenamento quando uma destas condições é verdadeira:

- O modelo proprietário não foi persistido, inclusive quando recebeu uma chave antecipadamente.
- A chave do proprietário persistido é o inteiro `0` ou a string `'0'`, o que conflita com o valor
  sentinela usado pelos padrões da classe na versão 1.x.

Essa validação também ocorre antes do consumo de um iterable em lote. Alterações por meio de
`defaultSettings()` continuam válidas porque esse serviço seleciona explicitamente o escopo dos
valores padrão da classe. A leitura permanece determinística: um proprietário não persistido retorna
`null` ou uma coleção vazia sem consultar sobrescritas, enquanto um proprietário persistido com a
chave `0` pode ler os valores padrão da classe, mas não pode alterá-los como sobrescritas do modelo.

`DragonCode\LaravelModelSettings\Exceptions\InvalidPayloadCast` é lançada quando uma conversão
configurada para todo o modelo ou por chave está ausente, tem um tipo inválido, não implementa um
contrato compatível ou não pode ser resolvida pelo container do Laravel. Sua mensagem pode identificar
o modelo pai, a chave da configuração e a classe da conversão, mas nunca o payload.

Se uma operação mista de `setMany()` falhar, a transação reverte suas escritas e exclusões. A exceção
é relançada, e a relação `modelSettings` carregada existente não é limpa.

## Veja também

- [Trabalhando com configurações](settings.md) — entenda o comportamento de cada operação.
- [Carregamento antecipado](eager-loading.md) — use `modelSettings` sem consultas N+1.
- [Conversões de payload](payload-casts.md) — controle os valores retornados por `get()` e `all()`.
