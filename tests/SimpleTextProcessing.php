<?php
declare(strict_types=1);

namespace PhpSh\Tests;

use PhpSh\Script;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
#[CoversNothing]
class SimpleTextProcessing extends TestCase {

    /** @test */
    public function createCatCommand(): void
    {

        $script = (new Script())
            ->echo('test')
            ->pipe()
            ->cat()
            ->generate();

        $this->assertEquals('echo -n test | cat', $script);

        $command = (new Script())
            ->command('echo', ['-n', 'test'])
            ->pipe()
            ->command('cat')
            ->generate();

        $this->assertEquals($command, $script);

        // Execute it!
        $this->assertEquals('test', shell_exec($script));

    }

    /** @test */
    public function createTacCommand(): void
    {

        $script = (new Script())
            ->echo('test')
            ->pipe()
            ->tac()
            ->generate();

        $this->assertEquals('echo -n test | tac', $script);

        $command = (new Script())
            ->command('echo', ['-n', 'test'])
            ->pipe()
            ->command('tac')
            ->generate();

        $this->assertEquals($command, $script);

        $this->assertEquals('test', shell_exec($script));

    }

    /** @test */
    public function createTailCommandLines(): void
    {

        $script = (new Script())
            ->echo('test')
            ->pipe()
            ->tail(null, 1)
            ->generate();

        $this->assertEquals('echo -n test | tail -n1', $script);


        $command = (new Script())
            ->command('echo', ['-n', 'test'])
            ->pipe()
            ->command('tail', ['-n1'])
            ->generate();

        $this->assertEquals($command, $script);

        // Execute it!
        $this->assertEquals('test', shell_exec($script));

    }

    /** @test */
    public function createTailCommandChars(): void
    {

        $script = (new Script())
            ->echo('test')
            ->pipe()
            ->tail(null, 4, true)
            ->generate();

        $this->assertEquals('echo -n test | tail -c4', $script);

        $command = (new Script())
            ->command('echo', ['-n', 'test'])
            ->pipe()
            ->command('tail', ['-c4'])
            ->generate();

        $this->assertEquals($command, $script);

        // Execute it!
        $this->assertEquals('test', shell_exec($script));

    }

    /** @test */
    public function createHeadCommandLines(): void
    {

        $script = (new Script())
            ->echo('test')
            ->pipe()
            ->head(null, 1)
            ->generate();

        $this->assertEquals('echo -n test | head -n1', $script);

        $command = (new Script())
            ->command('echo', ['-n', 'test'])
            ->pipe()
            ->command('head', ['-n1'])
            ->generate();

        $this->assertEquals($command, $script);

        // Execute it!
        $this->assertEquals('test', shell_exec($script));

    }

    /** @test */
    public function createHeadCommandChars(): void
    {

        $script = (new Script())
            ->echo('test')
            ->pipe()
            ->head(null, 4, true)
            ->generate();

        $this->assertEquals('echo -n test | head -c4', $script);

        $command = (new Script())
            ->command('echo', ['-n', 'test'])
            ->pipe()
            ->command('head', ['-c4'])
            ->generate();

        $this->assertEquals($command, $script);

        // Execute it!
        $this->assertEquals('test', shell_exec($script));

    }

}
