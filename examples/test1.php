<?php
declare(strict_types=1);
require __DIR__ . "/../vendor/autoload.php";

use NoirApi\Phpsh\Condition;
use NoirApi\Phpsh\Script;

//$script = (new Script())
//    ->shebang()
//    ->rm('/etc/squid/*', true, true)
//    ->chdir('/etc/squid')
//    ->command('unzip', ['/root/squid.zip'])
//    ->if((new Condition())->fileExists('/run/squid.pid'), function (Script $script) {
//        $script->command('/usr/sbin/squid', ['-k reconfigure']);
//    })->else(function (Script $script) {
//        $script->command('/usr/sbin/squid');
//    })
//    ->endif();
//
//echo $script;


$script = (new Script())
    ->shebang('/usr/bin/env', [ 'sh', '-x'])
    ->rm('/etc/squid/*', true, true)
    ->commandWithEnv(
        (new Script())->set('DEBIAN_FRONTEND', 'noninteractive'),
        'apt-get', ['-y', '-q', 'update']
    )
    ->nextLine()->and()->commandWithEnv(
        (new Script())->set('DEBIAN_FRONTEND', 'noninteractive'),
        'apt-get', ['-y', '-q', '--no-install-recommends', 'install',
            'wget',  'curl', 'unzip', 'zip', 'ca-certificates', 'squid']
    )->nextLine()->command(
        '',
        ['lsb-release',  'ca-certificates',  'apt-transport-https', 'software-properties-common',  'gnupg2', 'libtdb1']
    )
    ->nextLine()->and()->set('SQUID_VERSION', (new Script())->command('squid', ['-v'])->pipe()->command('awk', ['\'{print $4}\'']))
    ->nextLine()->and()->set('SQUID_CACHE_DIR', (new Script())->command("ps axuww | grep unattended-upgrade | grep -v grep | awk '{print \$2}'`; if [ -n \"\$pid\" ]; then kill -9 \$pid; fi;"))
    ->semiColon()
    ->nextLine()
    ->echo('$SQUID_VERSION')->redirect(1, '>>', '/root/squid-version.txt')->and()
    ->nextLine()
    ->head('/root/squid-version.txt', 1, true)->pipe()->command('awk', ['\'{print $1}\''])->and()
    ->exit();

//->and()->command('ls', ['-la'])->semiColon();

echo $script . PHP_EOL;
