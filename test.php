<?php
require 'vendor/autoload.php';

use ApiCrumbs\Core\ApiCrumbs;
use ApiCrumbs\Providers\Weather\OpenMeteoProvider;

$crumbs = new ApiCrumbs();

$crumbs->registerProvider(new OpenMeteoProvider(['verify' => 'C:\xampp8.2\php\extras\ssl\cacert.pem']));

echo $crumbs->build('51.5074,-0.1278');