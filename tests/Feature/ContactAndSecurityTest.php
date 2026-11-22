<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Deposit;
use App\Models\User;
use Tests\TestCase;

class ContactAndSecurityTest extends TestCase
{
    public function test_guest_can_send_contact_message(): void
    {
        $this->get(route('contact'))->assertOk()->assertSee('Gửi liên hệ');

        $this->post(route('contact.store'), [
            'name' => 'Khach test',
            'phone' => '0912345678',
            'subject' => 'Hoi ve bom BP',
            'message' => 'Cho minh hoi gia bom BP 5 ty.',
        ])->assertRedirect(route('contact'));

        $this->assertDatabaseHas('contact_messages', ['subject' => 'Hoi ve bom BP', 'handled_at' => null]);
    }

    public function test_contact_requires_email_or_phone_and_ignores_honeypot_spam(): void
    {
        $this->post(route('contact.store'), [
            'name' => 'Khach',
            'subject' => 'Thieu lien lac',
            'message' => 'Noi dung du dai de qua.',
        ])->assertSessionHasErrors(['email', 'phone']);

        $this->post(route('contact.store'), [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'subject' => 'Spam',
            'message' => 'Spam spam spam spam.',
            'website' => 'http://spam.example',
        ])->assertRedirect(route('contact'));

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_admin_can_mark_contact_handled(): void
    {
        $admin = User::factory()->admin()->create();
        $contact = ContactMessage::factory()->create(['subject' => 'Can ho tro']);

        $this->actingAs($admin)->get(route('admin.contact-messages.index'))->assertSee('Can ho tro');
        $this->actingAs($admin)->post(route('admin.contact-messages.handle', $contact))->assertRedirect();

        $this->assertNotNull($contact->fresh()->handled_at);
        $this->assertSame($admin->id, $contact->fresh()->handled_by);
    }

    public function test_member_cannot_view_or_cancel_another_members_deposit(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $deposit = Deposit::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($intruder)->get(route('deposits.show', $deposit))->assertForbidden();
        $this->actingAs($intruder)->post(route('deposits.cancel', $deposit))->assertForbidden();
        $this->actingAs($owner)->get(route('deposits.show', $deposit))->assertOk();
    }

    public function test_security_headers_and_noindex_on_private_pages(): void
    {
        $this->get(route('home'))
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeaderMissing('X-Robots-Tag');

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_robots_txt_blocks_admin_and_private_pages(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));

        $this->assertStringContainsString('Disallow: /admin', $robots);
        $this->assertStringContainsString('Disallow: /tai-khoan', $robots);
    }
}
