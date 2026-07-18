---
sidebar_position: 2
title: Primeiros passos
description: Instale o Laravel Model Settings e armazene o primeiro valor padrão e a primeira sobrescrita.
---

[← Visão geral](index.md) · [Voltar ao README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Trabalhando com configurações →](settings.md)

# Primeiros passos

## Requisitos

- PHP 8.3 ou mais recente.
- Laravel 12 ou 13.

## Instalar o pacote

```bash
composer require dragon-code/laravel-model-settings
```

O Laravel detecta automaticamente o service provider do pacote.

Publique a configuração e a migration, depois crie a tabela de configurações:

```bash
php artisan vendor:publish --tag="model_settings"
php artisan migrate
```

A tag `model_settings` publica `config/model_settings.php` e a migration do pacote. Por padrão, a
migration cria uma tabela `settings` na conexão padrão da aplicação com o banco de dados.

## Adicionar a trait

Adicione `HasSettings` a cada modelo Eloquent que precise de configurações:

```php
namespace App\Models;

use DragonCode\LaravelModelSettings\Concerns\HasSettings;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasSettings;
}
```

A trait adiciona os seguintes métodos públicos:

| Membro | Uso |
|--------|-----|
| `settings()` | Ler ou alterar as configurações efetivas de um modelo persistido |
| `defaultSettings()` | Ler ou alterar os valores padrão da classe do modelo |
| `modelSettings()` | Relação Eloquent usada para carregamento antecipado |

## Armazenar a primeira configuração

Crie um valor padrão para todos os modelos `User` persistidos:

```php
(new User)->defaultSettings()->set('timezone', 'UTC');
```

Sobrescreva esse valor para um usuário persistido:

```php
$user = User::query()->firstOrFail();

$user->settings()->set('timezone', 'Europe/Paris');

assert($user->settings()->get('timezone') === 'Europe/Paris');
```

Leia todas as configurações efetivas como uma coleção indexada pelo nome da configuração:

```php
$settings = $user->settings()->all();

assert($settings->get('timezone') === 'Europe/Paris');
```

Remova a sobrescrita para voltar a usar `UTC`:

```php
$user->settings()->forget('timezone');

assert($user->settings()->get('timezone') === 'UTC');
```

## Persista os modelos primeiro

Use `settings()->set()` e `settings()->forget()` somente depois que o modelo pai for persistido. Para
um modelo não persistido, `settings()->get()` retorna `null`, e `settings()->all()` retorna uma
coleção vazia mesmo quando a classe tem valores padrão. Os dois métodos de alteração lançam
`DragonCode\LaravelModelSettings\Exceptions\InvalidSettingsOwnerException` antes de uma consulta ao
armazenamento.

## Veja também

- [Trabalhando com configurações](settings.md) — entenda precedência, exclusão, chaves e valores.
- [Configuração](configuration.md) — selecione a conexão, a tabela ou o modelo de armazenamento.
- [Carregamento antecipado](eager-loading.md) — carregue configurações de coleções de forma eficiente.
