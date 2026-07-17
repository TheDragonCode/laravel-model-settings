---
sidebar_position: 5
title: Configuração
description: Configure o modelo de armazenamento, a conexão com o banco, a tabela e as conversões de payload.
---

[← Carregamento antecipado](eager-loading.md) · [Voltar ao README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Conversões de payload →](payload-casts.md)

# Configuração

## Publicar a configuração

```bash
php artisan vendor:publish --tag="model_settings"
```

Isso publica `config/model_settings.php` e a migration do pacote.

## Opções disponíveis

| Opção | Padrão | Finalidade |
|-------|--------|------------|
| `model` | `DragonCode\LaravelModelSettings\Models\Settings` | Modelo Eloquent usado para as configurações armazenadas |
| `connection` | Padrão da aplicação | Conexão usada pelo modelo e pela migration |
| `table` | `settings` | Tabela usada pelo modelo e pela migration |
| `casts` | `[]` | Conversão de payload selecionada pela classe do modelo pai |

O pacote lê estas variáveis de ambiente:

| Variável | Padrão |
|----------|--------|
| `MODEL_SETTINGS_DATABASE_CONNECTION` | `DATABASE_CONNECTION`, depois a conexão padrão do Laravel |
| `MODEL_SETTINGS_DATABASE_TABLE` | `settings` |

Defina a conexão e a tabela antes de executar a migration:

```dotenv
MODEL_SETTINGS_DATABASE_CONNECTION=mysql
MODEL_SETTINGS_DATABASE_TABLE=model_settings
```

Alterar qualquer um desses valores depois não move os registros existentes.

## Esquema de armazenamento

A migration publicada cria estas colunas:

| Coluna | Finalidade |
|--------|------------|
| `id` | Chave primária da linha de configuração |
| `item_type` | Classe morph ou alias do modelo pai |
| `item_id` | Identificador do modelo pai, armazenado como string de até 36 caracteres |
| `key` | Chave da configuração |
| `payload` | Payload declarado pela migration como `jsonb` |
| `created_at` e `updated_at` | Timestamps do Laravel |

A combinação de `item_type`, `item_id` e `key` é única.

A coluna padrão `item_id` armazena no máximo 36 caracteres. Identificadores inteiros, UUID e ULID
cabem nesse esquema. Uma chave primária personalizada mais longa exige uma alteração correspondente
na migration.

O valor `0` em `item_id` é reservado para os valores padrão da classe. Alterar a conexão, o nome da
tabela ou os aliases do morph map depois que os dados existirem exige mover ou atualizar as linhas
existentes manualmente.

## Substituir o modelo de armazenamento

O modelo de configurações interno é final. Configure uma substituição em vez de estendê-lo:

```php
namespace App\Models;

use DragonCode\LaravelModelSettings\Casts\PayloadCast;
use Illuminate\Database\Eloquent\Model;

final class ApplicationSetting extends Model
{
    protected $fillable = [
        'item_type',
        'item_id',
        'key',
        'payload',
    ];

    public function __construct(array $attributes = [])
    {
        $this->setConnection(config('model_settings.connection'));
        $this->setTable(config('model_settings.table'));

        parent::__construct($attributes);
    }

    protected function casts(): array
    {
        return [
            'item_id' => 'string',
            'payload' => PayloadCast::class,
        ];
    }
}
```

Depois, atualize a configuração:

```php
'model' => App\Models\ApplicationSetting::class,
```

A substituição deve continuar compatível com o esquema publicado. Mantenha os atributos preenchíveis
e o `PayloadCast`, a menos que o novo modelo implemente uma serialização equivalente.

No mínimo, o modelo substituto deve preservar estes comportamentos:

| Requisito | Motivo |
|-----------|--------|
| Preencher `item_type`, `item_id`, `key` e `payload` | `updateOrCreate()` grava esses atributos |
| Usar a conexão e a tabela configuradas | A migration e o repositório devem acessar as mesmas linhas |
| Converter `item_id` para `string` | Inteiros, UUID e ULID compartilham uma coluna |
| Converter `payload` com `PayloadCast` ou equivalente | Leituras e escritas devem preservar o comportamento JSON |

## Veja também

- [Primeiros passos](getting-started.md) — publique a configuração e a migration.
- [Conversões de payload](payload-casts.md) — configure tipos de payload específicos da aplicação.
- [Referência da API](api-reference.md) — consulte a superfície pública do pacote.
