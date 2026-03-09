<?php
require 'vendor/autoload.php';

use ApiCrumbs\Core\ApiCrumbs;
use ApiCrumbs\Providers\Geo\PostcodeProvider;

$engine = new ApiCrumbs();
$engine->registerProvider(new PostcodeProvider());

echo $engine->build('SW1A 1AA');