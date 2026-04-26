<?php

declare(strict_types=1);

namespace App\Core;

abstract class Middleware
{
    abstract public function handle(): bool;
}
