<?php

use App\Models\User;

it('issues a token in exchange for valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'ada@example.com',
        'password' => 'correct-horse-battery-staple',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'ada@example.com',
        'password' => 'correct-horse-battery-staple',
        'device_name' => 'pest-test',
    ]);

    $response
        ->assertOk()
        ->assertJsonStructure(['data' => ['token', 'device_name', 'user' => ['id', 'name', 'email']]])
        ->assertJsonPath('data.user.email', 'ada@example.com')
        ->assertJsonPath('data.device_name', 'pest-test');

    expect($user->fresh()->tokens)->toHaveCount(1);
});

it('rejects an unknown email with a 422 validation error', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'nobody@example.com',
        'password' => 'whatever',
        'device_name' => 'pest-test',
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('rejects a wrong password with a 422 validation error', function () {
    User::factory()->create([
        'email' => 'ada@example.com',
        'password' => 'correct-horse-battery-staple',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'ada@example.com',
        'password' => 'wrong-password',
        'device_name' => 'pest-test',
    ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('requires email, password and device_name', function () {
    $this->postJson('/api/login', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password', 'device_name']);
});
