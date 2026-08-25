<?php

namespace Tests\Feature;

use App\Models\DropiToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DropiSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected string $sampleDropiToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@tienda.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $this->sampleDropiToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYXBwLmRyb3BpLmNvOjgwIiwiaWF0IjoxNzg3NTk3NTMzLCJleHAiOjQ5NDMyNzExMzMsIm5iZiI6MTc4NzU5NzUzMywianRpIjoiWWxUb3NqczNCQVF5N1Q0QiIsInN1YiI6IjM2MTg2NCIsInBydiI6Ijg3ZTBhZjFlZjlmZDE1ODEyZmRlYzk3MTUzYTE0ZTBiMDQ3NTQ2YWEiLCJhdWQiOiJXT09DT01FUkNFIiwidG9rZW5fdHlwZSI6IklOVEVHUkFUSU9OUyIsIndiX2lkIjoxLCJpbnRlZ3JhdGlvbl90eXBlIjoiV09PQ09NRVJDRSIsImludGVncmF0aW9uX3R5cGVfaWQiOjEsImlwX3VybCI6W10sImludGVncmF0aW9uX3VybCI6Imh0dHBzOi8vdGllbmRhLmhhbXN0ZXJzb2Z0d2FyZS5jb20ifQ.OwRhf_UySrddaA7EPnn_69MnYq_I0WdcIGedQezpig8';
    }

    public function test_admin_can_access_dropi_settings_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dropi/settings');
        $response->assertStatus(200);
        $response->assertSee('Configuración');
        $response->assertSee('Token JWT de Autenticación Dropi');
    }

    public function test_admin_can_save_and_validate_dropi_token(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/dropi/settings', [
            'store' => 'Tienda Principal Hamster',
            'token' => $this->sampleDropiToken,
            'sync' => 'AUTOMÁTICAMENTE',
            'create_prod_empr' => 1,
            'api_url' => 'https://api.dropi.co/api/',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('dropi_tokens', [
            'store' => 'Tienda Principal Hamster',
            'user_id_dropi' => '361864',
            'is_valid' => true,
        ]);
    }

    public function test_admin_can_trigger_token_revalidation(): void
    {
        DropiToken::create([
            'store' => 'Tienda 1',
            'token' => $this->sampleDropiToken,
            'sync' => 'AUTOMÁTICAMENTE',
            'create_prod_empr' => true,
        ]);

        $response = $this->actingAs($this->admin)->post('/admin/dropi/settings/validate');
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $token = DropiToken::first();
        $this->assertTrue($token->is_valid);
        $this->assertEquals('361864', $token->user_id_dropi);
    }

    public function test_invalid_token_is_flagged(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/dropi/settings', [
            'store' => 'Tienda Test',
            'token' => 'invalid.jwt.token',
            'sync' => 'MANUALMENTE',
            'create_prod_empr' => 0,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('warning');

        $token = DropiToken::first();
        $this->assertFalse($token->is_valid);
    }
}
