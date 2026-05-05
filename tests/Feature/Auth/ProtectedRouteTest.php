<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProtectedRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_protected_route(): void
    {
        $this->get('/users')->assertRedirect('/login');
    }
}
