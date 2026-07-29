<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Requests\ContactRequest;
use Illuminate\Support\Facades\Validator;

class ContactRequestTest extends TestCase
{
    private function validate(array $data)
    {
        $request = new ContactRequest();
        return Validator::make($data, $request->rules(), $request->messages());
    }

    public function test_valid_data_passes(): void
    {
        $validator = $this->validate([
            'name' => 'Иван Петров',
            'email' => 'ivan@example.com',
            'phone' => '+79001234567',
            'comment' => 'Здравствуйте! Это тестовое сообщение.'
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_name_is_required(): void
    {
        $validator = $this->validate([
            'email' => 'ivan@example.com',
            'phone' => '+79001234567',
            'comment' => 'Тестовое сообщение'
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_email_is_required(): void
    {
        $validator = $this->validate([
            'name' => 'Иван Петров',
            'phone' => '+79001234567',
            'comment' => 'Тестовое сообщение'
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_phone_is_required(): void
    {
        $validator = $this->validate([
            'name' => 'Иван Петров',
            'email' => 'ivan@example.com',
            'comment' => 'Тестовое сообщение'
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('phone', $validator->errors()->toArray());
    }

    public function test_comment_is_required(): void
    {
        $validator = $this->validate([
            'name' => 'Иван Петров',
            'email' => 'ivan@example.com',
            'phone' => '+79001234567'
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('comment', $validator->errors()->toArray());
    }

    public function test_name_min_length(): void
    {
        $validator = $this->validate([
            'name' => 'А',
            'email' => 'ivan@example.com',
            'phone' => '+79001234567',
            'comment' => 'Тестовое сообщение'
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_name_max_length(): void
    {
        $validator = $this->validate([
            'name' => str_repeat('а', 101),
            'email' => 'ivan@example.com',
            'phone' => '+79001234567',
            'comment' => 'Тестовое сообщение'
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_valid_email_format(): void
    {
        $validator = $this->validate([
            'name' => 'Иван Петров',
            'email' => 'not-email',
            'phone' => '+79001234567',
            'comment' => 'Тестовое сообщение'
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_email_max_length(): void
    {
        $validator = $this->validate([
            'name' => 'Иван Петров',
            'email' => str_repeat('a', 250) . '@example.com',
            'phone' => '+79001234567',
            'comment' => 'Тестовое сообщение'
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_phone_format_valid(): void
    {
        $validator = $this->validate([
            'name' => 'Иван Петров',
            'email' => 'ivan@example.com',
            'phone' => '+79001234567',
            'comment' => 'Тестовое сообщение'
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_phone_format_invalid(): void
    {
        $validator = $this->validate([
            'name' => 'Иван Петров',
            'email' => 'ivan@example.com',
            'phone' => '+7123',
            'comment' => 'Тестовое сообщение'
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('phone', $validator->errors()->toArray());
    }

    public function test_comment_min_length(): void
    {
        $validator = $this->validate([
            'name' => 'Иван Петров',
            'email' => 'ivan@example.com',
            'phone' => '+79001234567',
            'comment' => 'Коротко'
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('comment', $validator->errors()->toArray());
    }

    public function test_comment_max_length(): void
    {
        $validator = $this->validate([
            'name' => 'Иван Петров',
            'email' => 'ivan@example.com',
            'phone' => '+79001234567',
            'comment' => str_repeat('а', 5001)
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('comment', $validator->errors()->toArray());
    }
}
