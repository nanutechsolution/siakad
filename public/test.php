<?php

dd([
    'intl' => extension_loaded('intl'),
    'NumberFormatter' => class_exists(NumberFormatter::class),
    'php' => PHP_VERSION,
]);
