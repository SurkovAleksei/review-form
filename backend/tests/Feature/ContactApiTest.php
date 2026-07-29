<?php

namespace Tests\Feature;

use Tests\TestCase;

class ContactApiTest extends TestCase
{
    public function test_can_submit_valid_contact_form(): void
    {
        $response = $this->postJson('/api/contact', [
            'name' => 'Иван Петров',
            'email' => 'ivan@example.com',
            'phone' => '+79001234567',
            'comment' => 'Здравствуйте! Это тестовое сообщение для проверки API.'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Сообщение успешно получено'
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'name',
                    'email',
                    'phone',
                    'received_at'
                ]
            ]);
    }

    public function test_can_submit_with_phone_without_plus(): void
    {
        $response = $this->postJson('/api/contact', [
            'name' => 'Иван Петров',
            'email' => 'ivan@example.com',
            'phone' => '79001234567',
            'comment' => 'Тестовое сообщение'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonPath('data.phone', '+79001234567');
    }

    public function test_can_submit_with_phone_with_8(): void
    {
        $response = $this->postJson('/api/contact', [
            'name' => 'Иван Петров',
            'email' => 'ivan@example.com',
            'phone' => '89001234567',
            'comment' => 'Тестовое сообщение'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonPath('data.phone', '+79001234567');
    }

    public function test_fails_without_name(): void
    {
        $response = $this->postJson('/api/contact', [
            'email' => 'ivan@example.com',
            'phone' => '+79001234567',
            'comment' => 'Тестовое сообщение'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_fails_without_email(): void
    {
        $response = $this->postJson('/api/contact', [
            'name' => 'Иван Петров',
            'phone' => '+79001234567',
            'comment' => 'Тестовое сообщение'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_fails_without_phone(): void
    {
        $response = $this->postJson('/api/contact', [
            'name' => 'Иван Петров',
            'email' => 'ivan@example.com',
            'comment' => 'Тестовое сообщение'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_fails_without_comment(): void
    {
        $response = $this->postJson('/api/contact', [
            'name' => 'Иван Петров',
            'email' => 'ivan@example.com',
            'phone' => '+79001234567'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['comment']);
    }

    public function test_fails_with_short_name(): void
    {
        $response = $this->postJson('/api/contact', [
            'name' => 'А',
            'email' => 'ivan@example.com',
            'phone' => '+79001234567',
            'comment' => 'Тестовое сообщение'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_fails_with_long_name(): void
    {
        $response = $this->postJson('/api/contact', [
            'name' => str_repeat('а', 101),
            'email' => 'ivan@example.com',
            'phone' => '+79001234567',
            'comment' => 'Тестовое сообщение'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_fails_with_invalid_email(): void
    {
        $response = $this->postJson('/api/contact', [
            'name' => 'Иван Петров',
            'email' => 'not-email',
            'phone' => '+79001234567',
            'comment' => 'Тестовое сообщение'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_fails_with_long_email(): void
    {
        $response = $this->postJson('/api/contact', [
            'name' => 'Иван Петров',
            'email' => str_repeat('a', 250) . '@example.com',
            'phone' => '+79001234567',
            'comment' => 'Тестовое сообщение'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_fails_with_short_phone(): void
    {
        $response = $this->postJson('/api/contact', [
            'name' => 'Иван Петров',
            'email' => 'ivan@example.com',
            'phone' => '123',
            'comment' => 'Тестовое сообщение для проверки'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_fails_with_phone_less_than_10_digits(): void
    {
        $response = $this->postJson('/api/contact', [
            'name' => 'Иван Петров',
            'email' => 'ivan@example.com',
            'phone' => '+712345',
            'comment' => 'Тестовое сообщение для проверки'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_fails_with_long_phone(): void
    {
        $response = $this->postJson('/api/contact', [
            'name' => 'Иван Петров',
            'email' => 'ivan@example.com',
            'phone' => '+790012345678901234567',
            'comment' => 'Тестовое сообщение'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_fails_with_short_comment(): void
    {
        $response = $this->postJson('/api/contact', [
            'name' => 'Иван Петров',
            'email' => 'ivan@example.com',
            'phone' => '+79001234567',
            'comment' => 'Коротко'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['comment']);
    }

    public function test_fails_with_long_comment(): void
    {
        $response = $this->postJson('/api/contact', [
            'name' => 'Иван Петров',
            'email' => 'ivan@example.com',
            'phone' => '+79001234567',
            'comment' => str_repeat('а', 5001)
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['comment']);
    }

    public function test_strips_html_tags_from_name(): void
    {
        $response = $this->postJson('/api/contact', [
            'name' => '<script>alert(1)</script>Иван',
            'email' => 'ivan@example.com',
            'phone' => '+79001234567',
            'comment' => 'Тестовое сообщение'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Иван');
    }

    public function test_strips_html_tags_from_comment(): void
    {
        $response = $this->postJson('/api/contact', [
            'name' => 'Иван Петров',
            'email' => 'ivan@example.com',
            'phone' => '+79001234567',
            'comment' => '<b>Тестовое</b> сообщение'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.comment', 'Тестовое сообщение');
    }

    public function test_health_endpoint(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok'
            ]);
    }
}
