<?php

namespace Drupal\Tests\test_support\Traits\Support;

use Drupal\Tests\test_support\Traits\Support\Mail\TestMail;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert;

trait InteractsWithMail
{
    /** @return TestMail[] */
    public function getSentMail(callable $filter = null): array
    {
        $mail = $this->container->get('state')->get('system.test_mail_collector');

        if ($mail === null) {
            return [];
        }

        /** @phpstan-ignore-next-line */
        return collect($mail)->mapInto(TestMail::class)->when($filter, function (Collection $mail, callable $filter) {
            return $mail->filter($filter);
        })->toArray();
    }

    /**
     * If multiple mails are found, then an array is returned.
     * If a single mail is found, then a TestMail instance is returned.
     *
     * @return TestMail[]|TestMail
     */
    public function getMailSentTo(string $mailTo)
    {
        $mail = $this->getSentMail(function (TestMail $mail) use ($mailTo): bool {
            return $mail->getTo() === $mailTo;
        });

        if (count($mail) === 1 && isset($mail[0])) {
            return $mail[0];
        }

        return $mail;
    }

    /**
     * If multiple mails are found, then an array is returned.
     * If a single mail is found, then a TestMail instance is returned.
     *
     * @return TestMail[]|TestMail
     */
    public function getSentMailWithSubject(string $subject)
    {
        $mail = $this->getSentMail(function (TestMail $mail) use ($subject): bool {
            return $mail->getSubject() === $subject;
        });

        if (count($mail) === 1 && isset($mail[0])) {
            return $mail[0];
        }

        return $mail;
    }

    public function clearMail(): self
    {
        $this->container->get('state')->set('system.test_mail_collector', []);

        return $this;
    }

    public function assertNoMailSent(): self
    {
        Assert::assertEmpty($this->getSentMail());

        return $this;
    }

    public function assertMailSent(?\Closure $callback = null): self
    {
        $mail = $this->getSentMail();

        Assert::assertNotEmpty($mail);

        if ($callback) {
            foreach ($mail as $testMail) {
                $callback($testMail);
            }
        }

        return $this;
    }

    public function assertMailSentCount(int $numberOfMailSent, ?\Closure $callback = null): self
    {
        $mail = $this->getSentMail();

        Assert::assertCount($numberOfMailSent, $mail);

        if ($callback) {
            foreach ($mail as $testMail) {
                $callback($testMail);
            }
        }

        return $this;
    }

    public function assertMailSentFromModule(string $module, ?\Closure $callback = null): self
    {
        $mail = $this->getSentMail(function (TestMail $mail) use ($module): bool {
            return $mail->getModule() === $module;
        });

        Assert::assertNotEmpty($mail);

        if ($callback) {
            foreach ($mail as $testMail) {
                $callback($testMail);
            }
        }

        return $this;
    }

    public function assertNoMailSentFromModule(string $module): self
    {
        $mail = $this->getSentMail(function (TestMail $mail) use ($module): bool {
            return $mail->getModule() === $module;
        });

        Assert::assertEmpty($mail);

        return $this;
    }

    public function assertMailSentTo(string $to, ?\Closure $callback = null): self
    {
        $mail = $this->getMailSentTo($to);

        if ($mail instanceof TestMail) {
            $mail = [$mail];
        }

        if ($mail === []) {
            $this->fail('No email was sent to ' . $to);
        }

        if ($callback) {
            foreach ($mail as $testMail) {
                $callback($testMail);
            }
        }

        return $this;
    }

    public function assertNoMailSentTo(string $to): self
    {
        $mail = $this->getMailSentTo($to);

        Assert::assertEmpty($mail);

        return $this;
    }

    /** The closure is passed to each mail item found with the given subject */
    public function assertMailSentWithSubject(string $subject, ?\Closure $callback = null): self
    {
        $mail = $this->getSentMailWithSubject($subject);

        if (is_array($mail) === false || $mail === []) {
            $this->fail('No email was sent with subject ' . $subject);
        }

        if ($callback) {
            foreach ($mail as $testMail) {
                $callback($testMail);
            }
        }

        return $this;
    }

    public function assertNoMailSentWithSubject(string $subject): self
    {
        $mail = $this->getSentMailWithSubject($subject);

        Assert::assertEmpty($mail);

        return $this;
    }
}
