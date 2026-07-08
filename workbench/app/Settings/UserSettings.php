<?php

declare(strict_types=1);

namespace Workbench\App\Settings;

final class UserSettings
{
    public function __construct(
        public ?int $ttb_command_index = null,
        public string $po_box = '',
        public int $default_agreement = 3,
        public bool $order_card_payment = false,
        public bool $need_invoices = false,
        public string $localization_code = 'ru',
        public bool $allow_duplicate_inn = false,
    ) {}
}
