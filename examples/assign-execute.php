<?php
require __DIR__ . "/../vendor/autoload.php";

use NoirApi\Phpsh\Condition;
use NoirApi\Phpsh\Script;

$execute = new Script();
$execute->command('ps', ['-a', '-x', '-w', '-u']);
$execute->pipe();
$execute->command('grep', ['init']);
$execute->pipe()
    ->command('awk', ['\'{print $1}\''])
    ->pipe()
    ->command('head', ['-1']);


$script = (new Script())
    ->shebang();
$script->set('i', $execute);

echo $script->echo('$i');
