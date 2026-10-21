<?php

namespace Tests\Feature;

use App\Filament\Pages\Backups as BackupsPage;
use App\Filament\Pages\SystemHealth;
use App\Filament\Resources\AuditLogs\Pages\ListAuditLogs;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\AuditLog;
use App\Models\Faq;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\Backups;
use App\Support\Impersonation;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Livewire\Livewire;
use Tests\TestCase;
use ZipArchive;

class SuperAdminToolsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');
    }

    public function test_only_super_admins_can_open_the_system_tools(): void
    {
        $urls = ['/admin/activity-log', '/admin/backups', '/admin/system'];

        $this->actingAs(User::factory()->create());
        foreach ($urls as $url) {
            $this->get($url)->assertForbidden();
        }

        $this->actingAs(User::factory()->superAdmin()->create());
        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_content_changes_are_logged_with_only_the_fields_that_changed(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $faq = Faq::create(['question' => 'Do you travel?', 'answer' => 'Yes, across Greater Victoria.']);
        $faq->update(['question' => 'Do you travel to Sooke?']);
        $faq->update(['question' => 'Do you travel to Sooke?']);
        $faq->delete();

        $logs = AuditLog::where('auditable_type', Faq::class)->orderBy('id')->get();

        $this->assertSame(['created', 'updated', 'deleted'], $logs->pluck('event')->all());
        $this->assertSame($admin->id, $logs[1]->user_id);
        $this->assertSame('Do you travel to Sooke?', $logs[1]->auditable_label);
        $this->assertSame(['question' => 'Do you travel?'], $logs[1]->old_values);
        $this->assertSame(['question' => 'Do you travel to Sooke?'], $logs[1]->new_values);
    }

    public function test_settings_changes_log_only_the_nested_keys_that_changed(): void
    {
        SiteSetting::set('contact', ['email' => 'old@example.com', 'phone' => '250-555-0100']);
        SiteSetting::set('contact', ['email' => 'new@example.com', 'phone' => '250-555-0100']);

        $log = AuditLog::where('event', 'updated')->sole();

        $this->assertSame('Site settings: Contact', $log->auditable_label);
        $this->assertSame(['value.email' => 'old@example.com'], $log->old_values);
        $this->assertSame(['value.email' => 'new@example.com'], $log->new_values);
    }

    public function test_passwords_are_never_written_to_the_log(): void
    {
        $user = User::factory()->create();
        $user->update(['password' => 'a-brand-new-secret']);

        $log = AuditLog::where('event', 'updated')->where('auditable_id', $user->id)->sole();

        $this->assertSame(['password' => '(changed)'], $log->new_values);
        $this->assertStringNotContainsString('a-brand-new-secret', AuditLog::all()->toJson());
    }

    public function test_sign_ins_and_failed_attempts_are_logged(): void
    {
        $user = User::factory()->create(['email' => 'aubri@example.com']);

        Auth::attempt(['email' => 'aubri@example.com', 'password' => 'wrong']);
        Auth::attempt(['email' => 'aubri@example.com', 'password' => 'password']);

        $this->assertDatabaseHas('audit_logs', ['event' => 'login_failed', 'description' => 'Failed sign-in for aubri@example.com']);
        $this->assertDatabaseHas('audit_logs', ['event' => 'login', 'user_id' => $user->id]);
    }

    public function test_super_admin_can_view_log_details(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $faq = Faq::create(['question' => 'Do you travel?', 'answer' => 'Yes.']);
        $faq->update(['question' => 'Do you travel to Sooke?']);

        Livewire::test(ListAuditLogs::class)
            ->assertCanSeeTableRecords(AuditLog::all())
            ->mountAction(TestAction::make('view')->table(AuditLog::where('event', 'updated')->sole()))
            ->assertSee('Do you travel to Sooke?');
    }

    public function test_backup_contains_the_database_and_uploads(): void
    {
        Storage::disk('public')->put('services/photo.jpg', UploadedFile::fake()->image('photo.jpg')->getContent());
        Faq::create(['question' => 'Do you travel?', 'answer' => 'Yes.']);

        $name = app(Backups::class)->create();

        $zip = new ZipArchive;
        $this->assertTrue($zip->open(app(Backups::class)->path($name)));
        $this->assertNotFalse($zip->locateName('manifest.json'));
        $this->assertNotFalse($zip->locateName('uploads/services/photo.jpg'));
        $this->assertSame('Do you travel?', json_decode($zip->getFromName('database/faqs.json'), true)[0]['question']);
        $this->assertFalse($zip->locateName('database/sessions.json'));
        $zip->close();

        $this->assertDatabaseHas('audit_logs', ['event' => 'backup_created']);
    }

    public function test_backup_command_prunes_old_backups_but_keeps_the_newest(): void
    {
        $disk = Storage::disk('local');

        foreach (range(1, 9) as $daysAgo) {
            $file = 'backups/backup-'.now()->subDays($daysAgo * 10)->format('Y-m-d-His').'.zip';
            $disk->put($file, 'old');
            touch($disk->path($file), now()->subDays($daysAgo * 10)->getTimestamp());
        }

        $this->artisan('app:backup --prune')->assertSuccessful();

        // 1 new + 9 old = 10; the newest 7 are always kept, older than 30 days are removed.
        $this->assertCount(Backups::KEEP_AT_LEAST, $disk->files('backups'));
    }

    public function test_backups_can_be_created_and_deleted_from_the_admin(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        Livewire::test(BackupsPage::class)->callAction('createBackup')->assertNotified('Backup created');

        $name = app(Backups::class)->latest()['name'];

        Livewire::test(BackupsPage::class)
            ->callAction(TestAction::make('delete')->table($name))
            ->assertNotified('Backup deleted');

        $this->assertNull(app(Backups::class)->latest());
    }

    public function test_backup_names_cannot_point_outside_the_backups_folder(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(Backups::class)->delete('../../.env');
    }

    public function test_super_admin_can_log_in_as_another_admin_and_return(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $aubri = User::factory()->create(['name' => 'Aubri']);

        $this->actingAs($superAdmin);

        Livewire::test(ListUsers::class)
            ->callAction(TestAction::make('impersonate')->table($aubri))
            ->assertRedirect();

        $this->assertTrue(Auth::user()->is($aubri));
        $this->assertSame($superAdmin->id, session(Impersonation::SESSION_KEY));

        $this->get('/admin')->assertOk()->assertSee('You are logged in as');

        $this->post('/impersonation/stop')->assertRedirect();

        $this->assertTrue(Auth::user()->is($superAdmin));
        $this->assertDatabaseHas('audit_logs', ['event' => 'impersonation_started', 'user_id' => $superAdmin->id, 'auditable_id' => $aubri->id]);
        $this->assertDatabaseMissing('audit_logs', ['event' => 'login']);
    }

    public function test_regular_admins_cannot_log_in_as_others_or_touch_super_admin_accounts(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $other = User::factory()->create();

        $this->actingAs(User::factory()->create());

        Livewire::test(ListUsers::class)
            ->assertActionHidden(TestAction::make('impersonate')->table($other))
            ->assertActionHidden(TestAction::make('edit')->table($superAdmin))
            ->assertActionHidden(TestAction::make('delete')->table($superAdmin));

        $this->get("/admin/users/{$superAdmin->id}/edit")->assertForbidden();
    }

    public function test_super_admins_cannot_be_impersonated(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        Livewire::test(ListUsers::class)
            ->assertActionHidden(TestAction::make('impersonate')->table(User::factory()->superAdmin()->create()));
    }

    public function test_system_page_shows_recent_log_entries_newest_first(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        config(['logging.channels.single.path' => storage_path('framework/testing/system-page-test.log')]);
        @mkdir(dirname(SystemHealth::logPath()), recursive: true);

        file_put_contents(SystemHealth::logPath(), "[2026-10-01 10:00:00] local.ERROR: first problem\n[2026-10-02 10:00:00] local.ERROR: second problem\n");

        try {
            $tail = SystemHealth::logTail();
            $this->assertLessThan(strpos($tail, 'first problem'), strpos($tail, 'second problem'));

            Livewire::test(SystemHealth::class)
                ->assertSee('second problem')
                ->callAction('clearLog')
                ->assertNotified('Log cleared');

            $this->assertSame('', file_get_contents(SystemHealth::logPath()));
        } finally {
            @unlink(SystemHealth::logPath());
        }
    }

    public function test_command_grants_and_revokes_super_admin(): void
    {
        $user = User::factory()->create(['email' => 'riley@example.com']);

        $this->artisan('app:super-admin riley@example.com')->assertSuccessful();
        $this->assertTrue($user->fresh()->isSuperAdmin());

        $this->artisan('app:super-admin riley@example.com --revoke')->assertSuccessful();
        $this->assertFalse($user->fresh()->isSuperAdmin());

        $this->artisan('app:super-admin nobody@example.com')->assertFailed();
    }
}
