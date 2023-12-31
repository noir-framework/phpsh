<?php declare(strict_types=1);

namespace Noir\PhpSh;

class Condition
{

    /** @var string[] */
    protected array $fragments = [];

    /**
     * Initialize a condition
     * @param string $expression
     * @return self
     */
    public static function create(string $expression = '') : self
    {
        $instance = new self();
        if (! empty($expression)) {
            return $instance->addFragment($expression);
        }

        return $instance;
    }

    /**
     * @return self
     */
    public function not() : self
    {
        return $this->addFragment('!');
    }

    /**
     * @param string $variable
     * @return self
     */
    public function is(string $variable) : self
    {
        $variable = $this->safeVariable($variable);

        return $this->addFragment($variable);
    }

    /**
     * @param int|string $expression
     * @return self
     */
    public function equals(int|string $expression) : self
    {
        return $this->addFragment('-eq '. $expression);
    }

    /**
     * @param int|string $expression
     * @return self
     */
    public function notEquals(int|string $expression) : self
    {
        return $this->addFragment('-ne '. $expression);
    }

    /**
     * @param int|string $expression
     * @return self
     */
    public function greaterThan(int|string $expression) : self
    {
        return $this->addFragment('-gt '. $expression);
    }

    /**
     * @param int|string $expression
     * @return self
     */
    public function lessThan(int|string $expression) : self
    {
        return $this->addFragment('-lt '. $expression);
    }

    /**
     * @param int|string $expression
     * @return self
     */
    public function notLessThan(int|string $expression) : self
    {
        return $this->addFragment('-ge '. $expression);
    }

    /**
     * @param int|string $expression
     * @return self
     */
    public function notGreaterThan(int|string $expression) : self
    {
        return $this->addFragment('-le '. $expression);
    }

    /**
     * @return self
     */
    public function and() : self
    {
        return $this->addFragment('-a');
    }

    /**
     * @return self
     */
    public function or() : self
    {
        return $this->addFragment('-o');
    }

    /**
     * @param string $variable
     * @return self
     */
    public function isEmpty(string $variable) : self
    {
        $variable = $this->safeVariable($variable);

        return $this->addFragment('-z '. $variable);
    }

    /**
     * @param string $variable
     * @return self
     */
    public function isset(string $variable) : self
    {
        $variable = $this->removeDollarSign($variable);

        return $this->isEmpty(sprintf('{%s+x}', $variable));
    }

    /**
     * @param string $variable
     * @return self
     */
    public function isNotEmpty(string $variable) : self
    {
        $variable = $this->safeVariable($variable);

        return $this->addFragment('-n '. $variable);
    }

    /**
     * @param string $path
     * @return self
     */
    public function fileExists(string $path) : self
    {
        return $this->checkPath('f', $path);
    }

    /**
     * @param string $path
     * @return self
     * @see Condition::fileExists
     */
    public function isFile(string $path) : self
    {
        return $this->fileExists($path);
    }

    /**
     * @param string $path
     * @return self
     */
    public function readable(string $path) : self
    {
        return $this->checkPath('r', $path);
    }

    /**
     * @param string $path
     * @return self
     */
    public function writable(string $path): self
    {
        return $this->checkPath('w', $path);
    }

    /**
     * @param string $path
     * @return self
     */
    public function executable(string $path) : self
    {
        return $this->checkPath('x', $path);
    }

    /**
     * @param string $path
     * @return self
     */
    public function notEmptyFile(string $path) : self
    {
        return $this->checkPath('s', $path);
    }

    /**
     * @param string $path
     * @return self
     */
    public function pathExists(string $path) : self
    {
        return $this->checkPath('e', $path);
    }

    /**
     * @param string $path
     * @return self
     */
    public function isDir(string $path) : self
    {
        return $this->checkPath('d', $path);
    }

    /**
     * @param string $path
     * @return self
     */
    public function directoryExists(string $path) : self
    {
        return $this->isDir($path);
    }

    /**
     * @param string $operator
     * @param string $path
     * @return self
     */
    public function checkPath(string $operator, string $path) : self
    {
        return $this->addFragment("-$operator $path");
    }

    /**
     * Convert the Condition object to shell condition string
     * @return string
     */
    public function generate(): string
    {
        return implode(' ', $this->fragments);
    }

    public function __toString() : string
    {
        return $this->generate();
    }

    /**
     * @param string $variable
     * @return string
     */
    protected function safeVariable(string $variable) : string
    {
        if (! str_starts_with($variable, '$')) {
            return '$'. $variable;
        }

        return $variable;
    }

    /**
     * Remove $ from a variable
     * @param string $variable
     * @return string
     */
    protected function removeDollarSign(string $variable) : string
    {
        if (str_starts_with($variable, '$')) {
            $variable = substr($variable, 1);
        }

        return $variable;
    }

    /**
     *
     * @param string $part
     * @return self
     */
    protected function addFragment(string $part) : self
    {
        $this->fragments[] = $part;

        return $this;
    }
}
