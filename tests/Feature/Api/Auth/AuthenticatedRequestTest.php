<?php

use App\Models\User;

it('returns the authenticated user when a valid X-API-Key header is sent', function () {
    $user = User::factory()->create();
    $token = $user->createToken('pest')->plainTextToken;

    $this->withHeader('X-API-Key', $token)
        ->getJson('/api/user')
        ->assertOk()
        ->assertJsonPath('email', $user->email);
});

it('rejects requests that omit the X-API-Key header', function () {
    User::factory()->create();

    $this->getJson('/api/user')->assertStatus(401);
});

it('rejects requests that send a bogus X-API-Key value', function () {
    $this->withHeader('X-API-Key', 'not-a-real-token')
        ->getJson('/api/user')
        ->assertStatus(401);
});

it('does not accept the token via Authorization Bearer header', function () {
    $user = User::factory()->create();
    $token = $user->createToken('pest')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/user')
        ->assertStatus(401);
});

it('revokes the current token on logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('pest')->plainTextToken;

    $this->withHeader('X-API-Key', $token)
        ->postJson('/api/logout')
        ->assertOk()
        ->assertJsonPath('message', 'Logged out.');

    expect($user->fresh()->tokens)->toHaveCount(0);

    $this->withHeader('X-API-Key', $token)
        ->getJson('/api/user')
        ->assertStatus(401);
});
