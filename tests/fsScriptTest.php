<?php
declare(strict_types=1);

namespace PhpSh\Tests;

use PhpSh\Script;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class fsScriptTest extends TestCase{

    /** @test  */
    public function chdirTest(): void {

        $script = (new Script())
            ->chdir('/tmp')
            ->generate();

        $this->assertEquals('cd /tmp', $script);

        $command = (new Script())
            ->command('cd', ['/tmp'])
            ->generate();

        $this->assertEquals($command, $script);

        $this->expectException(RuntimeException::class);

        (new Script())
            ->chdir('')
            ->generate();


    }

    /** @test */
    public function testChownCommand(): void
    {

        $script = (new Script())
            ->chown('root.root', '/home/home')
            ->generate();

        $this->assertEquals('chown root.root /home/home', $script);

        $command = (new Script())
            ->command('chown', ['root.root', '/home/home'])
            ->generate();

        $this->assertEquals($command, $script);

        $script = (new Script())
            ->chown("root.root", ['/home/home', '/home/home2'])
            ->generate();

        $this->assertEquals('chown root.root /home/home /home/home2', $script);

        $command = (new Script())
            ->command('chown', ['root.root', '/home/home', '/home/home2'])
            ->generate();

        $this->assertEquals($command, $script);

        // recursive directories
        $script = (new Script())
            ->chown('root.root', '/home/home', true)
            ->generate();

        $this->assertEquals('chown -R root.root /home/home', $script);

        $command = (new Script())
            ->command('chown', ['-R', 'root.root', '/home/home'])
            ->generate();

        $this->assertEquals($command, $script);

        $script = (new Script())
            ->chown("root.root", ['/home/home', '/home/home2'], true)
            ->generate();

        $this->assertEquals('chown -R root.root /home/home /home/home2', $script);

        $command = (new Script())
            ->command('chown', ['-R', 'root.root', '/home/home', '/home/home2'])
            ->generate();

        $this->assertEquals($command, $script);

    }

    public function testChownCommandNoOwner()
    {

        $this->expectException(RuntimeException::class);
        (new Script())
            ->chown('', '/tmp/test')
            ->generate();

    }

    public function testChownCommandNoFile()
    {

        $this->expectException(RuntimeException::class);
        (new Script())
            ->chown('root', '')
            ->generate();

    }

}
