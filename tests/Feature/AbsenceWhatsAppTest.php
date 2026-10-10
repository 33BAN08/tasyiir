<?php

namespace Tests\Feature;

use App\Livewire\Attendance\Index as AttendanceIndex;
use App\Livewire\Attendance\NotifyButton;
use App\Livewire\Settings\CenterProfile;
use App\Models\AttendanceRecord;
use App\Models\Course;
use App\Models\Group;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CenterProvisioner;
use App\Support\AbsenceMessage;
use App\Support\Permissions;
use App\Support\WhatsApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\Concerns\RunsInMode;
use Tests\TestCase;

/** Warning a parent on WhatsApp from the attendance sheet. */
class AbsenceWhatsAppTest extends TestCase
{
    use RefreshDatabase, RunsInMode;

    protected Tenant $tenant;

    protected User $owner;

    protected Group $group;

    protected function setUp(): void
    {
        $this->runInMode('saas');

        parent::setUp();
        config(['tasyiir.mode' => 'saas']);

        ['tenant' => $this->tenant, 'owner' => $this->owner] = app(CenterProvisioner::class)
            ->provision('مركز النور', 'المدير', 'owner@wa.test', 'secret1234');

        $this->tenant->settings = array_merge($this->tenant->settings ?? [], ['phone' => '0522334455']);
        $this->tenant->save();

        Auth::login($this->owner);

        $course = Course::create([
            'tenant_id' => $this->tenant->id, 'name' => 'English A1', 'price' => 300, 'status' => 'نشط',
        ]);

        $this->group = Group::create([
            'tenant_id' => $this->tenant->id, 'name' => 'English A1 - A', 'course_id' => $course->id,
            'capacity' => 20, 'schedule' => 'الاثنين والأربعاء 16:00 - 17:30', 'status' => 'نشط',
        ]);
    }

    protected function tearDown(): void
    {
        $this->restoreMode();

        parent::tearDown();
    }

    protected function student(string $name, array $attributes = []): Student
    {
        return Student::create([
            'tenant_id' => $this->tenant->id,
            'name' => $name,
            'phone' => '0610000000',
            'group_id' => $this->group->id,
            'registered_at' => today()->toDateString(),
            'enrollment_status' => 'نشط',
            'financial_status' => 'غير مؤدي',
            ...$attributes,
        ]);
    }

    // ── 1. the number helper ────────────────────────────────────────────────

    public function test_moroccan_and_international_numbers_are_normalised(): void
    {
        $this->assertSame('212612345678', WhatsApp::normalize('0612-34-56-78'));
        $this->assertSame('212612345678', WhatsApp::normalize('+212 612345678'));
        $this->assertSame('212612345678', WhatsApp::normalize('00212612345678'));
        $this->assertSame('212612345678', WhatsApp::normalize('212612345678'));
        $this->assertSame('212712345678', WhatsApp::normalize('07 12 34 56 78'));
        $this->assertSame('212522334455', WhatsApp::normalize('0522-33-44-55'));
        $this->assertSame('33612345678', WhatsApp::normalize('+33 6 12 34 56 78'), 'a foreign number is kept');
    }

    public function test_numbers_that_cannot_be_dialled_come_back_null(): void
    {
        $this->assertNull(WhatsApp::normalize('612345678'), 'no country code and no leading zero');
        $this->assertNull(WhatsApp::normalize('بدون رقم'));
        $this->assertNull(WhatsApp::normalize(''));
        $this->assertNull(WhatsApp::normalize(null));
        $this->assertNull(WhatsApp::normalize('06123'), 'too short for a national number');
        $this->assertNull(WhatsApp::normalize('1234567890123456'), 'longer than any real number');
        $this->assertNull(WhatsApp::link('garbage', 'hello'));
    }

    public function test_the_link_carries_the_encoded_message(): void
    {
        $link = WhatsApp::link('0612345678', 'السلام عليكم');

        $this->assertStringStartsWith('https://wa.me/212612345678?text=', $link);
        $this->assertStringContainsString(rawurlencode('السلام عليكم'), $link);
        $this->assertSame('https://wa.me/212612345678', WhatsApp::link('0612345678'));
    }

    // ── 2. the panel ────────────────────────────────────────────────────────

    public function test_the_panel_lists_only_absent_and_late_students_with_the_right_number(): void
    {
        $absentWithGuardian = $this->student('أحمد', ['guardian_phone' => '0620000001']);
        $lateWithoutGuardian = $this->student('سارة', ['phone' => '0630000002', 'guardian_phone' => null]);
        $absentWithoutAnyPhone = $this->student('كريم', ['phone' => 'بدون', 'guardian_phone' => null]);
        $present = $this->student('ياسين', ['guardian_phone' => '0640000004']);

        Livewire::test(AttendanceIndex::class)
            ->set('groupId', (string) $this->group->id)
            ->set('date', today()->toDateString())
            ->call('setState', $absentWithGuardian->id, 'غائب')
            ->call('setState', $lateWithoutGuardian->id, 'متأخر')
            ->call('setState', $absentWithoutAnyPhone->id, 'غائب')
            ->call('setState', $present->id, 'حاضر')
            ->call('save')
            ->assertViewHas('toNotify', fn ($rows) => $rows->count() === 3)
            ->assertViewHas('notifiedCount', 0);

        $records = AttendanceRecord::with('student')->get()->keyBy('student_id');

        // The parent's number wins.
        $this->assertTrue($records[$absentWithGuardian->id]->notifyPhone()['isGuardian']);
        $this->assertSame('212620000001', WhatsApp::normalize($records[$absentWithGuardian->id]->notifyPhone()['phone']));

        // No guardian number: the student's own, flagged as such.
        $this->assertFalse($records[$lateWithoutGuardian->id]->notifyPhone()['isGuardian']);
        $this->assertSame('212630000002', WhatsApp::normalize($records[$lateWithoutGuardian->id]->notifyPhone()['phone']));

        // Neither is usable: no link at all, so the button is the disabled one.
        $this->assertNull(WhatsApp::link($records[$absentWithoutAnyPhone->id]->notifyPhone()['phone'], 'x'));
    }

    public function test_the_panel_is_empty_before_the_sheet_is_saved(): void
    {
        $student = $this->student('أحمد', ['guardian_phone' => '0620000001']);

        Livewire::test(AttendanceIndex::class)
            ->set('groupId', (string) $this->group->id)
            ->set('date', today()->toDateString())
            ->call('setState', $student->id, 'غائب')
            ->assertViewHas('toNotify', fn ($rows) => $rows->isEmpty());
    }

    public function test_a_past_date_that_was_already_saved_still_offers_the_buttons(): void
    {
        $student = $this->student('أحمد', ['guardian_phone' => '0620000001']);

        AttendanceRecord::create([
            'tenant_id' => $this->tenant->id, 'student_id' => $student->id,
            'group_id' => $this->group->id, 'date' => today()->subDays(10)->toDateString(), 'state' => 'غائب',
        ]);

        Livewire::withQueryParams(['group' => $this->group->id, 'date' => today()->subDays(10)->toDateString()])
            ->test(AttendanceIndex::class)
            ->assertViewHas('toNotify', fn ($rows) => $rows->count() === 1);
    }

    // ── 3. the message ──────────────────────────────────────────────────────

    public function test_the_default_message_fills_every_placeholder_and_follows_the_gender(): void
    {
        $boy = $this->student('أحمد بناني', ['gender' => 'male', 'guardian_phone' => '0620000001']);
        $girl = $this->student('سارة العلوي', ['gender' => 'female', 'guardian_phone' => '0620000002']);
        $unknown = $this->student('طالب بلا جنس', ['gender' => null, 'guardian_phone' => '0620000003']);

        $make = fn (Student $s) => AttendanceRecord::create([
            'tenant_id' => $this->tenant->id, 'student_id' => $s->id, 'group_id' => $this->group->id,
            'date' => '2026-10-07', 'state' => 'غائب',
        ])->load(['student', 'group.course', 'group.scheduleSlots']);

        $boyText = AbsenceMessage::for($make($boy), $this->tenant);

        $this->assertStringContainsString('أحمد بناني', $boyText);
        $this->assertStringContainsString('ابنكم', $boyText);
        $this->assertStringContainsString('كان غائباً', $boyText);
        $this->assertStringContainsString('English A1 - A', $boyText);
        $this->assertStringContainsString('07/10/2026', $boyText, 'day first, the way a date is read in Morocco');
        $this->assertStringContainsString('الأربعاء', $boyText, 'the weekday of that date');
        $this->assertStringContainsString('مركز النور', $boyText);
        $this->assertStringContainsString('0522334455', $boyText);
        $this->assertStringNotContainsString('{', $boyText, 'no placeholder left behind');

        $girlText = AbsenceMessage::for($make($girl), $this->tenant);
        $this->assertStringContainsString('ابنتكم', $girlText);
        $this->assertStringContainsString('كانت غائبة', $girlText);

        $unknownText = AbsenceMessage::for($make($unknown), $this->tenant);
        $this->assertStringContainsString('ابنكم/ابنتكم', $unknownText);
    }

    public function test_the_late_message_uses_the_late_wording(): void
    {
        $girl = $this->student('سارة', ['gender' => 'female', 'guardian_phone' => '0620000002']);

        $record = AttendanceRecord::create([
            'tenant_id' => $this->tenant->id, 'student_id' => $girl->id, 'group_id' => $this->group->id,
            'date' => '2026-10-07', 'state' => 'متأخر',
        ])->load(['student', 'group.course', 'group.scheduleSlots']);

        $text = AbsenceMessage::for($record, $this->tenant);

        $this->assertStringContainsString('تأخرت', $text);
        $this->assertStringNotContainsString('غائبة', $text);
    }

    public function test_a_custom_template_from_settings_is_used_as_written(): void
    {
        $this->tenant->settings = array_merge($this->tenant->settings ?? [], [
            'absence_message' => 'Bonjour, {student} a manqué {group} le {date}. {center}',
        ]);
        $this->tenant->save();

        $student = $this->student('سارة', ['gender' => 'female', 'guardian_phone' => '0620000002']);

        $record = AttendanceRecord::create([
            'tenant_id' => $this->tenant->id, 'student_id' => $student->id, 'group_id' => $this->group->id,
            'date' => '2026-10-07', 'state' => 'غائب',
        ])->load(['student', 'group.course', 'group.scheduleSlots']);

        $text = AbsenceMessage::for($record, $this->tenant->fresh());

        $this->assertSame('Bonjour, سارة a manqué English A1 - A le 07/10/2026. مركز النور', $text);
        $this->assertStringNotContainsString('ابنتكم', $text, 'a custom sentence is never rewritten');
    }

    public function test_settings_stores_a_custom_template_and_resets_to_the_default(): void
    {
        Livewire::test(CenterProfile::class)
            ->set('absence_message', 'نص المركز الخاص {student}')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('نص المركز الخاص {student}', $this->tenant->fresh()->setting('absence_message'));

        Livewire::test(CenterProfile::class)
            ->call('resetTemplate', 'غائب')
            ->assertSet('absence_message', AbsenceMessage::defaultTemplate('غائب'))
            ->call('save');

        $this->assertNull(
            $this->tenant->fresh()->setting('absence_message'),
            'back to the built-in text, so later wording improvements still reach this center'
        );
    }

    public function test_the_settings_preview_reads_exactly_like_the_real_message(): void
    {
        $student = $this->student('سارة العلوي', ['gender' => 'female', 'guardian_phone' => '0620000002']);

        $record = AttendanceRecord::create([
            'tenant_id' => $this->tenant->id, 'student_id' => $student->id, 'group_id' => $this->group->id,
            'date' => today()->toDateString(), 'state' => 'غائب',
        ])->load(['student', 'group.course', 'group.scheduleSlots']);

        $real = AbsenceMessage::for($record, $this->tenant);

        $preview = Livewire::test(CenterProfile::class)->viewData('absencePreview');

        // Same sentence, same date format, same contact clause — only the
        // sample student, group and class time differ.
        $this->assertStringContainsString('مركز النور', $preview);
        $this->assertStringContainsString('0522334455', $preview);
        $this->assertStringContainsString(today()->format('d/m/Y'), $preview);
        $this->assertStringContainsString(today()->format('d/m/Y'), $real);
        $this->assertStringContainsString('المرجو التواصل مع مركز النور على الرقم 0522334455', $preview);
        $this->assertStringContainsString('المرجو التواصل مع مركز النور على الرقم 0522334455', $real);
    }

    public function test_without_a_center_phone_both_the_message_and_the_preview_drop_the_phone_clause(): void
    {
        $this->tenant->settings = array_merge($this->tenant->settings ?? [], ['phone' => null]);
        $this->tenant->save();

        $student = $this->student('سارة', ['gender' => 'female', 'guardian_phone' => '0620000002']);

        $record = AttendanceRecord::create([
            'tenant_id' => $this->tenant->id, 'student_id' => $student->id, 'group_id' => $this->group->id,
            'date' => today()->toDateString(), 'state' => 'غائب',
        ])->load(['student', 'group.course', 'group.scheduleSlots']);

        $real = AbsenceMessage::for($record, $this->tenant->fresh());

        $this->assertStringContainsString('المرجو التواصل مع مركز النور. شكراً.', $real);
        $this->assertStringNotContainsString('على الرقم', $real);
        $this->assertStringNotContainsString(' . ', $real, 'no dangling punctuation where the number was');

        $component = Livewire::test(CenterProfile::class);

        $this->assertStringNotContainsString('على الرقم', $component->viewData('absencePreview'));
        $this->assertStringNotContainsString('على الرقم', $component->viewData('latePreview'));
        $this->assertFalse($component->viewData('hasPhone'), 'the hint under the preview is shown');
        $component->assertSee('أضف رقم هاتف المركز ليظهر في الرسالة', false);

        // Typing a phone brings the clause back in the untouched default, and
        // saving it still counts as "not customised".
        $component->set('phone', '0522111222');

        $this->assertTrue($component->viewData('hasPhone'), 'the hint is gone once a phone is entered');
        $this->assertStringContainsString('على الرقم 0522111222', $component->viewData('absencePreview'));

        $component->call('save');

        $this->assertNull($this->tenant->fresh()->setting('absence_message'));
    }

    // ── 4. tracking ─────────────────────────────────────────────────────────

    public function test_notifying_stamps_who_and_when_and_a_state_change_clears_it(): void
    {
        $student = $this->student('أحمد', ['guardian_phone' => '0620000001']);

        Livewire::test(AttendanceIndex::class)
            ->set('groupId', (string) $this->group->id)
            ->set('date', today()->toDateString())
            ->call('setState', $student->id, 'غائب')
            ->call('save');

        $record = AttendanceRecord::firstOrFail();
        $this->assertNull($record->notified_at);

        Livewire::test(NotifyButton::class, ['record' => $record])
            ->call('markNotified');

        $record->refresh();
        $this->assertNotNull($record->notified_at);
        $this->assertSame($this->owner->id, $record->notified_by);

        // The counter follows.
        Livewire::test(AttendanceIndex::class)
            ->set('groupId', (string) $this->group->id)
            ->set('date', today()->toDateString())
            ->assertViewHas('notifiedCount', 1);

        // Correcting the state makes the old notification stale.
        Livewire::test(AttendanceIndex::class)
            ->set('groupId', (string) $this->group->id)
            ->set('date', today()->toDateString())
            ->call('setState', $student->id, 'متأخر')
            ->call('save');

        $record->refresh();
        $this->assertNull($record->notified_at);
        $this->assertNull($record->notified_by);
    }

    public function test_saving_the_same_state_again_keeps_the_notification(): void
    {
        $student = $this->student('أحمد', ['guardian_phone' => '0620000001']);

        $record = AttendanceRecord::create([
            'tenant_id' => $this->tenant->id, 'student_id' => $student->id, 'group_id' => $this->group->id,
            'date' => today()->toDateString(), 'state' => 'غائب',
            'notified_at' => now()->subHour(), 'notified_by' => $this->owner->id,
        ]);

        Livewire::test(AttendanceIndex::class)
            ->set('groupId', (string) $this->group->id)
            ->set('date', today()->toDateString())
            ->call('setState', $student->id, 'غائب')
            ->call('save');

        $this->assertNotNull($record->fresh()->notified_at);
    }

    // ── 5. dashboard ────────────────────────────────────────────────────────

    public function test_the_dashboard_counts_todays_unnotified_absences_only(): void
    {
        $a = $this->student('أ', ['guardian_phone' => '0620000001']);
        $b = $this->student('ب', ['guardian_phone' => '0620000002']);
        $c = $this->student('ج', ['guardian_phone' => '0620000003']);
        $d = $this->student('د', ['guardian_phone' => '0620000004']);

        $row = fn (Student $s, string $state, string $date, ?string $notified = null) => AttendanceRecord::create([
            'tenant_id' => $this->tenant->id, 'student_id' => $s->id, 'group_id' => $this->group->id,
            'date' => $date, 'state' => $state, 'notified_at' => $notified,
        ]);

        $row($a, 'غائب', today()->toDateString());
        $row($b, 'متأخر', today()->toDateString());
        $row($c, 'غائب', today()->toDateString(), now()->toDateTimeString());  // already done
        $row($d, 'حاضر', today()->toDateString());                              // nothing to say
        $row($a, 'غائب', today()->subDay()->toDateString());                    // yesterday

        $this->get('/dashboard')
            ->assertOk()
            ->assertViewHas('unnotified', fn ($u) => $u['count'] === 2 && $u['group_id'] === $this->group->id)
            ->assertSee('غيابات اليوم غير المُبلَّغ عنها', false);
    }

    public function test_the_dashboard_card_is_hidden_when_nothing_is_pending(): void
    {
        $this->get('/dashboard')
            ->assertOk()
            ->assertViewHas('unnotified', fn ($u) => $u['count'] === 0)
            ->assertDontSee('غيابات اليوم غير المُبلَّغ عنها', false);
    }

    // ── 6. who may do it ────────────────────────────────────────────────────

    public function test_a_user_without_manage_attendance_cannot_notify(): void
    {
        $student = $this->student('أحمد', ['guardian_phone' => '0620000001']);

        $record = AttendanceRecord::create([
            'tenant_id' => $this->tenant->id, 'student_id' => $student->id, 'group_id' => $this->group->id,
            'date' => today()->toDateString(), 'state' => 'غائب',
        ]);

        $accountant = User::create([
            'tenant_id' => $this->tenant->id, 'name' => 'محاسب', 'email' => 'acc@wa.test',
            'password' => 'secret1234', 'status' => 'نشط', 'email_verified_at' => now(),
        ]);
        $accountant->assignRole(Permissions::ACCOUNTANT_ROLE);

        Auth::login($accountant);

        Livewire::test(NotifyButton::class, ['record' => $record->id])
            ->assertViewHas('canNotify', false)
            ->call('markNotified')
            ->assertForbidden();

        $this->assertNull($record->fresh()->notified_at);
    }

    public function test_another_centers_record_is_a_404(): void
    {
        ['tenant' => $other, 'owner' => $otherOwner] = app(CenterProvisioner::class)
            ->provision('مركز آخر', 'مدير آخر', 'other@wa.test', 'secret1234');

        $student = $this->student('أحمد', ['guardian_phone' => '0620000001']);
        $record = AttendanceRecord::create([
            'tenant_id' => $this->tenant->id, 'student_id' => $student->id, 'group_id' => $this->group->id,
            'date' => today()->toDateString(), 'state' => 'غائب',
        ]);

        Auth::login($otherOwner);

        // TenantScope hides the row, so the button cannot even be built: the
        // lookup throws ModelNotFoundException, which Laravel renders as 404.
        // Nothing about the other center — not a name, not a phone number —
        // reaches the page or a wa.me link.
        try {
            Livewire::test(NotifyButton::class, ['record' => $record->id])->call('markNotified');
            $this->fail('another center managed to open the notify button');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->assertSame(404, (new \Illuminate\Foundation\Exceptions\Handler(app()))
                ->render(request(), $e)->getStatusCode());
        }

        $this->assertNull($record->fresh()->notified_at);
    }

    // ── 7. the migration ────────────────────────────────────────────────────

    public function test_adding_the_columns_leaves_existing_attendance_rows_untouched(): void
    {
        $student = $this->student('أحمد');

        \Schema::table('attendance_records', function ($table) {
            $table->dropConstrainedForeignId('notified_by');
            $table->dropColumn('notified_at');
        });

        $id = \DB::table('attendance_records')->insertGetId([
            'tenant_id' => $this->tenant->id, 'student_id' => $student->id, 'group_id' => $this->group->id,
            'date' => '2026-09-01', 'state' => 'غائب', 'created_at' => now(), 'updated_at' => now(),
        ]);

        (require database_path('migrations/2026_10_10_000003_add_notified_to_attendance_records_table.php'))->up();

        $row = \DB::table('attendance_records')->where('id', $id)->first();

        $this->assertNull($row->notified_at);
        $this->assertNull($row->notified_by);
        $this->assertSame('غائب', $row->state, 'the record itself is unchanged');
        $this->assertSame(1, \DB::table('attendance_records')->count());
    }
}
