<?php

namespace Drupal\Tests\test_support\Traits\Support\Mail;

use PHPUnit\Framework\Assert;

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
        return $this->getValue('to');
    }

    public function assertSentTo(string $to): self
    {
        Assert::assertEquals($to, $this->getTo());

        return $this;
    }

    public function getSubject(): ?string
    {
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

        if ($body === null) {
            return null;
        }

        return preg_replace('/\s+/', ' ', trim($body));
    }

    /** @param  mixed  $body */
    public function assertBody($body): self
    {
        Assert::assertEquals($body, $this->getBody());

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

        Assert::assertEquals($value, $paramValue);

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

    private function getValue(string $keyName): ?string
    {
        $value = $this->values[$keyName];

        if (is_string($value) === false) {
            return null;
        }

        return $value;
    }
}
