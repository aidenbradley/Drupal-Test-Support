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
        $this->sendMail('hello@example.com', 'Welcome Email', 'Welcome to Drupal!');

        $this->assertIsArray($this->getSentMail());
    }

    /** @test */
    public function get_sent_mail_to_single(): void
    {
        $this->sendMail('hello@example.com', 'Welcome Email', 'Welcome to Drupal!');

        $this->assertMailSentCount(1);

        $mail = $this->getMailSentTo('hello@example.com');

        $this->assertInstanceOf(TestMail::class, $mail);
    }

    /** @test */
    public function get_sent_mail_to_multiple(): void
    {
        $this->sendMail('hello@example.com', 'Welcome Email', 'Welcome to Drupal!');
        $this->sendMail('hello@example.com', 'Email Verification', 'Click here to verify');

        $this->assertMailSentCount(2);

        $mail = $this->getMailSentTo('hello@example.com');

        $this->assertIsArray($mail);
        $this->assertCount(2, $mail);
    }

    /** @test */
    public function get_mail_with_subject_single(): void
    {
        $this->sendMail('hello@example.com', 'Welcome Email', 'Welcome to Drupal!');

        $this->assertMailSentCount(1);

        $mail = $this->getMailWithSubject('Welcome Email');

        $this->assertInstanceOf(TestMail::class, $mail);
    }

    /** @test */
    public function get_mail_with_subject_multiple(): void
    {
        $this->sendMail('hello@example.com', 'Welcome Email', 'Welcome to Drupal!');
        $this->sendMail('welcome@example.com', 'Welcome Email', 'Welcome to Drupal!');

        $this->assertMailSentCount(2);

        $mail = $this->getMailWithSubject('Welcome Email');

        $this->assertIsArray($mail);
        $this->assertCount(2, $mail);
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

    /** @test */
    public function assert_no_mail_sent(): void
    {
        $this->clearMail();

        $this->assertNoMailSent();
    }

    /** @test */
    public function assert_mail_sent(): void
    {
        $this->assertNoMailSent();

        $this->sendMail('hello@example.com', 'Hello', 'Hello, at example.com');

        $this->assertMailSent();
    }

    /** @test */
    public function assert_mail_sent_with_closure_assertion(): void
    {
        $this->sendMail('hello@example.com', 'Hello', 'Hello, at example.com');

        $this->assertMailSent(function (TestMail $mail): void {
            $mail->assertSentTo('hello@example.com');
        });
    }

    /** @test */
    public function assert_mail_sent_multiple_with_closure_assertion(): void
    {
        $this->sendMail('hello@example.com', 'Hello', 'Hello, at example.com');
        $this->sendMail('example@example.com', 'Example', 'Example, at example.com');

        $this->assertMailSent(function (TestMail $mail): void {
            if ($mail->getTo() === 'hello@example.com') {
                $mail->assertSubject('Hello');
            }

            if ($mail->getTo() === 'example@example.com') {
                // Do we try catch in the assertion and re-throw our own assertion
                // then handle that in InteractsWithMail with a more descrpitive failure message?
                $mail->assertSubject('Example');
            }
        });
    }

    /** @test */
    public function assert_number_of_mail_sent(): void
    {
        $this->assertNoMailSent();

        $this->sendMail('hello@example.com', 'Hello', 'Hello, at example.com');

        $this->assertMailSentCount(1);

        $this->sendMail('hello@example.com', 'Hello', 'Hello, at example.com');

        $this->assertMailSentCount(2);
    }

    /** @test */
    public function assert_number_of_mail_sent_with_closure_assertion(): void
    {
        $this->assertNoMailSent();

        $this->sendMail('hello@example.com', 'Hello', 'Hello, at example.com');

        $this->assertMailSentCount(1, function (TestMail $mail): void {
            $mail->assertSentTo('hello@example.com');
        });

        $this->sendMail('example@example.com', 'Example', 'Hello, at example.com');

        $this->assertMailSentCount(2, function (TestMail $mail): void {
            if ($mail->getTo() === 'hello@example.com') {
                $mail->assertSubject('Hello');
            }

            if ($mail->getTo() === 'example@example.com') {
                $mail->assertSubject('Example');
            }
        });
    }

    /** @test */
    public function assert_no_mail_sent_from_module(): void
    {
        $this->clearMail();

        $this->assertNoMailSentFromModule('test_support_mail');
    }

    /** @test */
    public function assert_mail_sent_from_module(): void
    {
        $this->assertNoMailSentFromModule('test_support_mail');

        $this->sendMail('hello@example.com', 'Hello', 'Hello, at example.com');

        $this->assertMailSentFromModule('test_support_mail');
    }

    /** @test */
    public function assert_mail_sent_from_module_closure_assertion(): void
    {
        $this->sendMail('hello@example.com', 'Hello', 'Hello, at example.com');

        $this->assertMailSentFromModule('test_support_mail', function (TestMail $mail): void {
            $mail->assertSentTo('hello@example.com');
        });

        $this->sendMail('example@example.com', 'Example', 'Hello, at example.com');

        $this->assertMailSentFromModule('test_support_mail', function (TestMail $mail): void {
            if ($mail->getTo() === 'hello@example.com') {
                $mail->assertSubject('Hello');
            }

            if ($mail->getTo() === 'example@example.com') {
                $mail->assertSubject('Example');
            }
        });
    }

    /** @test */
    public function assert_no_mail_sent_to(): void
    {
        $this->sendMail('hello@example.com', 'Hello', 'Hello, at example.com');

        $this->clearMail();

        $this->assertNoMailSentTo('hello@example.com');
    }

    /** @test */
    public function assert_mail_sent_to(): void
    {
        $this->assertNoMailSentTo('hello@example.com');

        $this->sendMail('hello@example.com', 'Hello', 'Hello, at example.com');

        $this->assertMailSentTo('hello@example.com');

        $this->assertMailSentTo('hello@example.com', function (TestMail $mail) {
            $mail->assertSubject('Hello');
        });
    }

    /** @test */
    public function assert_mail_sent_to_multiple(): void
    {
        $this->assertNoMailSentTo('hello@example.com');

        $this->sendMail('hello@example.com', 'User Registration', 'Thanks for registering');
        $this->sendMail('hello@example.com', 'Welcome Email', 'Welcome to Drupal!');

        $this->assertMailSentTo('hello@example.com');

        $this->assertMailSentTo('hello@example.com', function (TestMail $mail) {
            if ($mail->getSubject() === 'User Registration') {
                $mail->assertSentTo('hello@example.com');
                $mail->assertSubject('User Registration');
            }

            if ($mail->getSubject() === 'Welcome Email') {
                $mail->assertSentTo('hello@example.com');
                $mail->assertSubject('Welcome Email');
            }
        });
    }

    /** @test */
    public function assert_mail_sent_with_subject(): void
    {
        $this->assertNoMailSentWithSubject('User Registration');

        $this->sendMail('hello@example.com', 'User Registration', 'Thanks for registering!');
        $this->sendMail('hello_again@example.com', 'User Registration', 'Thanks for registering again!');

        $this->assertMailSentWithSubject('User Registration');

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
    public function assert_no_mail_sent_with_subject(): void
    {
        $this->assertNoMailSentWithSubject('User Registration');

        $this->sendMail('hello@example.com', 'User Account Updated', 'Thanks for updating your account!');

        $this->assertNoMailSentWithSubject('User Registration');
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
