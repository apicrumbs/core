<?php
require 'vendor/autoload.php';

use ApiCrumbs\Core\ApiCrumbs;
use ApiCrumbs\Providers\Geo\PostcodeIoProvider;

$engine = new ApiCrumbs();
$engine->registerProvider(new PostcodeIoProvider($guzzleConfig = ['verify' => false]));

echo $engine->build('SW1A     1AA');