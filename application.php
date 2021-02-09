#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use App\CommissionApp\Commands\CalculateCommission;

$application = new Application();

// ... register commands

$application->add(new CalculateCommission());

$application->run();
