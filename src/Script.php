<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace PhpSh;

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
     * @return $this
     */
    public function shebang(string $path = '/bin/sh', string|array $arguments = '') : self
    {

        if(!empty($this->fragments)) {
            throw new RuntimeException('Shebang must be called before everything else');
        }

        if(!empty($arguments) && is_array($arguments)) {
            $arguments = implode(' ', $arguments);
        }

        return $this->line(implode(' ', [
            '#!' . $path,
            $arguments
        ]));

    }

    /**
     * Set the value of a variable
     * @param string $variable
     * @param Script|string $expression
     * @param bool $with_export
     * @return self
     */
    public function set(string $variable, Script|string $expression, bool $with_export = false) : self
    {
        if(is_string($expression) && !is_numeric($expression)) {
            $expression = static::doubleQuote($expression);
        } elseif($expression instanceof Script) {
            $expression = static::backtick($expression);
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
     * @return $this
     */
    public function pipe(): self {
        return $this->put('|');
    }

    /**
     * @param int $fd
     * @param string $op
     * @param string $dst
     * @return $this
     */
    public function redirect(int $fd, string $op, string $dst) : self
    {
        $allowed = ['>', '>>', '<', '<<', '>&', '<&', '>&-', '<&-'];
        if(!in_array($op, $allowed)) {
            throw new RuntimeException(sprintf('Invalid redirection operator: %s', $op));
        }
        return $this->put(sprintf('%s%s%s', $fd, $op, $dst));
    }

    public function execute(Script|string $expression) : self
    {
        $expression = (string) $expression;

        return $this->addFragment($expression, true);
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
        $script = $this->newNestedScript($callable);

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
        $script = $this->newNestedScript($callable);

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
            ->line($this->newNestedScript($callable))
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
            ->line($this->newNestedScript($callable))
            ->line(';;');
    }

    /**
     * @param string|Condition $condition
     * @param callable $callable
     * @return self
     */
    public function while(Condition|string $condition, callable $callable) : self
    {
        return $this
            ->line(sprintf('while [ %s ]; do', $condition))
            ->line($this->newNestedScript($callable))
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
     * @param bool|array $arguments
     * @return self
     */
    public function printf(string $expression, bool|array $arguments = false) : self
    {
        return $this->line(implode(' ', [
            'printf',
            static::doubleQuote($expression),
            $arguments ? static::doubleQuote(implode('" "', $arguments)) : '',
        ]));
    }

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
     * @return $this
     */
    public function command(string $command, array $arguments = [], bool $needs_escape = false) : self
    {
        if($needs_escape && !empty($arguments)) {
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
            implode(' ', $arguments)
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
        return sprintf('`%s`', $expression);
    }

    /**
     * @param int $seconds
     * @return $this
     */
    public function sleep(int $seconds) : self
    {
        return $this->line(sprintf('sleep %d', $seconds));
    }

    /**
     * @param string $mode
     * @param string|array $file
     * @param bool $recursive
     * @return $this
     */
    public function chmod(string $mode, string|array $file, bool $recursive = false) : self
    {
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
     * @return $this
     */
    public function chown(string $ownership, string|array $file, bool $recursive = false) : self
    {
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
     * @return $this
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
     * @return $this
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
     * @return $this
     */
    public function rm(string|array $path, bool $recursive = false, bool $force = false): self {
        if(empty($path)) {
            throw new RuntimeException('Path cannot be empty');
        }

        if(is_array($path)) {
            $path = implode(' ', $path);
        }

        $options = [];
        if($recursive) {
            $options[] = '-r';
        }

        if($force) {
            $options[] = '-f';
        }

        return $this->line(sprintf('rm %s %s', implode(' ', $options), $path));
    }

    /**
     * @return $this
     */
    public function and(): self {
        return $this->put('&&');
    }

    /**
     * @return $this
     */
    public function or(): self {
        return $this->put('||');
    }

    /**
     * @param int $code
     * @return $this
     */
    public function exit(int $code = 0): self {
        return $this->line(sprintf('exit %d', $code));
    }

    /**
     * @param string $file
     * @return $this
     */
    public function touch(string $file): self {
        return $this->line(sprintf('touch %s', $file));
    }

    /**
     * @return $this
     */
    public function semiColon(): self {
        return $this->put(';');
    }

    /**
     * @param int|array $pid
     * @param int $signal
     * @return $this
     */
    public function kill(int|array $pid, int $signal = 15): self {
        if(is_array($pid)) {
            $pid = implode(' ', $pid);
        }

        return $this->line(sprintf('kill -%s %s', $signal, $pid));
    }

    /**
     * @param string|null $file
     * @return $this
     */
    public function cat(?string $file = null): self {
        return $this->line($file === null ? 'cat' : sprintf('cat %s', $file));
    }

    /**
     * @param string $file
     * @return $this
     */
    public function tac(string $file): self {
        return $this->line(sprintf('tac %s', $file));
    }

    /**
     * @param string $file
     * @param int $lines
     * @param bool $bytes
     * @return $this
     */
    public function tail(string $file = '-', int $lines = 10, bool $bytes = false): self {
        if($bytes) {
            $op = '-c';
        } else {
            $op = '-n';
        }
        return $this->put(sprintf('tail %d %d %s', $op, $lines, $file));
    }

    /**
     * @param string $file
     * @param int $lines
     * @param bool $bytes
     * @return $this
     */
    public function head(string $file = '-', int $lines = 10, bool $bytes = false): self {
        if($bytes) {
            $op = '-c';
        } else {
            $op = '-n';
        }
        return $this->put(sprintf('head %s %d %s', $op, $lines, $file));
    }

    /**
     * @return $this
     */
    public function nextLine(bool $with_tab = true): self {
        //XXX it's important to be handled in this way!
        return $with_tab ? $this->put( "\\\\\n\t") : $this->line("");
    }

    /**
     * Generates the resulting shell script
     * @return string
     */
    public function generate(): string {
        $result = '';
        $length = count($this->fragments);
        for ($i = 0; $i < $length; $i++) {
            $result .= str_pad('', $this->nested, "\t");
            $result .= $this->fragments[$i];
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
     * @return self
     */
    protected function addFragment(string $line, bool $newline) : self
    {
        if($this->newline) {
            $this->fragments[] = $line;
        } else {
            $frag_no = count($this->fragments) - 1;
            if(str_starts_with(';', $line) || str_starts_with('\\', $this->fragments[$frag_no]) || preg_match('/\s+$/', $this->fragments[$frag_no])) {
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
     * @return $this
     */
    protected function newNestedScript(callable $callable) : Script
    {
        $script = new Script();
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

    public function put(Script|string $expression) : self
    {
        $expression = (string) $expression;

        $this->newline = false;
        return $this->addFragment($expression, false);
    }

}
