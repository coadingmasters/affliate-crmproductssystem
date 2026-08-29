<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LoginPasswordToggleTest extends TestCase
{
    use RefreshDatabase;

    public static function loginRoutes(): array
    {
        return [
            'customer' => ['login'],
            'admin' => ['admin.login'],
        ];
    }

    #[DataProvider('loginRoutes')]
    public function test_the_password_field_can_be_revealed(string $route): void
    {
        $this->get(route($route))
            ->assertOk()
            ->assertSee('data-password-toggle="password"', false)
            ->assertSee('aria-label="Show password"', false)
            // the shared script has to survive the layout's section handling
            ->assertSee('field.type = revealed ?', false);
    }
}
