<?php
declare(strict_types=1);

namespace Noir\PhpSh\Tests;

use Noir\PhpSh\Condition;
use Noir\PhpSh\Script;
use PHPUnit\Framework\TestCase;

class ScriptingConditionsTest extends TestCase
{
    /** @test */
    public function it_can_build_an_if_statement(): void
    {
        $condition = Condition::create();
        $sh = (new Script())
            ->let('i', 10)
            ->if($condition->is('i')->equals(10), function (Script $script) {
                $script->printf('OK');
            })
            ->endif()
            ->generate();
        $this->assertEquals('OK', shell_exec($sh));
    }

    /** @test */
    public function it_can_build_an_if_statement_with_multiple_conditions(): void
    {
        $sh = (new Script())
            ->let('i', 5)
            ->if(Condition::create('$i')->lessThan(10)->and()->is('i')->greaterThan('1'), function (Script $script) {
                $script->printf("OK");
            })
            ->endif()
            ->generate();
        $this->assertEquals('OK', shell_exec($sh));
    }

    /** @test */
    public function it_can_build_an_if_else_statement(): void
    {
        $sh = (new Script())
            ->let('i', 15)
            ->if(Condition::create('$i')->lessThan(10), function (Script $script) {
                $script->printf("NOT_OK");
            })->else(function (Script $script) {
                $script->printf("OK");
            })->fi()
            ->generate();

        $this->assertEquals('OK', shell_exec($sh));
    }

    /** @test */
    public function it_can_build_an_if_elseif_else_statement(): void
    {
        $sh = (new Script())
            ->let('i', 25)
            ->if(Condition::create('$i')->lessThan(10), function (Script $script) {
                $script->printf('NOT_OK');
            })->elseif(Condition::create('$i')->greaterThan(20), function (Script $script) {
                $script->printf('OK');
            })->else(function (Script $script) {
                $script->printf('NOT_OK');
            })->fi()
            ->generate();
        $this->assertEquals('OK', shell_exec($sh));
    }

    /** @test */
    public function it_can_build_switch_statement(): void
    {
        $sh = (new Script())
            ->let('i', 1)
            ->decrement('i')
            ->switch('i', function (Script $script) {
                $script
                    ->case('1', function (Script $script) {
                        $script->echo('NOT_OK');
                    })
                    ->case('*', function (Script $script) {
                        $script->echo('OK');
                    });
            });

        $this->assertEquals('OK', shell_exec((string)$sh));
    }


    public function test_not_condition(): void
    {

        $sh = (new Script())
            ->if(Condition::create()->not()->fileExists("/tmp/test"), function (Script $script) {
                $script->echo('OK');
            })->fi()
            ->generate();

        $this->assertEquals("if [  ! -f /tmp/test  ] ; then
	echo -n OK
fi", $sh);


    }

}
