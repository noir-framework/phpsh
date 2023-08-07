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

    /** @test */
    public function testChownCommandNoOwner(): void
    {

        $this->expectException(RuntimeException::class);
        (new Script())
            ->chown('', '/tmp/test')
            ->generate();

    }

    /** @test */
    public function testChownCommandNoFile(): void
    {

        $this->expectException(RuntimeException::class);
        (new Script())
            ->chown('root', '')
            ->generate();

    }

    public function testChmodCommand(): void
    {

        $script = (new Script())
            ->chmod(777, '/home/home')
            ->generate();

        $this->assertEquals('chmod 777 /home/home', $script);

        $command = (new Script())
            ->command('chmod', ['777', '/home/home'])
            ->generate();

        $this->assertEquals($command, $script);

        $script = (new Script())
            ->chmod("777", ['/home/home', '/home/home2'])
            ->generate();

        $this->assertEquals('chmod 777 /home/home /home/home2', $script);

        $command = (new Script())
            ->command('chmod', ['777', '/home/home', '/home/home2'])
            ->generate();

        $this->assertEquals($command, $script);

        // recursive directories
        $script = (new Script())
            ->chmod(777, '/home/home', true)
            ->generate();

        $this->assertEquals('chmod -R 777 /home/home', $script);

        $command = (new Script())
            ->command('chmod', ['-R','777', '/home/home'])
            ->generate();

        $this->assertEquals($command, $script);

        $script = (new Script())
            ->chmod("777", ['/home/home', '/home/home2'], true)
            ->generate();

        $this->assertEquals('chmod -R 777 /home/home /home/home2', $script);

        $command = (new Script())
            ->command('chmod', ['-R', '777', '/home/home', '/home/home2'])
            ->generate();

        $this->assertEquals($command, $script);

    }

    /** @test */
    public function testChmodCommandNoMode(): void
    {

        $this->expectException(RuntimeException::class);
        (new Script())
            ->chmod('', '/tmp/test')
            ->generate();

    }

    /** @test */
    public function testChmodCommandNoFile(): void
    {

        $this->expectException(RuntimeException::class);
        (new Script())
            ->chmod(777, '')
            ->generate();

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
    public function testMkdirCommandNoDir(): void
    {

        $this->expectException(RuntimeException::class);
        (new Script())
            ->mkdir('')
            ->generate();

    }

    /** @test  */
    public function chdirCommand() : void
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

    /** @test */
    public function chdirCommandNoDir(): void
    {

        $this->expectException(RuntimeException::class);
        (new Script())
            ->chdir('')
            ->generate();

    }

}
