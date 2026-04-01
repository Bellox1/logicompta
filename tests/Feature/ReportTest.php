<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function testPdfExportResultat()
    {
        $this->seed();
        $user = User::first();
        $this->actingAs($user);

        $response = $this->get('/accounting/resultat-pdf');
        $response->assertStatus(200);
    }
}
