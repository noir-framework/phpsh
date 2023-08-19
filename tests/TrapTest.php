<?php
declare(strict_types=1);

namespace Noir\PhpSh\Tests;

use Noir\PhpSh\Enum\Signal;
use Noir\PhpSh\Script;
use PHPUnit\Framework\TestCase;
use ValueError;

class TrapTest extends TestCase {

    public function testTrapSingle(): void
    {

        $script = new Script();
        $script->trap('echo \'Hello World\'', [9]);

        $this->assertEquals(
            "trap \"echo 'Hello World'\" 9",
            $script->generate()
        );

    }

    public function testTrapMultiple(): void
    {

        $script = new Script();
        $script->trap('echo \'Hello World\'', [1, 9, 15]);

        $this->assertEquals(
            "trap \"echo 'Hello World'\" 1 9 15",
            $script->generate()
        );

    }


    public function testTrapWithEnum(): void
    {

        $script = new Script();
        $script->trap('echo \'Hello World\'', Signal::SIGTERM);

        $this->assertEquals(
            "trap \"echo 'Hello World'\" 15",
            $script->generate()
        );

    }

    public function testTrapWithEnums(): void
    {

        $script = new Script();
        $script->trap('echo \'Hello World\'', [Signal::SIGTERM, Signal::SIGKILL]);

        $this->assertEquals(
            "trap \"echo 'Hello World'\" 15 9",
            $script->generate()
        );

    }

    public function testTrapWithInvalidSignal(): void
    {

        /** @noinspection PhpMultipleClassDeclarationsInspection */
        $this->expectException(ValueError::class);

        $script = new Script();
        $script->trap('echo \'Hello World\'', [Signal::SIGTERM, 999]);

    }

}
