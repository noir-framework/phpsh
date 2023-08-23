<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace Noir\PhpSh;

use Noir\PhpSh\Enum\Signal;
use RuntimeException;

class Script
{
    /**
     * @var string[]
     */
    protected array $fragments = [];
    protected int $nested = 0;
    protected bool $newline = true;

    /**
     * @param string $path
     * @param string|array $arguments
     * @return self
     */
    public function shebang(string $path = '/bin/sh', string|array $arguments = '') : self
    {
        if(! empty($this->fragments)) {
            throw new RuntimeException('Shebang must be called before everything else');
        }

        if(is_array($arguments)) {
            $arguments = implode(' ', $arguments);
        }

        return $this->line('#!' . $path . (empty($arguments) ? '' : ' ' . $arguments));

    }

    /**
     * Set the value of a variable
     * @param string $variable
     * @param Script|string|int $expression
     * @param bool $with_export
     * @return self
     */
    public function let(string $variable, Script|string|int $expression, bool $with_export = false) : self
    {
        if($expression instanceof self) {
            $expression = static::backtick($expression->generate());
        } elseif(! is_int($expression) && ! is_numeric($expression) && ! str_starts_with($expression, '`')) {
            $expression = static::doubleQuote($expression);
        }

        $export = $with_export ? 'export ' : '';

        return $this->line(sprintf(
            '%s%s=%s',
            $export,
            $variable,
            $expression
        ));
    }

    /**
     * @return self
     */
    public function pipe(): self
    {
        return $this->put('|');
    }

    /**
     * @param int $fd
     * @param string $op
     * @param string $dst
     * @return self
     */
    public function redirect(int $fd, string $op, string $dst) : self
    {
        $allowed = ['>', '>>', '<', '<<', '>&', '<&', '>&-', '<&-'];
        if(! in_array($op, $allowed)) {
            throw new RuntimeException(sprintf('Invalid redirection operator: %s', $op));
        }

        return $this->put(sprintf('%s%s %s', $fd, $op, $dst));
    }

    /**
     * Construct an if condition
     * @param string|Condition $condition
     * @param callable $callable A callable that receives a new Script instance as argument
     * @param bool $double
     * @param string $tag
     * @return self
     */
    public function if(Condition|string $condition, callable $callable, bool $double = false, string $tag = 'if') : self
    {
        $script = (string)$this->newNestedScript($callable);

        return $this
            ->line(implode(' ', [
                $tag,
                $double ? '[[ ' : '[ ',
                $condition,
                $double ? ' ]]' : ' ]',
                '; then',
            ]))
            ->line($script);
    }

    /**
     * @param callable $callable
     * @return self
     */
    public function else(callable $callable) : self
    {
        $script = (string)$this->newNestedScript($callable);

        return $this
            ->line('else')
            ->line($script)
            ->fi();
    }

    /**
     * @param string|Condition $condition
     * @param callable $callable
     * @param bool $double
     * @return self
     */
    public function elseif(Condition|string $condition, callable $callable, bool $double = false) : self
    {
        return $this->if($condition, $callable, $double, 'elif');
    }

    /**
     * @see Script::fi
     */
    public function endif() : self
    {
        return $this->fi();
    }

    /**
     * Finish up an if block
     * @return self
     */
    public function fi() : self
    {
        return $this->line('fi');
    }

    /**
     * @param string $variable
     * @param callable $callable
     * @return self
     */
    public function switch(string $variable, callable $callable) : self
    {
        return $this
            ->line(sprintf('case $%s in', $variable))
            ->line((string)$this->newNestedScript($callable))
            ->line('esac');
    }

    /**
     * @param string $pattern
     * @param callable $callable
     * @return self
     */
    public function case(string $pattern, callable $callable) : self
    {
        return $this
            ->line("$pattern)")
            ->line((string)$this->newNestedScript($callable))
            ->line(';;');
    }

    /**
     * @param string|Condition $condition
     * @param callable $callable
     * @return self
     */
    public function while(Condition|string $condition, callable $callable) : self
    {
        $condition = (string) $condition;

        return $this
            ->line(sprintf('while [ %s ]; do', $condition))
            ->line((string)$this->newNestedScript($callable))
            ->line('done');
    }

    /**
     * @param string $expression
     * @return self
     */
    public function break(string  $expression = '') : self
    {
        return $this->line('break '. $expression);
    }

    /**
     * @param string $expression
     * @return self
     */
    public function continue(string $expression = '') : self
    {
        return $this->line('continue '. $expression);
    }

    /**
     * @param string $variable
     * @param int $count
     * @return self
     */
    public function increment(string $variable, int $count = 1) : self
    {
        return $this->line(sprintf('%s=$((%s+%d))', $variable, $variable, $count));
    }

    /**
     * @param string $variable
     * @param int $count
     * @return self
     */
    public function decrement(string $variable, int $count = 1) : self
    {
        return $this->line(sprintf('%s=$((%s-%d))', $variable, $variable, $count));
    }

    /**
     * @param string $expression
     * @param bool $newline
     * @return self
     */
    public function echo(string $expression, bool $newline = false) : self
    {
        return $this->line(sprintf(
            'echo %s %s',
            $newline ? '' : '-n',
            $expression
        ));
    }

    /**
     * @param string $expression
     * @param array $arguments
     * @return self
     */
    public function printf(string $expression, array $arguments = []) : self
    {
        return $this->line(implode(' ', [
            'printf',
            static::doubleQuote($expression),
            ! empty($arguments) ? static::doubleQuote(implode('" "', $arguments)) : '',
        ]));
    }

    /**
     * @param string|Script $env
     * @param string $command
     * @param array $arguments
     * @param bool $needs_escape
     * @return self
     */
    public function commandWithEnv(string|Script $env, string $command, array $arguments = [], bool $needs_escape = false) : self
    {
        $env = (string) $env;
        $this->line($env);
        $this->newline = false;

        return $this->command($command, $arguments, $needs_escape);
    }

    /**
     * @param string $command
     * @param array $arguments
     * @param bool $needs_escape
     * @return self
     */
    public function command(string $command, array $arguments = [], bool $needs_escape = false) : self
    {
        if($needs_escape && ! empty($arguments)) {
            $arguments = array_map('escapeshellarg', $arguments);
        }

        if(empty($command)) {
            return $this->line(implode(' ', $arguments));
        }

        if($needs_escape) {
            $command = escapeshellcmd($command);
        }

        return $this->line(trim(implode(' ', [
            $command,
            implode(' ', $arguments),
        ])));
    }

    /**
     * Surround an expression with double quotes
     * @param string $expression
     * @return string
     */
    public static function doubleQuote(string $expression) : string
    {
        return sprintf('"%s"', $expression);
    }

    /**
     * @param Script|string $expression
     * @return string
     */
    public static function backtick(Script|string $expression) : string
    {
        $expression = (string) $expression;

        return sprintf('`%s`', $expression);
    }

    /**
     * @param int|string $seconds
     * @return self
     */
    public function sleep(int|string $seconds) : self
    {
        if(! is_numeric($seconds)) {
            throw new RuntimeException('Seconds must be an integer');
        }

        return $this->line(sprintf('sleep %d', $seconds));
    }

    /**
     * @param int|string $mode
     * @param string|array $file
     * @param bool $recursive
     * @return self
     */
    public function chmod(int|string $mode, string|array $file, bool $recursive = false) : self
    {

        if(empty($mode)) {
            throw new RuntimeException('Mode cannot be empty');
        }

        if(empty($file)) {
            throw new RuntimeException('File cannot be empty');
        }

        if(is_array($file)) {
            $file = implode(' ', $file);
        }

        if($recursive) {
            $mode = sprintf('-R %s', $mode);
        }

        return $this->line(sprintf('chmod %s %s', $mode, $file));
    }

    /**
     * @param string $ownership
     * @param string|array $file
     * @param bool $recursive
     * @return self
     */
    public function chown(string $ownership, string|array $file, bool $recursive = false) : self
    {
        if(empty($ownership)) {
            throw new RuntimeException('Ownership cannot be empty');
        }

        if(empty($file)) {
            throw new RuntimeException('File cannot be empty');
        }

        if(is_array($file)) {
            $file = implode(' ', $file);
        }

        if($recursive) {
            $ownership = sprintf('-R %s', $ownership);
        }

        return $this->line(sprintf('chown %s %s', $ownership, $file));
    }

    /**
     * @param string $directory
     * @param bool $recursive
     * @return self
     */
    public function mkdir(string $directory, bool $recursive = false) : self
    {
        if(empty($directory)) {
            throw new RuntimeException('Directory cannot be empty');
        }

        if($recursive) {
            $directory = sprintf('-p %s', $directory);
        }

        return $this->line(sprintf('mkdir %s', $directory));
    }

    /**
     * @param string $directory
     * @return self
     */
    public function chdir(string $directory) : self
    {
        if(empty($directory)) {
            throw new RuntimeException('Directory cannot be empty');
        }

        return $this->line(sprintf('cd %s', $directory));
    }

    /**
     * @param string|array $path
     * @param bool $recursive
     * @param bool $force
     * @return self
     */
    public function rm(string|array $path, bool $recursive = false, bool $force = false): self
    {
        if(empty($path)) {
            throw new RuntimeException('Path cannot be empty');
        }

        if(is_array($path)) {
            $path = trim(implode(' ', $path));
        }

        $options = '';
        if($recursive) {
            $options .= '-r ';
        }

        if($force) {
            $options .= '-f ';
        }

        return $this->line(sprintf('rm %s%s', $options, $path));
    }

    /**
     * @param string $dir
     * @return self
     */
    public function dirname(string $dir): self {
        return $this->put("dirname $dir", true);
    }

    /**
     * @return self
     */
    public function and(): self
    {
        return $this->put('&&');
    }

    /**
     * @return self
     */
    public function or(): self
    {
        return $this->put('||');
    }

    /**
     * @param int $code
     * @return self
     */
    public function exit(int $code = 0): self
    {
        return $this->line(sprintf('exit %d', $code));
    }

    /**
     * @param string $file
     * @return self
     */
    public function touch(string $file): self
    {
        return $this->line(sprintf('touch %s', $file));
    }

    /**
     * @return self
     */
    public function semiColon(): self
    {
        return $this->put(';');
    }

    /**
     * @param int|string|array $pid
     * @param int|Signal $signal
     * @return self
     */
    public function kill(int|string|array $pid, int|Signal $signal = Signal::SIGTERM): self
    {
        if(is_array($pid)) {
            $pid = implode(' ', $pid);
        }

        if($signal instanceof Signal) {
            $signal = $signal->value;
        } else {
            $signal = Signal::from($signal)->value;
        }

        return $this->line(sprintf('kill -%s %s', $signal, $pid));
    }

    /**
     * @param string|null $file
     * @return self
     */
    public function cat(?string $file = null): self
    {
        return $this->line($file === null ? 'cat' : sprintf('cat %s', $file));
    }

    /**
     * @param string|null $file
     * @return self
     */
    public function tac(?string $file = null): self
    {
        return $this->line($file === null ? 'tac' : sprintf('tac %s', $file));
    }

    /**
     * @param string|null $file
     * @param int|string|null $amount
     * @param bool $chars_mode
     * @return self
     * @noinspection DuplicatedCode
     */
    public function tail(?string $file = null, int|string|null $amount = null, bool $chars_mode = false): self
    {

        if($chars_mode) {
            if($amount === null) {
                throw new RuntimeException('Chars mode requires amount of chars');
            }
            $op = ' -c';
        } else if(empty($amount)) {
            $op = '';
            $amount = '';
        } else {
            $op = ' -n';
        }

        if(empty($file) || $file === '-') {
            $file = '';
        } else {
            $file = ' ' . $file;
        }

        return $this->put(sprintf('tail%s%s%s', $op, $amount, $file), true);

    }

    /**
     * @param string|null $file
     * @param int|string|null $amount
     * @param bool $chars_mode
     * @return self
     * @noinspection DuplicatedCode
     */
    public function head(?string $file = null, int|string|null $amount = null, bool $chars_mode = false): self
    {

        if($chars_mode) {
            if($amount === null) {
                throw new RuntimeException('Chars mode requires amount of chars');
            }
            $op = ' -c';
        } else if(empty($amount)) {
            $op = '';
            $amount = '';
        } else {
            $op = ' -n';
        }

        if(empty($file) || $file === '-') {
            $file = '';
        } else {
            $file = ' ' . $file;
        }

        return $this->put(sprintf('head%s%s%s', $op, $amount, $file), true);

    }

    /**
     * @param string|Script $script
     * @param int|Signal|array|Signal[] $signals
     * @return self
     */
    public function trap(string|Script $script, int|Signal|array $signals): self
    {
        if($script instanceof self) {
            $script = $script->generate();
        }

        $real = '';

        if($signals instanceof Signal) {
            $real .= $signals->value;
        } elseif(is_int($signals)) {
            $real .= $signals;
        } else {

            foreach($signals as $signal) {
                if($signal instanceof Signal) {
                    $real .= $signal->value . ' ';
                } else {
                    $real .= Signal::from($signal)->value . ' ';
                }
            }

        }

        return $this->line(sprintf('trap "%s" %s', $script, trim($real)));
    }

    public function source(string $filename): self
    {
        return $this->line(sprintf('source %s', $filename));
    }

    /**
     * @param string $option
     * @return self
     */
    public function set(string $option): self
    {
        return $this->line(sprintf('set -%s', $option));
    }

    /**
     * @param string $variable
     * @return self
     */
    public function unset(string $variable): self
    {
        if (str_starts_with($variable, '$')) {
            $variable = substr($variable, 1);
        }

        return $this->line(sprintf('unset %s', $variable));
    }

    /**
     * @param bool $with_tab
     * @return self
     */
    public function nextLine(bool $with_tab = true): self
    {
        //XXX it's important to be handled in this way!
        return $with_tab ? $this->put("\\\\\n\t") : $this->put("\\\\\n");
    }

    /**
     * Generates the resulting shell script
     * @return string
     */
    public function generate(): string
    {
        $result = '';
        $length = count($this->fragments);
        foreach ($this->fragments as $i => $iValue) {
            $result .= str_pad('', $this->nested, "\t");
            $result .= $iValue;
            if ($i < $length - 1) {
                $result .= PHP_EOL;
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->generate();
    }

    /**
     * Add a new shell fragment
     * @param string $line
     * @param bool $newline
     * @param bool $allow_empty_frags
     * @return self
     */
    protected function addFragment(string $line, bool $newline, bool $allow_empty_frags = false) : self
    {
        if($this->newline) {

            $this->fragments[] = $line;

        } else {

            $frag_no = count($this->fragments) - 1;
            if($allow_empty_frags) {

                if(empty($this->fragments)) {
                    $this->fragments[] = '';
                    $frag_no = 0;
                }

            } else if(empty($this->fragments[$frag_no])) {
                throw new RuntimeException('Cannot append fragment to current line, this line is empty');
            }

            if(empty($this->fragments[$frag_no]) || str_starts_with($line, ';') || str_starts_with($this->fragments[$frag_no], '\\') || preg_match('/\s+$/', $this->fragments[$frag_no])) {
                $space = '';
            } else {
                $space = ' ';
            }

            $this->fragments[$frag_no] .= $space . $line;
        }

        $this->newline = $newline;

        return $this;
    }

    /**
     * Create a new nested script fragment
     * @param callable $callable
     * @return self
     */
    protected function newNestedScript(callable $callable) : Script
    {
        $script = new self();
        $script->nested = $this->nested + 1;
        call_user_func_array($callable, [&$script]);

        return $script;
    }

    /**
     * Add a new command line
     * @param string|static $expression
     * @return self
     */
    protected function line(Script|string $expression) : self
    {
        $expression = (string) $expression;

        return $this->addFragment($expression, true);
    }

    public function put(Script|string $expression, bool $allow_empty_frags = false) : self
    {
        $expression = (string) $expression;

        $this->newline = false;

        return $this->addFragment($expression, false, $allow_empty_frags);
    }

}
