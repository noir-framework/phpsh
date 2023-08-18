<?php
declare(strict_types=1);

namespace Noir\PhpSh;

class Date {

    private static array $translations = [
        'Y' => '%Y',
        'y' => '%y',
        'm' => '%m',
        'd' => '%d',
        'H' => '%H',
        'i' => '%M',
        's' => '%S',
        'w' => '%w',
        'W' => '%W',
        'z' => '%j',
        'a' => '%p',
        'A' => '%P',
        'g' => '%l',
        'G' => '%k',
        'h' => '%I',
        'I' => '%l',
        'L' => '%L',
        'u' => '%s',
        'v' => '%3N',
        'e' => '%Z',
        'O' => '%z',
        'P' => '%:z',
        'T' => '%Z',
        'c' => '%Y-%m-%dT%H:%M:%S%:z',
        'r' => '%a, %d %b %Y %T %z',
        'U' => '%s',
    ];

    /**
     * @param string $format
     * @param bool $shell_date_format
     * @return string
     */
    public static function get(string $format, bool $shell_date_format = true): string
    {

        if($shell_date_format) {
            return (new Script())
                ->command('date', ['+"'. $format .'"'])
                ->generate();
        }

        return (new Script())
            ->command('date', ['+"'. self::translate($format) .'"'])
            ->generate();

    }

    /**
     * @param string $format
     * @return string
     */
    private static function translate(string $format): string {

        $res = '';

        foreach(str_split($format) as $char) {
            $res .= self::$translations[$char] ?? $char;
        }

        return $res;

    }


}
