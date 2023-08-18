<?php
declare(strict_types=1);

namespace Noir\PhpSh\Tests;

use Noir\PhpSh\Script;
use PHPUnit\Framework\TestCase;

class TrapTest extends TestCase {

    public function testTrap(): void
    {

        $script = new Script();
        $script->trap('echo \'Hello World\'', [9]);

        $this->assertEquals(
            "trap \"echo 'Hello World'\" 9",
            $script->generate()
        );

    }

}
