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

Use a relação `modelSettings` com `with()`, `load()` ou `loadMissing()`. Use os dois métodos de serviço
para ler ou alterar valores. Em tempo de execução, a relação é uma `SettingsRelation` do pacote,
baseada na relação `MorphMany` do Laravel.

## SettingsService

| Método | Retorna | Comportamento |
|--------|---------|---------------|
| `all()` | `Collection` | Retorna os valores padrão combinados com as sobrescritas do modelo |
| `get(int\|string\|UnitEnum $key)` | `mixed` | Retorna uma sobrescrita, seu valor padrão ou `null` |
| `set(int\|string\|UnitEnum $key, mixed $value)` | `void` | Cria, substitui ou remove uma configuração vazia |
| `forget(int\|string\|UnitEnum $key)` | `void` | Remove uma configuração se ela existir |

Os métodos com chave aceitam backed enums e pure unit enums. O Laravel converte backed enums para
seu valor subjacente e pure unit enums para o nome do case.

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
```

O resultado é uma `Illuminate\Support\Collection` indexada pela chave da configuração. Para
configurações de modelo, as sobrescritas substituem os valores padrão com a mesma chave.

## get

```php
$timezone = $user->settings()->get('timezone');
```

O resultado é o valor efetivo decodificado ou convertido. Quando a sobrescrita não existe, o valor
padrão é usado. Se nem a sobrescrita nem o padrão existirem, o resultado será `null`.

## set

```php
$user->settings()->set('timezone', 'Europe/Paris');
```

O método executa uma operação update-or-create para o tipo do modelo, o identificador e a chave. Um
valor considerado vazio pelo Laravel remove a linha. Em ambos os caminhos, a relação `modelSettings`
carregada é limpa para que a próxima leitura não reutilize dados desatualizados.

## forget

```php
$user->settings()->forget('timezone');
```

O método é seguro quando a chave não existe. Remover uma sobrescrita não remove seu valor padrão
compartilhado. A relação carregada é limpa depois da exclusão.

## defaultSettings

O serviço retornado por `defaultSettings()` tem os mesmos quatro métodos:

```php
$defaults = (new User)->defaultSettings();

$defaults->set('timezone', 'UTC');
$timezone = $defaults->get('timezone');
$all = $defaults->all();
$defaults->forget('timezone');
```

## Veja também

- [Trabalhando com configurações](settings.md) — entenda o comportamento de cada operação.
- [Carregamento antecipado](eager-loading.md) — use `modelSettings` sem consultas N+1.
- [Conversões de payload](payload-casts.md) — controle os valores retornados por `get()` e `all()`.
