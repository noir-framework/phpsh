<?php
declare(strict_types=1);

namespace PhpSh\Tests;

use PhpSh\Script;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
#[CoversNothing]
class SimpleTextProcessing extends TestCase {

    /** @test */
    public function it_createCatCommand(): void {

        $script = (new Script())
            ->echo('test')
            ->pipe()
            ->cat()
            ->generate();

        $this->assertEquals('echo -n test | cat', $script);

        $this->assertEquals('test', shell_exec($script));

    }

    /** @test */
    public function it_createCatCommand2(): void {

        $script = (new Script())
            ->command('echo', ['-n', 'test'])
            ->pipe()
            ->command('cat')
            ->generate();

        $this->assertEquals('echo -n test | cat', $script);

        $this->assertEquals('test', shell_exec($script));

    }

    /** @test */
    public function it_createTacCommand(): void {

        $script = (new Script())
            ->echo('test')
            ->pipe()
            ->tac()
            ->generate();

        $this->assertEquals('echo -n test | tac', $script);

        $this->assertEquals('test', shell_exec($script));

    }

    /** @test */
    public function it_createTacCommand2(): void {

        $script = (new Script())
            ->command('echo', ['-n', 'test'])
            ->pipe()
            ->command('tac')
            ->generate();

        $this->assertEquals('echo -n test | tac', $script);

        $this->assertEquals('test', shell_exec($script));

    }

}
