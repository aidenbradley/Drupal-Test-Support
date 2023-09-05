<?php

namespace Drupal\Tests\test_support\Unit\Support\Mail;

use Drupal\Tests\test_support\Traits\Support\Mail\TestMail;
use Drupal\Tests\UnitTestCase;
use Drupal\user\Entity\User;
use Prophecy\PhpUnit\ProphecyTrait;

class TestMailTest extends UnitTestCase
{
    use ProphecyTrait;

    /** @test */
    public function get_to(): void
    {
        $mail = TestMail::createFromValues([
            'to' => 'hello@example.com',
        ]);

        $this->assertEquals('hello@example.com', $mail->getTo());
    }

    /** @test */
    public function assert_mail_sent_to(): void
    {
        $mail = TestMail::createFromValues([
            'to' => 'hello@example.com',
        ]);

        $mail->assertSentTo('hello@example.com');
    }

    /** @test */
    public function get_subject(): void
    {
        $mail = TestMail::createFromValues([
            'subject' => 'email subject',
        ]);

        $this->assertEquals('email subject', $mail->getSubject());
    }

    /** @test */
    public function assert_subject(): void
    {
        $mail = TestMail::createFromValues([
            'subject' => 'email subject',
        ]);

        $mail->assertSubject('email subject');
    }

    /** @test */
    public function get_body(): void
    {
        $mail = TestMail::createFromValues([
            'body' => 'email body',
        ]);
        $this->assertEquals('email body', $mail->getBody());

        $mail = TestMail::createFromValues([
            'body' => 'email body' . PHP_EOL,
        ]);
        $this->assertEquals('email body', $mail->getBody());
    }

    /** @test */
    public function assert_body(): void
    {
        $mail = TestMail::createFromValues([
            'body' => 'email body',
        ]);

        $mail->assertBody('email body');
    }

    /** @test */
    public function get_module(): void
    {
        $mail = TestMail::createFromValues([
            'module' => 'test_support',
        ]);

        $this->assertEquals('test_support', $mail->getModule());
    }

    /** @test */
    public function assert_sent_from_module(): void
    {
        $mail = TestMail::createFromValues([
            'module' => 'test_support',
        ]);

        $mail->assertSentFromModule('test_support');
    }

    /** @test */
    public function get_param(): void
    {
        $mail = TestMail::createFromValues([
            'params' => [
                'message' => 'mail message',
                'article_title' => 'arbitrary value',
            ],
        ]);

        $this->assertEquals('mail message', $mail->getParameter('message'));
        $this->assertEquals('arbitrary value', $mail->getParameter('article_title'));
    }

    /** @test */
    public function assert_param(): void
    {
        $user = $this->prophesize(User::class);

        /** @phpstan-ignore-next-line */
        $user->id()->willReturn(1);

        /** @phpstan-ignore-next-line */
        $user->getEmail()->willReturn('hello@example.com');

        $mail = TestMail::createFromValues([
            'params' => [
                'message' => 'mail message',
                'article_title' => 'arbitrary value',
                'user' => $user->reveal(),
            ],
        ]);

        $mail->assertParameter('message', 'mail message');
        $mail->assertParameter('article_title', 'arbitrary value');
        $mail->assertParameter('user', $user->reveal(), function (User $user) {
            $this->assertEquals('hello@example.com', $user->getEmail());
        });
    }
}
