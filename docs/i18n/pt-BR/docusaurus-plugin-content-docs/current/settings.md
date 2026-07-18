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

`get()`, `has()` e `all()` resolvem os valores com a mesma precedência:

```php
$timezone = $user->settings()->get('timezone');
$hasTimezone = $user->settings()->has('timezone');
$settings = $user->settings()->all();
```

`all()` retorna uma `Illuminate\Support\Collection` indexada pelas chaves das configurações.

`get()` aceita somente a chave. Ele retorna primeiro a sobrescrita do modelo, depois o valor padrão
persistente da classe e, por fim, `null`. Ele não aceita um valor alternativo fornecido pelo
chamador. `has()` distingue uma chave ausente de um JSON `null` armazenado:

```php
if ($user->settings()->has('timezone')) {
    $timezone = $user->settings()->get('timezone');
}
```

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

## Alterações em lote

Use `setMany()` e `forgetMany()` quando um escopo precisar de várias alterações:

```php
$user->settings()->setMany([
    'timezone' => 'Europe/Paris',
    'locale' => 'fr',
]);

$user->settings()->forgetMany(['timezone', 'locale']);
```

Os dois métodos aceitam qualquer iterable. `setMany()` normaliza cada chave antes da escrita. Quando
várias chaves de entrada são normalizadas para a mesma chave armazenada, o último valor vence. Todos
os valores são armazenados; somente `forget()` e `forgetMany()` removem linhas.

Um lote não vazio de `setMany()` usa um único upsert nativo dentro de uma transação. `forgetMany()`
remove todas as chaves informadas com uma consulta. A quantidade de consultas é limitada pelo tipo
de operação, não pelo número de chaves.

Use `purge()` para remover todo o escopo atual:

```php
$user->settings()->purge();
```

Em `settings()`, `purge()` remove somente as sobrescritas desse proprietário e volta a expor os
valores padrão persistentes. Em `defaultSettings()`, remove os padrões dessa classe sem excluir as
sobrescritas dos modelos. Os três métodos em lote retornam `void`.

## Valores JSON

`set()` e `setMany()` armazenam valores JSON exatos:

| Valor | Resultado |
|-------|-----------|
| `null` | Armazenado |
| `''` ou string contendo apenas espaços | Armazenado |
| `[]` | Armazenado |
| `0` | Armazenado |
| `false` | Armazenado |
| `'0'` | Armazenado |

Um `null` armazenado é considerado um valor existente. `has($key)` retorna `true`, enquanto
`get($key)` retorna `null`. Uma sobrescrita de modelo com `null` também oculta um valor padrão
preenchido da classe até que a sobrescrita seja removida com `forget()`.

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

Chaves vazias ou contendo apenas espaços lançam
`DragonCode\LaravelModelSettings\Exceptions\InvalidSettingKey`. A validação ocorre depois da
normalização de inteiros e enums para a string armazenada. A exceção e os logs do pacote nunca
incluem a chave rejeitada nem o payload da configuração.

Pontos são caracteres literais. A chave `mail.from.address` é uma única chave opaca e nunca representa
um caminho aninhado:

```php
$user->settings()->set('mail.from.address', 'noreply@example.com');

$address = $user->settings()->get('mail.from.address');
```

## Identificadores de modelo

Chaves primárias inteiras, string, UUID e ULID são compatíveis.

Alterações nas configurações de um modelo exigem um proprietário persistido com uma chave diferente
de `null`. Para um modelo não persistido, `get()` retorna `null`, `has()` retorna `false` e `all()`
retorna uma coleção vazia sem consultar as sobrescritas do modelo. Seus métodos `set()`, `setMany()`,
`forget()`, `forgetMany()` e `purge()` lançam `InvalidSettingsOwnerException` antes de uma consulta
ao armazenamento ou do consumo do iterable.

Modelos persistidos com identificador inteiro `0` ou string `'0'` aceitam as mesmas leituras e
alterações que qualquer outro proprietário persistido. O discriminador de escopo separa suas
sobrescritas dos padrões da classe, mesmo que as duas linhas mantenham `item_id = '0'`. Outras chaves
string, incluindo `'00'`, continuam válidas.

As configurações são armazenadas usando a classe morph atual do modelo. Adicionar ou alterar um alias
do morph map depois que as configurações forem gravadas exige a atualização dos valores `item_type`
existentes.

## Veja também

- [Carregamento antecipado](eager-loading.md) — evite uma consulta de configurações por modelo.
- [Conversões de payload](payload-casts.md) — retorne objetos de domínio em vez de JSON decodificado.
- [Referência da API](api-reference.md) — consulte assinaturas e valores de retorno.
