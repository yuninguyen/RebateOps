<?php
require __DIR__ . '/vendor/autoload.php';

use Filament\Navigation\NavigationGroup;

$methods = get_class_methods(NavigationGroup::class);
echo implode("\n", $methods);
