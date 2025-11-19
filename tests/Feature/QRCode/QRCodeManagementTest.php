<?php

namespace Tests\Feature\QRCode;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QRCodeManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Seed permissions and roles
        $this->seed(\Database\Seeders\LaratrustSeeder::class);

        $this->user = User::factory()->create();
        $this->business = Business::factory()->create([
            'onboarding_completed_at' => now(),
        ]);
        
        $this->business->users()->attach($this->user->id, [
            'role' => 'owner',
            'is_active' => true,
            'joined_at' => now(),
        ]);

        // Assign owner role via Laratrust
        $ownerRole = \App\Models\Role::where('name', 'owner')->first();
        if ($ownerRole) {
            $this->user->addRole($ownerRole, $this->business);
        }
    }

    public function test_unauthenticated_user_cannot_access_qr_code_designer(): void
    {
        $response = $this->get(route('qr-code.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_qr_code_preview_route_exists(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('qr-code.preview'), [
                'size' => 400,
                'foreground_color' => '#FF0000',
                'background_color' => '#FFFFFF',
                'margin' => 10,
            ]);

        // Route exists and processes request (may redirect or return content)
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_qr_code_download_route_exists(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('qr-code.download'), [
                'format' => 'svg',
                'size' => 800,
            ]);

        $this->assertTrue(in_array($response->status(), [200, 302]));
    }
}
