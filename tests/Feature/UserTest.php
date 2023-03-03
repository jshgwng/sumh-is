<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Mail\VerificationCodeEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    // Test if the user can be created successfully with first name, last name, email, username, phone and password and emailed a verification code
    public function testUserCanBeCreatedSuccessfullyWithVerificationCode()
    {
        // Create the user
        $response = $this->post('/api/register', [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->email(),
            'username' => fake()->userName(),
            'phone' => fake()->phoneNumber(),
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        // Check if the user was created successfully
        $response->assertStatus(201);

        // Check if the user was created with the correct data
        $this->assertDatabaseHas('users', [
            'first_name' => $response->json()['user']['first_name'],
            'last_name' => $response->json()['user']['last_name'],
            'email' => $response->json()['user']['email'],
            'username' => $response->json()['user']['username'],
            'phone' => $response->json()['user']['phone'],
        ]);

        // Check if the user was emailed a verification code
        $this->assertDatabaseHas('email_verifications', [
            'email' => $response->json()['user']['email'],
        ]);
    }


// // Test if a user can login successfully
// public function testUserCanLoginSuccessfully()
// {
//     // Create the user
//     $user = $this->post('/api/register', [
//         'first_name' => fake()->firstName(),
//         'last_name' => fake()->lastName(),
//         'email' => fake()->email(),
//         'username' => fake()->userName(),
//         'phone' => fake()->phoneNumber(),
//         'password' => 'password',
//         'password_confirmation' => 'password'
//     ]);

//     // Login the user
//     $response = $this->post('/api/login', [
//         'email' => $user->json()['user']['email'],
//         'password' => 'password'
//     ]);

//     // Check if the user was logged in successfully
//     $response->assertStatus(200);

//     // Check if the user was logged in with the correct data
//     $this->assertDatabaseHas('users', [
//         'first_name' => $response->json()['user']['first_name'],
//         'last_name' => $response->json()['user']['last_name'],
//         'email' => $response->json()['user']['email'],
//         'username' => $response->json()['user']['username'],
//         'phone' => $response->json()['user']['phone'],
//     ]);

//     // Check if the response has a token
//     $this->assertArrayHasKey('token', $response->json());

//     // check if the response has success message
//     $this->assertArrayHasKey('message', $response->json());

//     // check if the response has a status
//     $this->assertArrayHasKey('status', $response->json());
// }

// // Test if the user can verify their email
// public function testUserCanVerifyEmail()
// {
//     // Create the user
//     $user = $this->post('/api/register', [
//         'first_name' => fake()->firstName(),
//         'last_name' => fake()->lastName(),
//         'email' => fake()->email(),
//         'username' => fake()->userName(),
//         'phone' => fake()->phoneNumber(),
//         'password' => 'password',
//         'password_confirmation' => 'password'
//     ]);

//     // Verify the user email
//     $response = $this->get('/api/verify-email/' . $user->json()['user']['email']);

//     // Check if the user was verified successfully
//     $response->assertStatus(200);

//     // Check if the user was verified with the correct data
//     $this->assertDatabaseHas('users', [
//         'first_name' => $response->json()['user']['first_name'],
//         'last_name' => $response->json()['user']['last_name'],
//         'email' => $response->json()['user']['email'],
//         'username' => $response->json()['user']['username'],
//         'phone' => $response->json()['user']['phone'],
//     ]);

//     // check if the response has success message
//     $this->assertArrayHasKey('message', $response->json());

//     // check if the response has a status
//     $this->assertArrayHasKey('status', $response->json());
// }
}