<?php
declare(strict_types=1);

namespace PhpSh\Tests;

use PhpSh\Script;
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
    public function createKillCommand()
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

    }

    /** @test */
    public function createSemiColon()
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
    public function createExitCommand()
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
    public function createAnd()
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
    public function createOr()
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
    public function createRmCommand(): void
    {

        $script = (new Script())
            ->rm('test.txt')
            ->generate();

        $this->assertEquals('rm test.txt', $script);

        $command = (new Script())
            ->command('rm', ['test.txt'])
            ->generate();

        $this->assertEquals($command, $script);

        $script = (new Script())
            ->rm(['test.txt', 'test2.txt'])
            ->generate();

        $this->assertEquals('rm test.txt test2.txt', $script);

        $command = (new Script())
            ->command('rm', ['test.txt', 'test2.txt'])
            ->generate();

        $this->assertEquals($command, $script);

        $script = (new Script())
            ->rm('text.txt' ,true)
            ->generate();

        $this->assertEquals('rm -r text.txt', $script);

        $command = (new Script())
            ->command('rm', ['-r', 'text.txt'])
            ->generate();

        $this->assertEquals($command, $script);

        $script = (new Script())
            ->rm(['text.txt', 'text2.txt'], true)
            ->generate();

        $this->assertEquals('rm -r text.txt text2.txt', $script);

        $command = (new Script())
            ->command('rm', ['-r', 'text.txt', 'text2.txt'])
            ->generate();

        $this->assertEquals($command, $script);

        //
        $script = (new Script())
            ->rm('text.txt' ,true, true)
            ->generate();

        $this->assertEquals('rm -r -f text.txt', $script);

        $command = (new Script())
            ->command('rm', ['-r -f', 'text.txt'])
            ->generate();

        $this->assertEquals($command, $script);

        $script = (new Script())
            ->rm(['text.txt', 'text2.txt'], true, true)
            ->generate();

        $this->assertEquals('rm -r -f text.txt text2.txt', $script);

        $command = (new Script())
            ->command('rm', ['-r', '-f', 'text.txt', 'text2.txt'])
            ->generate();

        $this->assertEquals($command, $script);

    }

    /** @test  */
    public function chdirCommand()
    {

        $script = (new Script())
            ->chdir('/home')
            ->generate();

        $this->assertEquals('cd /home', $script);

        $command = (new Script())
            ->command('cd', ['/home'])
            ->generate();

        $this->assertEquals($command, $script);

    }

    /** @test  */
    public function mkdirCommand(): void
    {

            $script = (new Script())
                ->mkdir('/home/home')
                ->generate();

            $this->assertEquals('mkdir /home/home', $script);

            $command = (new Script())
                ->command('mkdir', ['/home/home'])
                ->generate();

            $this->assertEquals($command, $script);

            $script = (new Script())
                ->mkdir('/home/home', true)
                ->generate();

            $this->assertEquals('mkdir -p /home/home', $script);

            $command = (new Script())
                ->command('mkdir', ['-p', '/home/home'])
                ->generate();

            $this->assertEquals($command, $script);

    }

    /** @test */
    public function testSleepCommand()
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
    public function testSetAndBackTick()
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
    public function testShebang()
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
    public function testPipe()
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

    public function testRedirect() {

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

}
