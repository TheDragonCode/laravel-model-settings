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

O formato legado para todo o modelo aplica uma conversão a todas as configurações pertencentes a uma
classe de modelo pai:

```php
'casts' => [
    App\Models\User::class => App\Casts\UserSettingsPayloadCast::class,
],
```

Use um mapa por chave quando somente chaves exatas precisarem de tratamento personalizado:

```php
'casts' => [
    App\Models\User::class => [
        'profile' => App\Data\ProfileData::class,
        'billing.credentials' => App\Casts\EncryptedSettingPayload::class,
    ],
],
```

Os aliases do morph map do Laravel são resolvidos de volta para a classe do modelo pai antes da
seleção. A correspondência usa a chave armazenada da configuração, não o nome do atributo Eloquent
`payload`. Pontos são literais, então `billing.credentials` é uma única chave. Chaves ausentes de um
mapa por chave usam o JSON normal.

Uma classe configurada deve implementar `CastsAttributes` ou estender `Spatie\LaravelData\Data`.
Classes configuradas inválidas, ausentes, incompatíveis ou que o container não consegue resolver
lançam `InvalidPayloadCast`; o pacote não volta silenciosamente ao JSON simples para uma entrada
configurada.

## Ciclo de vida da conversão

Para uma implementação de `CastsAttributes`, o pacote executa esta sequência:

| Direção | Sequência |
|---------|-----------|
| Escrita | Chamar o `set()` personalizado e codificar o resultado como JSON |
| Leitura | Passar a string JSON armazenada para o `get()` personalizado |

O argumento `$model` é o modelo de armazenamento configurado, não o modelo pai `User` ou `Post`.
O pacote resolve implementações de `CastsAttributes` pelo container do Laravel, então dependências do
construtor podem usar os bindings normais do container.

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

## Criptografia por chave

A criptografia pertence a uma conversão da aplicação porque o esquema do pacote não tem metadados de
criptografia nem um contrato de rotação de chaves. Esta conversão criptografa uma chave de
configuração e mantém todas as outras no caminho JSON normal:

```php
namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

final class EncryptedSettingPayload implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $ciphertext = Json::decode($value);

        return Json::decode(Crypt::decryptString((string) $ciphertext));
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return Crypt::encryptString(Json::encode($value));
    }
}
```

Registre-a para uma chave literal exata:

```php
'casts' => [
    App\Models\User::class => [
        'billing.credentials' => App\Casts\EncryptedSettingPayload::class,
    ],
],
```

```php
$user->settings()->set('billing.credentials', $credentials);

$credentials = $user->settings()->get('billing.credentials');
```

Não registre o valor em log antes nem depois da conversão. Se as chaves de criptografia puderem
mudar, defina e teste uma política de rotação no nível da aplicação antes de armazenar dados de
produção. Não adicione colunas de metadados à tabela do pacote sem um contrato de armazenamento
separado que defina versionamento e rotação.

## Spatie Laravel Data

Quando `spatie/laravel-data` estiver instalado, uma classe `Data` poderá ser usada diretamente:

```bash
composer require spatie/laravel-data:^4.23
```

```php
'casts' => [
    App\Models\User::class => [
        'preferences' => App\Data\UserSettingsData::class,
    ],
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

Outras chaves do mesmo modelo continuam usando o JSON normal. Use o formato legado para todo o modelo
somente quando cada payload desse modelo pai for uma entrada válida para a classe de dados configurada.

## Erros de conversão

`DragonCode\LaravelModelSettings\Exceptions\InvalidPayloadCast` identifica a classe do modelo pai, a
chave da configuração e a conversão configurada quando a resolução falha. Ele nunca inclui o payload.
A exceção é lançada em escritas simples e em lote, e também em leituras de valores persistidos que
usam essa entrada configurada.

## Veja também

- [Configuração](configuration.md) — registre conversões e substitua o modelo de armazenamento.
- [Trabalhando com configurações](settings.md) — veja quais valores vazios são removidos.
- [Referência da API](api-reference.md) — confira os tipos retornados por `get()` e `all()`.
