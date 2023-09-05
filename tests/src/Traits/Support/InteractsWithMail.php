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
        $mail = $this->getSentMail(function(TestMail $mail) use($mailTo): bool {
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
    public function getMailWithSubject(string $subject)
    {
        $mail = $this->getSentMail(function(TestMail $mail) use($subject): bool {
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
        $this->assertEmpty($this->getSentMail());

        return $this;
    }

    public function assertMailSent(): self
    {
        $mail = $this->getSentMail();

        $this->assertNotEmpty($mail);

        return $this;
    }

    public function assertMailSentCount(int $numberOfMailSent): self
    {
        $mail = $this->getSentMail();

        $this->assertCount($numberOfMailSent, $mail);

        return $this;
    }

    public function assertMailSentFromModule(string $module): self
    {
        $mail = $this->getSentMail(function (TestMail $mail) use ($module): bool {
            return $mail->getModule() === $module;
        });

        $this->assertNotEmpty($mail);

        return $this;
    }

    public function assertNoMailSentFromModule(string $module): self
    {
        $mail = $this->getSentMail(function (TestMail $mail) use ($module): bool {
            return $mail->getModule() === $module;
        });

        $this->assertEmpty($mail);

        return $this;
    }

    public function assertMailSentTo(string $to, ?\Closure $callback = null): self
    {
        $mail = (array) $this->getMailSentTo($to);

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

        $this->assertEmpty($mail);

        return $this;
    }

    /** The closure is passed to each mail item found with the given subject */
    public function assertMailSentWithSubject(string $subject, ?\Closure $callback = null): self
    {
        $mailItems = $this->getMailWithSubject($subject);

        if ($mailItems === []) {
            $this->fail('No email was sent with subject ' . $subject);
        }

        foreach ($mailItems as $mail) {
            $this->assertEquals($subject, $mail->getSubject());

            if ($callback === null) {
                continue;
            }

            $callback($mail);
        }

        return $this;
    }

    public function assertNoMailSentWithSubject(string $subject): self
    {
        $mail = $this->getMailWithSubject($subject);

        $this->assertEmpty($mail);

        return $this;
    }
}
