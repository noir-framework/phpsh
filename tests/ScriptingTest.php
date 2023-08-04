<?php
declare(strict_types=1);

namespace PhpSh\Tests;

use PhpSh\Script;
use PHPUnit\Framework\TestCase;

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

}
