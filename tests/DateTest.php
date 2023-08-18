<?php
declare(strict_types=1);

namespace Noir\PhpSh\Tests;

use Noir\PhpSh\Date;
use PHPUnit\Framework\TestCase;

class DateTest extends TestCase {

    public function testGetPhpDate(): void
    {

        $date = Date::get('Y-m-d H:i:s', false);

        $this->assertEquals(
            'date +"%Y-%m-%d %H:%M:%S"',
            $date
        );

    }

    public function testGetShellDate(): void
    {

        $date = Date::get('%Y-%m-%d %H:%M:%S');

        $this->assertEquals(
            'date +"%Y-%m-%d %H:%M:%S"',
            $date
        );

    }

}
