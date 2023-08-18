<?php
require __DIR__ . "/../vendor/autoload.php";

use NoirApi\Phpsh\Condition;
use NoirApi\Phpsh\Script;

$script = new Script();

echo $script
    ->shebang()
    ->command('ps', ['axwu'])
    ->pipe()
    ->command('grep', ['-v grep'])
    ->redirect(1, '&2')
    ->generate();
