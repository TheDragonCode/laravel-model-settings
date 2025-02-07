<?php

declare(strict_types=1);

dataset('settings', [
    'empty' => [null],

    'partial' => [
        [
            'foo'    => 'q1',
            'custom' => 'q2',
        ],
    ],

    'full' => [
        [
            'foo' => 'q1',

            'bar' => ['baz' => 'q2'],
            'qwe' => ['rty' => 'q3'],

            'custom' => 'q4',
        ],
    ],
]);
