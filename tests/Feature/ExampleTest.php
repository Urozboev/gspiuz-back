<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Sayt ildizi admin panelning kirish sahifasiga yo'naltiradi
     * (routes/web.php da shunday belgilangan).
     */
    public function test_root_redirects_to_the_login_page()
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}
