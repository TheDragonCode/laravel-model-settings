---
sidebar_position: 3
title: Trabalhando com configurações
description: Gerencie valores padrão compartilhados, sobrescritas por modelo, chaves e valores de configuração.
---

[← Primeiros passos](getting-started.md) · [Voltar ao README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Carregamento antecipado →](eager-loading.md)

# Trabalhando com configurações

O mesmo serviço gerencia valores padrão e valores dos modelos. O ponto de entrada determina qual
escopo é lido ou alterado:

| Ponto de entrada | Escopo |
|------------------|--------|
| `(new User)->defaultSettings()` | Valores padrão compartilhados pelos modelos `User` persistidos |
| `$user->settings()` | Configurações efetivas de um usuário persistido |

## Valores padrão compartilhados

Os valores padrão são aplicados a todos os modelos persistidos com a mesma classe morph do Eloquent:

```php
$defaults = (new User)->defaultSettings();

$defaults->set('timezone', 'UTC');
$defaults->set('notifications', ['email' => true]);
```

Leia ou remova valores padrão usando o mesmo serviço:

```php
$timezone = $defaults->get('timezone');
$all = $defaults->all();

$defaults->forget('timezone');
```

Os valores padrão são independentes para cada classe de modelo.

## Sobrescritas por modelo

`set()` cria uma configuração ou substitui o valor existente:

```php
$user->settings()->set('timezone', 'Europe/Paris');
$user->settings()->set('timezone', 'America/Toronto');
```

Somente a configuração desse modelo é alterada. Os demais modelos continuam usando sua própria
sobrescrita ou o valor padrão compartilhado.

`get()` e `all()` resolvem os valores com a mesma precedência:

```php
$timezone = $user->settings()->get('timezone');
$settings = $user->settings()->all();
```

`all()` retorna uma `Illuminate\Support\Collection` indexada pelas chaves das configurações.

Por exemplo, uma sobrescrita substitui somente o valor padrão correspondente:

```php
(new User)->defaultSettings()->set('timezone', 'UTC');
(new User)->defaultSettings()->set('locale', 'en');

$user->settings()->set('timezone', 'Europe/Paris');

assert($user->settings()->all()->sortKeys()->all() === [
    'locale' => 'en',
    'timezone' => 'Europe/Paris',
]);
```

## Remover um valor

Remover uma sobrescrita do modelo volta a expor o valor padrão:

```php
(new User)->defaultSettings()->set('timezone', 'UTC');

$user->settings()->set('timezone', 'Europe/Paris');
$user->settings()->forget('timezone');

assert($user->settings()->get('timezone') === 'UTC');
```

Para remover o próprio valor padrão, chame `forget()` por meio de `defaultSettings()`:

```php
(new User)->defaultSettings()->forget('timezone');
```

Chamar `forget()` para uma chave inexistente não tem efeito.

## Valores vazios

`set()` usa o helper `blank()` do Laravel. Um valor vazio remove a configuração em vez de armazená-la.

| Valor | Resultado |
|-------|-----------|
| `null` | Removido |
| `''` ou string contendo apenas espaços | Removido |
| `[]` | Removido |
| `0` | Armazenado |
| `false` | Armazenado |
| `'0'` | Armazenado |

O pacote não consegue persistir um valor intencionalmente vazio por meio de `set()`.

## Chaves de configuração

As chaves podem ser strings, inteiros ou enums PHP que implementem `UnitEnum`:

```php
enum SettingKey: string
{
    case Timezone = 'timezone';
}

$user->settings()->set(SettingKey::Timezone, 'Europe/Paris');

$timezone = $user->settings()->get(SettingKey::Timezone);
```

O Laravel armazena um backed enum pelo seu valor subjacente e um pure unit enum pelo nome do case.
Use a mesma chave ou case ao ler, substituir ou remover uma configuração.

O pacote não valida o conteúdo da chave. A API pública e o esquema padrão aceitam chaves vazias e
chaves contendo apenas espaços.

## Identificadores de modelo

Chaves primárias inteiras, string, UUID e ULID são compatíveis.

Alterações nas configurações de um modelo exigem um proprietário persistido com uma chave diferente
de `null`. Para um modelo não persistido, `get()` retorna `null`, e `all()` retorna uma coleção vazia
sem consultar as sobrescritas do modelo. Seus métodos `set()` e `forget()` lançam
`InvalidSettingsOwnerException` antes de uma consulta ao armazenamento.

O inteiro `0` e a string `'0'` são reservados para valores padrão compartilhados na versão 1.x. Um
modelo persistido com qualquer uma dessas chaves pode ler os valores padrão da classe, mas `set()` e
`forget()` lançam `InvalidSettingsOwnerException`. Outras chaves string, incluindo `'00'`, continuam
válidas.

As configurações são armazenadas usando a classe morph atual do modelo. Adicionar ou alterar um alias
do morph map depois que as configurações forem gravadas exige a atualização dos valores `item_type`
existentes.

## Veja também

- [Carregamento antecipado](eager-loading.md) — evite uma consulta de configurações por modelo.
- [Conversões de payload](payload-casts.md) — retorne objetos de domínio em vez de JSON decodificado.
- [Referência da API](api-reference.md) — consulte assinaturas e valores de retorno.
