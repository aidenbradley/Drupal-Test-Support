<?php

namespace Drupal\Tests\test_support\Traits\Support\Mail;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;

class TestMail
{
    /** @var mixed[] */
    protected $values;

    /** @param mixed[] $values */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /** @param mixed[] $values */
    public static function createFromValues(array $values): self
    {
        return new static($values);
    }

    public function getTo(): ?string
    {
        /** @phpstan-ignore-next-line */
        return $this->getValue('to');
    }

    public function assertSentTo(string $to): self
    {
        $this->assertEquals($to, $this->getTo());

        return $this;
    }

    public function getSubject(): ?string
    {
        /** @phpstan-ignore-next-line */
        return $this->getValue('subject');
    }

    public function assertSubject(string $subject): self
    {
        Assert::assertEquals($subject, $this->getSubject());

        return $this;
    }

    public function getBody(): ?string
    {
        $body = $this->getValue('body');

        if (is_string($body) === false) {
            return null;
        }

        return preg_replace('/\s+/', ' ', trim($body));
    }

    /** @param  mixed  $body */
    public function assertBody($body): self
    {
        $this->assertEquals($body, $this->getBody());

        return $this;
    }

    public function getModule(): ?string
    {
        /** @phpstan-ignore-next-line */
        return $this->getValue('module');
    }

    public function assertSentFromModule(string $module): self
    {
        $this->assertEquals($module, $this->getModule());

        return $this;
    }

    /** @return mixed */
    public function getParameter(string $param)
    {
        if (isset($this->values['params']) === false || is_array($this->values['params']) === false) {
            return null;
        }

        if (isset($this->values['params'][$param]) === false) {
            return null;
        }

        return $this->values['params'][$param];
    }

    /**
     * @param mixed $value
     *
     * The closure will pass back the value attributed to the given parameter
     */
    public function assertParameter(string $parameter, $value, ?\Closure $assertionCallback = null): self
    {
        $paramValue = $this->getParameter($parameter);

        $this->assertEquals($value, $paramValue);

        if ($assertionCallback !== null) {
            $assertionCallback($paramValue);
        }

        return $this;
    }

    /** @return mixed[] */
    public function toArray(): array
    {
        return $this->values;
    }

    /** @return mixed|null */
    private function getValue(string $keyName)
    {
        if (isset($this->values[$keyName]) === false) {
            return null;
        }

        return $this->values[$keyName];
    }

    /**
     * @param mixed $expected
     * @param mixed $actual
     */
    private function assertEquals($expected, $actual): void
    {
        try {
            Assert::assertEquals($expected, $actual);
        } catch (ExpectationFailedException $exception) {
            Assert::fail('Failed asserting that `' . $expected . '` equals `' . $actual . '`');
        }
    }
}
