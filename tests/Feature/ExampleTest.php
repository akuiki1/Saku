<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_root_diarahkan_ke_panel_admin(): void
    {
        $this->get('/')->assertRedirect('/admin');
    }
}
