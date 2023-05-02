<?php

namespace AidenBradley\DrupalTestSupport\Support;

use AidenBradley\DrupalTestSupport\Support\Mail\TestMail;
use Illuminate\Support\Collection;

trait InteractsWithMail
{
    /** @return TestMail[] */
    public function getSentMail(?string $fromModule = null): array
    {
        $mail = $this->container->get('state')->get('system.test_mail_collector');

        if ($mail === null) {
            return [];
        }

        return collect($mail)->when($fromModule, function (Collection $mail, string $fromModule) {
            return $mail->filter(function (array $mail) use ($fromModule) {
                return $mail['module'] === $fromModule;
            });
        })->mapInto(TestMail::class)->toArray();
    }

    public function assertMailSent(?int $numberOfMailSent = null): self
    {
        $mail = $this->getSentMail();

        $this->assertNotEmpty($mail);

        if ($numberOfMailSent) {
            $this->assertEquals($numberOfMailSent, count($mail));
        }

        return $this;
    }

    public function assertNoMailSent(): self
    {
        $this->assertEmpty($this->getSentMail());

        return $this;
    }

    public function getMailSentTo(string $mailTo): ?TestMail
    {
        foreach ($this->getSentMail() as $mail) {
            if ($mail->getTo() !== $mailTo) {
                continue;
            }

            return $mail;
        }

        return null;
    }

    public function assertMailSentTo(string $to, ?\Closure $callback = null): self
    {
        $mail = $this->getMailSentTo($to);

        if ($mail === null) {
            $this->fail('No email was sent to ' . $to);
        }

        $this->assertEquals($to, $mail->getTo());

        if ($callback) {
            $callback($mail);
        }

        return $this;
    }

    /** @return TestMail[] */
    public function getMailWithSubject(string $subject): array
    {
        $sentMail = [];

        foreach ($this->getSentMail() as $mail) {
            if ($mail->getSubject() !== $subject) {
                continue;
            }

            $sentMail[] = $mail;
        }

        return $sentMail;
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

    public function clearMail(): self
    {
        $this->container->get('state')->set('system.test_mail_collector', []);

        return $this;
    }
}
