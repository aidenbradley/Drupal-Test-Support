<?php

namespace Drupal\Tests\test_support\Kernel\Support;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\test_support\Traits\Support\InteractsWithMail;
use Drupal\Tests\test_support\Traits\Support\Mail\TestMail;

class InteractsWithMailTest extends KernelTestBase
{
    use InteractsWithMail;

    /** @var \Drupal\Core\Mail\MailManager */
    private $mailManager;

    private const SEND_MAIL = true;

    private const NO_REPLY = null;

    /** @var string[] */
    protected static $modules = [
        'test_support_mail',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->mailManager = $this->container->get('plugin.manager.mail');
    }

    /** @test */
    public function get_sent_mail(): void
    {
        $this->assertNoMailSent();

        $this->sendMail('hello@example.com', 'Hello', 'Hello, at example.com');

        $this->assertMailSent(1);

        $this->sendMail('hello@example.com', 'Hello', 'Hello, at example.com');

        $this->assertMailSent(2);
    }

    /** @test */
    public function get_sent_mail_from_module(): void
    {
        // this will send from the test_support_mail module
        $this->sendMail('hello@example.com', 'Hello', 'Hello, at example.com');

        $this->assertNotEmpty($this->getSentMail('test_support_mail'));
        $this->assertEmpty($this->getSentMail('node'));
    }

    /** @test */
    public function get_mail_sent_to(): void
    {
        $this->assertEmpty($this->getMailSentTo('hello@example.com'));

        $this->sendMail('hello@example.com', 'Hello', 'Hello, at example.com');

        $this->assertNotEmpty($this->getMailSentTo('hello@example.com'));

        $this->assertMailSentTo('hello@example.com', function (TestMail $mail) {
            $mail->assertSentTo('hello@example.com');
            $mail->assertSubject('Hello');
        });
    }

    /** @test */
    public function get_mail_with_subject(): void
    {
        $this->assertEmpty($this->getMailWithSubject('User Registration'));

        $this->sendMail('hello@example.com', 'User Registration', 'Thanks for registering!');

        $this->assertNotEmpty($this->getMailWithSubject('User Registration'));

        $this->assertMailSentWithSubject('User Registration', function (TestMail $mail) {
            $mail->assertSentTo('hello@example.com');
        });
    }

    /** @test */
    public function multiple_get_mail_with_subject(): void
    {
        $this->assertEmpty($this->getMailWithSubject('User Registration'));

        $this->sendMail('hello@example.com', 'User Registration', 'Thanks for registering!');
        $this->sendMail('hello_again@example.com', 'User Registration', 'Thanks for registering again!');

        $this->assertNotEmpty($this->getMailWithSubject('User Registration'));

        $this->assertMailSentWithSubject('User Registration', function (TestMail $mail) {
            if ($mail->getTo() === 'hello@example.com') {
                $mail->assertBody('Thanks for registering!');
            }

            if ($mail->getTo() === 'hello_again@example.com') {
                $mail->assertBody('Thanks for registering again!');
            }
        });
    }

    /** @test */
    public function sent_mail_contains_subject(): void
    {
        $this->assertEmpty($this->getMailWithSubject('User Registration'));

        $this->sendMail('hello@example.com', 'User Registration', 'Thanks for registering!');

        $this->assertNotEmpty($this->getMailWithSubject('User Registration'));
    }

    /** @test */
    public function clear_mail(): void
    {
        $this->assertNoMailSent();

        $this->sendMail('hello@example.com', 'Hello', 'Hello, at example.com');

        $this->assertMailSent();

        $this->clearMail();

        $this->assertNoMailSent();
    }

    /** @param array<mixed> $params */
    private function sendMail(string $to, string $subject, string $body, array $params = []): void
    {
        $state = $this->container->get('state');

        $state->set('test_support.mail_subject', $subject);
        $state->set('test_support.mail_body', $body);

        $this->mailManager->mail(
            'test_support_mail',
            'test_support_mail',
            $to,
            'en',
            $params,
            self::NO_REPLY,
            self::SEND_MAIL
        );
    }
}
