<?php

require_once __DIR__.'/../vendor/autoload.php';

use JLaso\ToolsLib\Status;

$status = new Status();

$st1 = $status->getInfo(1);

print "status for id=1 is ".$st1.PHP_EOL;

$status->updateStatus(1, "status-#".rand(10,30));

$st2 = $status->getInfo(1);

print "and now is ".$st2.PHP_EOL;