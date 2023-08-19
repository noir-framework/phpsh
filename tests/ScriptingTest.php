<?php
declare(strict_types=1);

namespace Noir\PhpSh\Tests;

use Noir\PhpSh\Enum\Signal;
use Noir\PhpSh\Script;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ScriptingTest extends TestCase
{

    /** @test */
    public function createNextLine(): void
    {

        $script = (new Script())
            ->echo('test')
            ->nextLine()
            ->generate();

        $this->assertEquals("echo -n test \\\\\n\t", $script);

        $script = (new Script())
            ->echo('test')
            ->nextLine(false)
            ->generate();

        $this->assertEquals("echo -n test \\\\\n", $script);

    }

    /** @test */
    public function createKillCommand(): void
    {

        $script = (new Script())
            ->kill(1, 15)
            ->generate();

        $this->assertEquals('kill -15 1', $script);

        $command = (new Script())
            ->command('kill', [-15, 1])
            ->generate();

        $this->assertEquals($command, $script);

        $script = (new Script())
            ->kill([1, 2, 3], 15)
            ->generate();

        $this->assertEquals('kill -15 1 2 3', $script);

        $command = (new Script())
            ->command('kill', [-15, 1, 2, 3])
            ->generate();

        $this->assertEquals($command, $script);

        $script = (new Script())
            ->kill([1, 2, 3], Signal::SIGINT)
            ->generate();

        $command = (new Script())
            ->command('kill', [-2, 1, 2, 3])
            ->generate();

        $this->assertEquals($command, $script);

    }

    /** @test */
    public function createSemiColon(): void
    {

        $script = (new Script())
            ->echo('test')
            ->semiColon()
            ->generate();

        $this->assertEquals('echo -n test;', $script);

        // Execute it!
        $this->assertEquals('test', shell_exec($script));

        $command = (new Script())
            ->command('echo', ['-n', 'test'])
            ->put(';')
            ->generate();

        $this->assertEquals($command, $script);

        $command = (new Script())
            ->echo('test')
            ->put(';')
            ->generate();

        $this->assertEquals($command, $script);

        $this->expectException(RuntimeException::class);
        (new Script())->semiColon()->generate();


    }

    /** @test */
    public function createTouchCommand(): void
    {

        $script = (new Script())
            ->touch('test.txt')
            ->generate();

        $this->assertEquals('touch test.txt', $script);

        $command = (new Script())
            ->command('touch', ['test.txt'])
            ->generate();

        $this->assertEquals($command, $script);

    }

    /** @test */
    public function createExitCommand(): void
    {

        $script = (new Script())
            ->exit(10)
            ->generate();

        $this->assertEquals('exit 10', $script);

        $script = (new Script())
            ->exit(10)
            ->semiColon()
            ->generate();

        $this->assertEquals('exit 10;', $script);

        $command = (new Script())
            ->command('exit', [10])
            ->put(';')
            ->generate();

        $this->assertEquals($command, $script);

    }

    /** @test */
    public function createAnd(): void
    {

        $script = (new Script())
            ->echo('test')
            ->and()
            ->echo('test2')
            ->generate();

        $this->assertEquals('echo -n test && echo -n test2', $script);

        $command = (new Script())
            ->command('echo', ['-n', 'test'])
            ->and()
            ->command('echo', ['-n', 'test2'])
            ->generate();

        $this->assertEquals($command, $script);

        // Execute it!
        $this->assertEquals('testtest2', shell_exec($script));

        $this->expectException(RuntimeException::class);
        (new Script())->and()->generate();

    }

    /** @test */
    public function createOr(): void
    {

        $script = (new Script())
            ->echo('test')
            ->or()
            ->echo('test2')
            ->generate();

        $this->assertEquals('echo -n test || echo -n test2', $script);

        $command = (new Script())
            ->command('echo', ['-n', 'test'])
            ->or()
            ->command('echo', ['-n', 'test2'])
            ->generate();

        $this->assertEquals($command, $script);

        // Execute it!
        $this->assertEquals('test', shell_exec($script));

        $this->expectException(RuntimeException::class);
        (new Script())->or()->generate();

    }

    /** @test */
    public function testSleepCommand(): void
    {

        $script = (new Script())
            ->sleep(10)
            ->generate();

        $this->assertEquals('sleep 10', $script);

        $command = (new Script())
            ->command('sleep', [10])
            ->generate();

        $this->assertEquals($command, $script);

        $script = (new Script())
            ->sleep('10')
            ->generate();

        $this->assertEquals('sleep 10', $script);

        $command = (new Script())
            ->command('sleep', ['10'])
            ->generate();

        $this->assertEquals($command, $script);

        $this->expectException(RuntimeException::class);
        (new Script())
            ->sleep('test')
            ->generate();

    }

    /** @test */
    public function testSetAndBackTick(): void
    {

        $script = (new Script())
            ->set('CONFIG', (new Script())->backtick('cat /etc/config'))
            ->generate();

        $this->assertEquals('CONFIG=`cat /etc/config`', $script);

        $command = (new Script())
            ->command('CONFIG=`cat /etc/config`')
            ->generate();

        $this->assertEquals($command, $script);

        $script = (new Script())
            ->set('CONFIG', (new Script())->backtick('cat /etc/config'), true)
            ->generate();

        $this->assertEquals('export CONFIG=`cat /etc/config`', $script);

        $command = (new Script())
            ->command('export', ['CONFIG=`cat /etc/config`'])
            ->generate();

        $this->assertEquals($command, $script);

    }

    /** @test */
    public function testShebang(): void
    {

        $script = (new Script())
            ->shebang()
            ->generate();

        $this->assertEquals('#!/bin/sh', $script);

        $command = (new Script())
            ->command('#!/bin/sh')
            ->generate();

        $this->assertEquals($command, $script);

        $script = (new Script())
            ->shebang('/usr/bin/env', ['sh', '-x'])
            ->generate();

        $this->assertEquals('#!/usr/bin/env sh -x', $script);

        $command = (new Script())
            ->command('#!/usr/bin/env sh -x')
            ->generate();

        $this->assertEquals($command, $script);

        $this->expectException(RuntimeException::class);
        (new Script())
            ->sleep(10)
            ->shebang()
            ->generate();

    }

    /** @test  */
    public function testPipe(): void
    {

        $script = (new Script())
            ->echo('test')
            ->pipe()
            ->cat()
            ->generate();

        $this->assertEquals('echo -n test | cat', $script);
        $this->assertEquals('test', shell_exec($script));

        $command = (new Script())
            ->command('echo', ['-n', 'test'])
            ->pipe()
            ->command('cat')
            ->generate();

        $this->assertEquals($command, $script);
        $this->assertEquals('test', shell_exec($command));

        $command = (new Script())
            ->command('echo', ['-n', 'test', '|', 'cat'])
            ->generate();

        $this->assertEquals($command, $script);
        $this->assertEquals('test', shell_exec($command));

        $this->expectException(RuntimeException::class);
        (new Script())
            ->pipe()
            ->generate();

    }

    public function testRedirect(): void
    {

        foreach([1, 2] as $fd) {

            foreach(['>', '>>', '<', '<<', '>&', '<&', '>&-', '<&-'] as $op) {

                $script = (new Script())
                    ->echo('test')
                    ->redirect($fd, $op, '/tmp/test')
                    ->generate();

                $this->assertEquals("echo -n test $fd$op /tmp/test", $script);

                $command = (new Script())
                    ->command('echo', ['-n', 'test', $fd.$op, '/tmp/test'])
                    ->generate();

                $this->assertEquals($command, $script);

            }

        }

        $this->expectException(RuntimeException::class);
        (new Script())
            ->echo('test')
            ->redirect($fd, 'unknown', '/tmp/test')
            ->generate();

    }

    /** @test */
    public function testCatCommand(): void
    {

        $script = (new Script())
            ->cat('/etc/config')
            ->generate();

        $this->assertEquals('cat /etc/config', $script);

        $command = (new Script())
            ->command('cat', ['/etc/config'])
            ->generate();

        $this->assertEquals($command, $script);

    }

    /** @test */
    public function testTacCommand(): void
    {

        $script = (new Script())
            ->tac('/etc/config')
            ->generate();

        $this->assertEquals('tac /etc/config', $script);

        $command = (new Script())
            ->command('tac', ['/etc/config'])
            ->generate();

        $this->assertEquals($command, $script);

    }

    /** @test */
    public function testTailCommandWith(): void
    {

        $script = (new Script())
            ->tail('/test')
            ->generate();

        $this->assertEquals('tail /test', $script);

        $command = (new Script())
            ->command('tail', ['/test'])
            ->generate();

        $this->assertEquals($command, $script);

        $script = (new Script())
            ->tail('/test', 10)
            ->generate();

        $this->assertEquals('tail -n10 /test', $script);

        $command = (new Script())
            ->command('tail', ['-n10', '/test'])
            ->generate();

        $this->assertEquals($command, $script);

        $script = (new Script())
            ->tail('/test', 10, true)
            ->generate();

        $this->assertEquals('tail -c10 /test', $script);

        $command = (new Script())
            ->command('tail', ['-c10', '/test'])
            ->generate();

        $this->assertEquals($command, $script);

        $this->expectException(RuntimeException::class);
        (new Script())
            ->tail('/test', null, true)
            ->generate();

    }

    /** @test */
    public function testTailCommandWithCat(): void
    {

        $script = (new Script())
            ->cat('/etc/config')
            ->pipe()
            ->tail()
            ->generate();

        $this->assertEquals('cat /etc/config | tail', $script);

        $command = (new Script())
            ->command('cat', ['/etc/config', '|', 'tail'])
            ->generate();

        $this->assertEquals($command, $script);

        $script = (new Script())
            ->cat('/etc/config')
            ->pipe()
            ->tail('-')
            ->generate();

        $this->assertEquals('cat /etc/config | tail', $script);

        $command = (new Script())
            ->command('cat', ['/etc/config', '|', 'tail'])
            ->generate();

        $this->assertEquals($command, $script);

    }

    /** @test */
    public function testHeadCommandWith(): void
    {

        $script = (new Script())
            ->head('/test')
            ->generate();

        $this->assertEquals('head /test', $script);

        $command = (new Script())
            ->command('head', ['/test'])
            ->generate();

        $this->assertEquals($command, $script);

        $script = (new Script())
            ->head('/test', 10)
            ->generate();

        $this->assertEquals('head -n10 /test', $script);

        $command = (new Script())
            ->command('head', ['-n10', '/test'])
            ->generate();

        $this->assertEquals($command, $script);

        $script = (new Script())
            ->head('/test', 10, true)
            ->generate();

        $this->assertEquals('head -c10 /test', $script);

        $command = (new Script())
            ->command('head', ['-c10', '/test'])
            ->generate();

        $this->assertEquals($command, $script);

        $this->expectException(RuntimeException::class);
        (new Script())
            ->head('/head', null, true)
            ->generate();

    }

    /** @test */
    public function testHeadCommandWithCat(): void
    {

        $script = (new Script())
            ->cat('/etc/config')
            ->pipe()
            ->head()
            ->generate();

        $this->assertEquals('cat /etc/config | head', $script);

        $command = (new Script())
            ->command('cat', ['/etc/config', '|', 'head'])
            ->generate();

        $this->assertEquals($command, $script);

        $script = (new Script())
            ->cat('/etc/config')
            ->pipe()
            ->head('-')
            ->generate();

        $this->assertEquals('cat /etc/config | head', $script);

        $command = (new Script())
            ->command('cat', ['/etc/config', '|', 'head'])
            ->generate();

        $this->assertEquals($command, $script);

    }

    /** @test */
    public function testCommand(): void
    {

        $script = (new Script())
            ->command('echo', ['test'], true)
            ->generate();

        $this->assertEquals('echo \'test\'', $script);

        $script = (new Script())
            ->command('echo', ['test'])
            ->generate();

        $this->assertEquals('echo test', $script);

        $script = (new Script())
            ->command('', ['echo', 'test', '|', 'test'])
            ->generate();

        $this->assertEquals('echo test | test', $script);

    }

    /** @test */
    public function testCommandWithEnv(): void
    {

        $env = (new Script())->set('CONFIG', 'test');

        $script = (new Script())
            ->commandWithEnv($env, 'echo', ['test'], true)
            ->generate();

        $this->assertEquals('CONFIG="test" echo \'test\'', $script);

        $script = (new Script())
            ->commandWithEnv($env, 'echo', ['test'])
            ->generate();

        $this->assertEquals('CONFIG="test" echo test', $script);

        $script = (new Script())
            ->commandWithEnv($env, '', ['echo', 'test', '|', 'test'])
            ->generate();

        $this->assertEquals('CONFIG="test" echo test | test', $script);

    }

    /** @test */
    public function testEnv(): void
    {

        $script = (new Script())
            ->set('CONFIG', 'test')
            ->generate();

        $this->assertEquals('CONFIG="test"', $script);

        $script = (new Script())
            ->set('CONFIG', 'test', true)
            ->generate();

        $this->assertEquals('export CONFIG="test"', $script);

        $script = (new Script())
            ->set('CONFIG', (new Script())->command('echo', ['test']))
            ->generate();

        $this->assertEquals('CONFIG=`echo test`', $script);

        $script = (new Script())
            ->set('CONFIG', (new Script())->echo('test')->pipe()->command('grep', ['-v', 'test']))
            ->generate();

        $this->assertEquals('CONFIG=`echo -n test | grep -v test`', $script);

    }

}
