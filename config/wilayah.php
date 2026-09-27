<?php

return [
    'source_url' => env(
        'WILAYAH_SOURCE_URL',
        'https://raw.githubusercontent.com/cahyadsn/wilayah/master/db/wilayah.sql',
    ),
    'timeout' => (int) env('WILAYAH_SOURCE_TIMEOUT', 60),
];
