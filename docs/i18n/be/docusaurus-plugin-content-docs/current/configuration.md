---
sidebar_position: 5
title: Канфігурацыя
description: Налада мадэлі захоўвання, падключэння да базы даных, табліцы і пераўтварэнняў даных.
---

[← Папярэдняя загрузка](eager-loading.md) · [Вярнуцца да README](https://github.com/TheDragonCode/laravel-model-settings#readme) · [Пераўтварэнні даных →](payload-casts.md)

# Канфігурацыя

## Публікацыя канфігурацыі

```bash
php artisan vendor:publish --tag="model_settings"
```

Каманда публікуе `config/model_settings.php` і міграцыю пакета.

## Даступныя параметры

| Параметр | Значэнне па змаўчанні | Прызначэнне |
|----------|-----------------------|-------------|
| `model` | `DragonCode\LaravelModelSettings\Models\Settings` | Eloquent-мадэль для захаваных налад |
| `connection` | Стандартнае для праграмы | Падключэнне да базы даных для мадэлі і міграцыі |
| `table` | `settings` | Табліца базы даных для мадэлі і міграцыі |
| `casts` | `[]` | Пераўтварэнне даных, выбранае па класе бацькоўскай мадэлі |

Пакет чытае наступныя зменныя асяроддзя:

| Зменная | Значэнне па змаўчанні |
|---------|-----------------------|
| `MODEL_SETTINGS_DATABASE_CONNECTION` | `DATABASE_CONNECTION`, затым стандартнае падключэнне Laravel |
| `MODEL_SETTINGS_DATABASE_TABLE` | `settings` |

Задайце падключэнне і табліцу да запуску міграцыі:

```dotenv
MODEL_SETTINGS_DATABASE_CONNECTION=mysql
MODEL_SETTINGS_DATABASE_TABLE=model_settings
```

Пазнейшае змяненне гэтых параметраў не пераносіць наяўныя запісы.

## Схема захоўвання

Апублікаваная міграцыя стварае наступныя слупкі:

| Слупок | Прызначэнне |
|--------|-------------|
| `id` | Першасны ключ радка налады |
| `item_type` | Morph-клас бацькоўскай мадэлі або псеўданім |
| `item_id` | Ідэнтыфікатар бацькоўскай мадэлі як радок даўжынёй да 36 сімвалаў |
| `key` | Ключ налады |
| `payload` | Даныя, абвешчаныя ў міграцыі як `jsonb` |
| `created_at` і `updated_at` | Часавыя пазнакі Laravel |

Спалучэнне `item_type`, `item_id` і `key` унікальнае.

Стандартны слупок `item_id` захоўвае не больш за 36 сімвалаў. У гэтую схему змяшчаюцца цэлалікавыя,
радковыя, UUID- і ULID-ідэнтыфікатары, калі іх радковае прадстаўленне не даўжэйшае за 36 сімвалаў.
Для даўжэйшага карыстальніцкага першаснага ключа патрэбна адпаведнае змяненне міграцыі.

Значэнне `0` у `item_id` зарэзервавана для налад класа па змаўчанні. У версіі 1.x `set()` і
`forget()` адхіляюць захаванага ўладальніка з цэлалікавым ключом `0` або радковым ключом `'0'`,
выклікаючы `InvalidSettingsOwnerException` да запыту да гэтай табліцы. Калі даныя ўжо існуюць,
змяненне падключэння, назвы табліцы або псеўданімаў morph map патрабуе самастойнага пераносу або
абнаўлення радкоў.

## Замена мадэлі захоўвання

Убудаваная мадэль налад абвешчана як final. Замест наследавання наладзьце замену:

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

Затым абнавіце канфігурацыю:

```php
'model' => App\Models\ApplicationSetting::class,
```

Замена павінна заставацца сумяшчальнай з апублікаванай схемай. Захавайце запаўняльныя атрыбуты і
`PayloadCast`, калі новая мадэль не рэалізуе эквівалентную серыялізацыю.

Як мінімум новая мадэль павінна захоўваць наступныя паводзіны:

| Патрабаванне | Прычына |
|--------------|---------|
| Запаўняць `item_type`, `item_id`, `key` і `payload` | `updateOrCreate()` запісвае гэтыя атрыбуты |
| Выкарыстоўваць наладжаныя падключэнне і табліцу | Міграцыя і рэпазіторый павінны звяртацца да адных радкоў |
| Пераўтвараць `item_id` у `string` | Цэлыя лікі, радкі, UUID і ULID захоўваюцца ў адным слупку |
| Пераўтвараць `payload` праз `PayloadCast` або эквівалент | Чытанне і запіс павінны захоўваць паводзіны JSON |

## Гл. таксама

- [Пачатак працы](getting-started.md) — публікацыя канфігурацыі і міграцыі.
- [Пераўтварэнні даных](payload-casts.md) — налада прыкладных тыпаў даных.
- [Даведнік API](api-reference.md) — публічная паверхня пакета.
