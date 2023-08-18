<?php
require __DIR__ . "/../vendor/autoload.php";

use NoirApi\Phpsh\Condition;
use NoirApi\Phpsh\Script;

$lessThan10 = Condition::create('$i')->lessThan(10);
$script = new Script();

$script->set('i', 0);
echo $script
    ->while($lessThan10, function (Script $script) {
        $script->printf('$i\n')
            ->command('ps', [ '-a', "-v"], true)
            ->increment('i');
    })
    ->generate();