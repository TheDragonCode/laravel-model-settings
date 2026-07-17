---
sidebar_position: 6
title: Conversões de payload
description: Decodifique payloads de configuração como arrays, valores personalizados ou objetos Spatie Laravel Data.
---

[← Configuração](configuration.md) · [Voltar ao README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Referência da API →](api-reference.md)

# Conversões de payload

## Valores JSON padrão

Sem uma conversão personalizada, o pacote codifica valores não vazios como JSON durante a escrita e
retorna arrays decodificados ou valores escalares durante a leitura.

```php
$user->settings()->set('notifications', [
    'email' => true,
    'push' => false,
]);

$notifications = $user->settings()->get('notifications');
```

Os valores devem ser serializáveis em JSON. Erros de codificação JSON não são suprimidos.

## Seleção da conversão

Conversões personalizadas são configuradas pela classe do modelo pai:

```php
'casts' => [
    App\Models\User::class => App\Casts\UserSettingsPayloadCast::class,
],
```

Uma conversão configurada processa todos os payloads de configuração pertencentes àquela classe de
modelo pai. Os aliases do morph map do Laravel são resolvidos de volta para a classe do modelo antes
da seleção da conversão.

Uma classe configurada deve implementar `CastsAttributes` ou estender `Spatie\LaravelData\Data`.
Outras classes não recebem tratamento personalizado e usam o caminho JSON padrão.

## Ciclo de vida da conversão

Para uma implementação de `CastsAttributes`, o pacote executa esta sequência:

| Direção | Sequência |
|---------|-----------|
| Escrita | Chamar o `set()` personalizado e codificar o resultado como JSON |
| Leitura | Passar a string JSON armazenada para o `get()` personalizado |

O argumento `$model` é o modelo de armazenamento configurado, não o modelo pai `User` ou `Post`.
O pacote instancia a conversão sem argumentos no construtor.

## Conversão de atributo do Eloquent

A conversão pode implementar o contrato `CastsAttributes` do Laravel:

```php
namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

final class UserSettingsPayloadCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        return (array) $value;
    }
}
```

O resultado do `set()` personalizado deve continuar serializável em JSON. Erros de codificação JSON
não são suprimidos.

## Spatie Laravel Data

Quando `spatie/laravel-data` estiver instalado, uma classe `Data` poderá ser usada diretamente:

```bash
composer require spatie/laravel-data:^4.23
```

```php
'casts' => [
    App\Models\User::class => App\Data\UserSettingsData::class,
],
```

Passe para `set()` dados aceitos pela classe ou uma instância de `Data`. `get()` retorna uma instância
de dados, e `all()` retorna uma coleção contendo instâncias de dados.

```php
$preferences = UserSettingsData::from([
    'timezone' => 'Europe/Paris',
    'notifications' => true,
]);

$user->settings()->set('preferences', $preferences);

$preferences = $user->settings()->get('preferences');
```

A conversão é selecionada por classe de modelo pai, e não por chave. Todo payload desse modelo deve
ser uma entrada válida para a conversão configurada.

## Veja também

- [Configuração](configuration.md) — registre conversões e substitua o modelo de armazenamento.
- [Trabalhando com configurações](settings.md) — veja quais valores vazios são removidos.
- [Referência da API](api-reference.md) — confira os tipos retornados por `get()` e `all()`.
