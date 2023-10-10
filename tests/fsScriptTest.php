<?php
declare(strict_types=1);

namespace Noir\PhpSh\Tests;

use Noir\PhpSh\Script;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class fsScriptTest extends TestCase
{

    /** @test  */
    public function chdirTest(): void
    {

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
            ->rm('text.txt', true)
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
            ->rm('text.txt', true, true)
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

    /** @test */
    public function testRmCommandNoFile(): void
    {

        $this->expectException(RuntimeException::class);
        (new Script())
            ->rm('')
            ->generate();

    }

    /**
     * @return void
     */
    public function testDirname(): void
    {

        $script = new Script();
        $script->let('dirname', (new Script())->dirname("/tmp"));

        $this->assertEquals(
            'dirname=`dirname /tmp`',
            $script->generate()
        );

    }

    public function testSource(): void
    {

        $script = new Script();
        $script->source('/tmp/test.sh');

        $this->assertEquals(
            'source /tmp/test.sh',
            $script->generate()
        );

    }

    public function testMv(): void
    {

        $script = new Script();
        $script->mv('/tmp/test', '/tmp/test2');

        $this->assertEquals(
            'mv /tmp/test /tmp/test2',
            $script->generate()
        );

        $script = new Script();
        $script->mv('/tmp/test', '/tmp/test2', '-f');

        $this->assertEquals(
            'mv -f /tmp/test /tmp/test2',
            $script->generate()
        );

        $script = new Script();
        $script->mv(['/tmp/test', '/tmp/test1'], '/tmp/test2', ['-f']);

        $this->assertEquals(
            'mv -f /tmp/test /tmp/test1 /tmp/test2',
            $script->generate()
        );

        $script = new Script();
        $script->mv(['/tmp/test', '/tmp/test1'], '/tmp/test2');

        $this->assertEquals(
            'mv /tmp/test /tmp/test1 /tmp/test2',
            $script->generate()
        );

        $script = new Script();
        $script->mv(['/tmp/test', '/tmp/test1'], '/tmp/test2', '-f');

        $this->assertEquals(
            'mv -f /tmp/test /tmp/test1 /tmp/test2',
            $script->generate()
        );

        $script = new Script();
        $script->mv(['/tmp/test', '/tmp/test1'], '/tmp/test2', ['-f']);

        $this->assertEquals(
            'mv -f /tmp/test /tmp/test1 /tmp/test2',
            $script->generate()
        );

        $script = new Script();
        $script->mv('/tmp/test', '/tmp/test2', ['-f', '-t /tmp/1']);

        $this->assertEquals(
            'mv -f -t /tmp/1 /tmp/test /tmp/test2',
            $script->generate()
        );

        $this->expectException(RuntimeException::class);
        $script = new Script();
        $script->mv('/tmp/test', '/tmp/test2', ['-i']);

        $this->assertEquals(
            'mv -i /tmp/1 /tmp/test /tmp/test2',
            $script->generate()
        );

    }

    public function testCp(): void
    {

        $script = new Script();
        $script->cp('/tmp/test', '/tmp/test2');

        $this->assertEquals(
            'cp /tmp/test /tmp/test2',
            $script->generate()
        );

        $script = new Script();
        $script->cp('/tmp/test', '/tmp/test2', '-a');

        $this->assertEquals(
            'cp -a /tmp/test /tmp/test2',
            $script->generate()
        );

        $script = new Script();
        $script->cp('/tmp/test', '/tmp/test2', ['-a']);

        $this->assertEquals(
            'cp -a /tmp/test /tmp/test2',
            $script->generate()
        );

        $script = new Script();
        $script->cp(['/tmp/test', '/tmp/test1'], '/tmp/test2');

        $this->assertEquals(
            'cp /tmp/test /tmp/test1 /tmp/test2',
            $script->generate()
        );

        $script = new Script();
        $script->cp(['/tmp/test', '/tmp/test1'], '/tmp/test2', '-a');

        $this->assertEquals(
            'cp -a /tmp/test /tmp/test1 /tmp/test2',
            $script->generate()
        );

        $script = new Script();
        $script->cp(['/tmp/test', '/tmp/test1'], '/tmp/test2', ['-a']);

        $this->assertEquals(
            'cp -a /tmp/test /tmp/test1 /tmp/test2',
            $script->generate()
        );

        $this->expectException(RuntimeException::class);
        $script = new Script();
        $script->cp(['/tmp/test', '/tmp/test1'], '/tmp/test2', ['--interactive']);

        $this->assertEquals(
            'cp -a /tmp/test /tmp/test1 /tmp/test2',
            $script->generate()
        );

    }

}
