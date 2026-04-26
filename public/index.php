<?php

declare(strict_types=1);

use App\Core\App;

require_once dirname(__DIR__) . '/app/bootstrap.php';

$app = new App();
$app->run();
