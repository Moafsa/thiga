<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PhoneLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = \App\Models\Plan::create([
            'name' => 'Basic Plan',
            'price' => 100.00,
        ]);

        $this->tenant = Tenant::create([
            'name' => 'Test Tenant',
            'domain' => 'test',
            'cnpj' => '12345678000199',
            'plan_id' => $plan->id,
        ]);

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Phone User',
            'email' => 'phone@example.com',
            'phone' => '(11) 98765-4321', // Formatted
            'password' => Hash::make('password123'),
        ]);
    }

    public function test_login_with_valid_phone()
    {
        $response = $this->post('/login', [
            'email' => '(11) 98765-4321', // Sending phone in email field
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_login_with_normalized_phone()
    {
        $response = $this->post('/login', [
            'email' => '11987654321', // Sending clean phone
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_login_fails_with_wrong_password()
    {
        $response = $this->post('/login', [
            'email' => '11987654321',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_with_email_still_works()
    {
        $response = $this->post('/login', [
            'email' => 'phone@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->user);
    }
}
