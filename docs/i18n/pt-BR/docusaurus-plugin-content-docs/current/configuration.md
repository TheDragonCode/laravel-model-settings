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
| `casts` | `[]` | Conversões de payload selecionadas pela classe do modelo pai e, opcionalmente, pela chave da configuração |

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

## Configuração das conversões de payload

O formato legado para todo o modelo continua compatível. Uma conversão processa todos os payloads
pertencentes à classe do modelo:

```php
'casts' => [
    App\Models\User::class => App\Casts\UserSettingsPayloadCast::class,
],
```

Use um mapa por chave quando chaves diferentes precisarem de tipos ou tratamentos diferentes:

```php
'casts' => [
    App\Models\User::class => [
        'profile' => App\Data\ProfileData::class,
        'billing.credentials' => App\Casts\EncryptedSettingPayload::class,
    ],
],
```

A correspondência da chave é exata. Pontos não representam caminhos aninhados, e uma chave ausente
do mapa usa a conversão JSON padrão. Cada entrada de modelo é uma string de classe para todo o modelo
ou um mapa por chave; não existe uma entrada curinga dentro desse mapa. Consulte
[Conversões de payload](payload-casts.md) para ver os contratos compatíveis e um exemplo de criptografia.

## Esquema de armazenamento

A migration publicada cria estas colunas:

| Coluna | Finalidade |
|--------|------------|
| `id` | Chave primária da linha de configuração |
| `item_type` | Classe morph ou alias do modelo pai |
| `item_id` | Identificador do modelo pai, armazenado como string de até 36 caracteres |
| `is_default` | Distingue os padrões da classe das sobrescritas dos modelos |
| `key` | Chave da configuração |
| `payload` | Payload declarado pela migration como `jsonb` |
| `created_at` e `updated_at` | Timestamps do Laravel |

A combinação de `item_type`, `item_id`, `is_default` e `key` é única. Um índice de consulta em
`item_type`, `is_default` e `item_id` atende às leituras dos padrões e do escopo do proprietário.

Os valores padrão da classe e as sobrescritas dos modelos compartilham essa tabela. O pacote não cria
uma segunda tabela de valores padrão nem adiciona colunas de metadados de criptografia.

A coluna padrão `item_id` armazena no máximo 36 caracteres. Identificadores inteiros, string, UUID e
ULID cabem nesse esquema quando sua representação como string tem no máximo 36 caracteres. Uma chave
primária personalizada mais longa exige uma alteração correspondente na migration.

Os padrões da classe usam `item_id = '0'` com `is_default = true`. Um proprietário persistido cuja
chave seja o inteiro `0` ou a string `'0'` usa o mesmo `item_id` físico com `is_default = false`.
Assim, as duas linhas podem coexistir para o mesmo tipo de modelo e a mesma chave de configuração.
Alterar a conexão, o nome da tabela ou os aliases do morph map depois que os dados existirem exige
mover ou atualizar as linhas existentes manualmente.

## Atualização a partir de uma versão 1.x anterior

Esta versão altera o contrato de execução além da migration do discriminador de armazenamento:

| Comportamento anterior da 1.x | Comportamento atual | Alteração necessária na aplicação |
|--------------------------------|---------------------|------------------------------------|
| `set($key, null)`, strings vazias, strings de espaços e arrays vazios removiam a linha | `set()` armazena cada valor JSON exatamente | Substitua chamadas de exclusão por `forget($key)` |
| Entradas vazias em `setMany()` eram removidas no mesmo lote | Cada entrada de `setMany()` é armazenada em um único upsert transacional | Mova as chaves removidas para uma chamada separada de `forgetMany()` |
| Chaves vazias ou contendo apenas espaços eram aceitas | Chaves normalizadas vazias lançam `InvalidSettingKey` | Renomeie ou remova chaves inválidas antes da atualização |
| Verificações de existência exigiam `all()->has($key)` | `has($key)` distingue um JSON `null` armazenado de uma chave ausente | Prefira o método específico `has()` |

Uma sobrescrita de modelo com `null` armazenado agora oculta um valor padrão preenchido da classe até
que `forget()` remova a sobrescrita. Conversões personalizadas de payload agora recebem valores
vazios de ambos os setters, e eventos de criação ou atualização de um modelo de armazenamento
personalizado os recebem de `set()`. `get()` continua aceitando somente um argumento; nenhum valor
alternativo do chamador nem alias permanente `put()` foi adicionado.

Depois de atualizar o pacote, publique a nova migration e execute-a com a aplicação em modo de
manutenção:

```bash
php artisan vendor:publish --tag="model_settings"
php artisan migrate
```

A migration adiciona `is_default`, classifica cada linha antiga com `item_id = '0'` como padrão da
classe, cria os índices que incluem o discriminador e então remove o índice único anterior. Ela nunca
escreve chaves ou payloads das configurações na saída da migration.

Os esquemas 1.x anteriores codificavam da mesma forma os padrões da classe e as linhas de um
proprietário real com identificador `0`. Portanto, a migration não consegue distinguir uma
sobrescrita inserida manualmente de um padrão e classifica ambas como padrões. Depois da migration,
revise os dados antigos conhecidos de proprietários com identificador `0` e defina
`is_default = false` nas linhas que forem sobrescritas reais dos modelos.

Não execute a versão antiga do pacote com o esquema atualizado. Ela não grava o discriminador e
armazenaria padrões como sobrescritas. Implante a migration e a versão compatível do pacote na mesma
janela de manutenção.

O rollback só é seguro antes que exista uma sobrescrita real de proprietário com identificador `0`.
A migration para antes de alterar o esquema quando encontra `item_id = '0'` com
`is_default = false`, porque o esquema antigo não pode representar essa linha sem mudar seu sentido.
Remova ou exporte essas sobrescritas antes do rollback. Um rollback seguro restaura o índice único
anterior e remove `is_default`.

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
        'is_default',
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
            'item_id'    => 'string',
            'is_default' => 'boolean',
            'payload'    => PayloadCast::class,
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
| Preencher `item_type`, `item_id`, `is_default`, `key` e `payload` | O armazenamento grava esses atributos |
| Usar a conexão e a tabela configuradas | A migration e o repositório devem acessar as mesmas linhas |
| Converter `item_id` para `string` | Inteiros, strings, UUID e ULID compartilham uma coluna |
| Converter `is_default` para `boolean` | As resoluções lazy e eager devem ler o mesmo discriminador de escopo |
| Converter `payload` com `PayloadCast` ou equivalente | Leituras e escritas devem preservar o comportamento JSON |

## Veja também

- [Primeiros passos](getting-started.md) — publique a configuração e a migration.
- [Conversões de payload](payload-casts.md) — configure tipos de payload específicos da aplicação.
- [Referência da API](api-reference.md) — consulte a superfície pública do pacote.
