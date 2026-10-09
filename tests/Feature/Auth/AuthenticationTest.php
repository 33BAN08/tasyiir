<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.login');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'wrong-password');

        $component->call('login');

        $component
            ->assertHasErrors()
            ->assertNoRedirect();

        $this->assertGuest();
    }

    public function test_the_app_shell_renders_for_a_center_user(): void
    {
        ['owner' => $owner] = app(\App\Services\CenterProvisioner::class)
            ->provision('مركز الاختبار', 'المدير', 'owner@test.test', 'secret1234');

        $this->actingAs($owner)->get('/dashboard')
            ->assertOk()
            ->assertSee('مركز الاختبار')
            ->assertSee(route('logout'));
    }

    public function test_users_can_logout(): void
    {
        ['owner' => $owner] = app(\App\Services\CenterProvisioner::class)
            ->provision('مركز الاختبار', 'المدير', 'owner@test.test', 'secret1234');

        $this->actingAs($owner)
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
