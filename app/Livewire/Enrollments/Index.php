<?php

namespace App\Livewire\Enrollments;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Notification;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'course', history: true)]
    public string $courseFilter = '';

    #[Url(as: 'status', history: true)]
    public string $statusFilter = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public ?int $confirmingDeleteId = null;

    // Form fields — remaining/status are derived from price, discount and paid.
    // Flow: course → group → student (the student list depends on the course).
    public string $studentSearch = '';

    /** Set when the modal was opened from a student (?student=ID): the student is fixed. */
    public ?int $pinnedStudentId = null;

    public ?int $student_id = null;

    public ?int $course_id = null;

    public ?int $group_id = null;

    public string $date = '';

    public string $due_date = '';

    /** Subscription pack: 1 = the monthly billing every enrollment used before. */
    public int $duration_months = 1;

    public $price = 0;

    public $discount = 0;

    public $paid = 0;

    protected function rules(): array
    {
        $tenantId = auth()->user()->tenant_id;

        return [
            'course_id' => ['required', Rule::exists('courses', 'id')->where('tenant_id', $tenantId)->whereNull('deleted_at')],
            'student_id' => [
                'required',
                Rule::exists('students', 'id')->where('tenant_id', $tenantId)->whereNull('deleted_at'),
                // One enrollment per student per course.
                Rule::unique('enrollments', 'student_id')
                    ->where('tenant_id', $tenantId)
                    ->where('course_id', $this->course_id)
                    ->whereNull('deleted_at')
                    ->ignore($this->editingId),
            ],
            'group_id' => [
                'nullable',
                Rule::exists('groups', 'id')->where('tenant_id', $tenantId)->whereNull('deleted_at')
                    ->when($this->course_id, fn ($rule) => $rule->where('course_id', $this->course_id)),
            ],
            'date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', function ($attribute, $value, $fail) {
                if ($value && $this->date && $value < $this->date) {
                    $fail(__('يجب أن يكون تاريخ الاستحقاق بعد تاريخ التسجيل أو مساوياً له.'));
                }
            }],
            'duration_months' => ['required', 'integer', Rule::in(Enrollment::DURATIONS)],
            'price' => ['required', 'integer', 'min:0'],
            'discount' => ['required', 'integer', 'min:0', 'lte:price'],
            'paid' => ['required', 'integer', 'min:0'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'student_id' => __('الطالب'),
            'course_id' => __('الدورة'),
            'group_id' => __('المجموعة'),
            'date' => __('تاريخ التسجيل'),
            'due_date' => __('تاريخ الاستحقاق'),
            'duration_months' => __('مدة الاشتراك'),
            'price' => __('السعر'),
            'discount' => __('الخصم'),
            'paid' => __('المبلغ المؤدى'),
        ];
    }

    public function mount(): void
    {
        $studentId = request()->integer('student');
        if ($studentId && ($student = Student::find($studentId))) {
            $this->openCreate();
            $this->pinnedStudentId = $student->id;
            $this->student_id = $student->id;

            // Carried over from the student form, which offers the course and the
            // pack but deliberately creates nothing: the enrollment is confirmed
            // here, pre-filled with the price and the end of the period.
            $duration = request()->integer('duration');
            if (in_array($duration, Enrollment::DURATIONS, true)) {
                $this->duration_months = $duration;
            }

            $courseId = request()->integer('course');
            if ($courseId && Course::whereKey($courseId)->exists()) {
                $this->course_id = $courseId;
            }

            $this->applyCoursePricing();
        }
    }

    /**
     * Price and end of period follow the course and the chosen pack. Both stay
     * editable afterwards — a center may agree a one-off price — so this only
     * ever fills the fields while creating, never while editing an existing
     * enrollment whose figures are already agreed.
     */
    protected function applyCoursePricing(): void
    {
        if ($this->editingId) {
            return;
        }

        if ($this->course_id && ($course = Course::find($this->course_id))) {
            $this->price = $course->priceFor($this->duration_months);
        }

        if ($this->date) {
            $this->due_date = Carbon::parse($this->date)->addMonthsNoOverflow($this->duration_months)->toDateString();
        }
    }

    public function updatedDurationMonths(): void
    {
        // A value outside DURATIONS can only come from a tampered select, and
        // it is rejected by the rules rather than silently turned into monthly.
        $this->applyCoursePricing();
    }

    public function unpinStudent(): void
    {
        $this->pinnedStudentId = null;
        $this->student_id = null;
    }

    /** Default the due date to one pack length after the enrollment date while creating. */
    public function updatedDate($value): void
    {
        if (! $this->editingId && $value) {
            $this->due_date = Carbon::parse($value)->addMonthsNoOverflow($this->duration_months)->toDateString();
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCourseFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    /** Course drives the group list, the eligible students and the default price. */
    public function updatedCourseId($value): void
    {
        $course = $value ? Course::find($value) : null;

        if ($this->group_id && (! $course || ! Group::where('id', $this->group_id)->where('course_id', $course->id)->exists())) {
            $this->group_id = null;
        }

        if (! $this->editingId) {
            if (! $this->pinnedStudentId) {
                $this->student_id = null;
            }
            $this->studentSearch = '';
            if ($course) {
                $this->price = $course->priceFor($this->duration_months);
            }
        }
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->date = now()->toDateString();
        $this->due_date = now()->addMonthsNoOverflow($this->duration_months)->toDateString();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $enrollment = Enrollment::findOrFail($id);
        $this->editingId = $enrollment->id;
        $this->student_id = $enrollment->student_id;
        $this->course_id = $enrollment->course_id;
        $this->group_id = $enrollment->group_id;
        $this->date = $enrollment->date->toDateString();
        $this->due_date = $enrollment->due_date?->toDateString() ?? '';
        $this->duration_months = $enrollment->months;
        $this->price = (int) $enrollment->price;
        $this->discount = (int) $enrollment->discount;
        $this->paid = $enrollment->paid;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    protected function resetForm(): void
    {
        $this->reset(['studentSearch', 'pinnedStudentId', 'student_id', 'course_id', 'group_id', 'date', 'due_date', 'duration_months', 'price', 'discount', 'paid']);
    }

    public function save(): void
    {
        $data = $this->validate();

        $attributes = [
            'student_id' => $data['student_id'],
            'course_id' => $data['course_id'],
            'group_id' => $data['group_id'] ?: null,
            'date' => $data['date'],
            'due_date' => $data['due_date'] ?: null,
            'duration_months' => (int) $data['duration_months'],
            'price' => (int) $data['price'],
            'discount' => (int) $data['discount'],
        ] + Enrollment::settle((int) $data['price'], (int) $data['discount'], (int) $data['paid']);

        if ($this->editingId) {
            $enrollment = Enrollment::findOrFail($this->editingId);

            // Settling a period that is due (or overdue) in full moves the
            // deadline forward by one pack length.
            $dueDate = $attributes['due_date'] ? Carbon::parse($attributes['due_date']) : null;
            if ($attributes['status'] === 'مكتمل' && $enrollment->status !== 'مكتمل' && $dueDate && $dueDate->lte(today())) {
                $attributes['due_date'] = $dueDate->addMonthsNoOverflow($attributes['duration_months'])->toDateString();
            }

            $enrollment->update($attributes);
            $this->dispatch('toast', message: __('تم تحديث التسجيل بنجاح'));
        } else {
            $enrollment = Enrollment::create($attributes);
            $enrollment->load(['student', 'course']);
            Notification::notify([
                'title' => 'تم تسجيل طالب جديد',
                'body' => "تم تسجيل {$enrollment->student?->name} في دورة {$enrollment->course?->name}.",
                'icon' => 'user-round-plus',
                'tone' => 'brand',
                'category' => 'التسجيلات',
            ]);
            $this->dispatch('notification-created');
            $this->dispatch('toast', message: __('تم تسجيل الطالب بنجاح'));
        }

        $enrollment->syncStudent();

        $this->closeModal();
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
    }

    public function delete(): void
    {
        if ($this->confirmingDeleteId) {
            Enrollment::findOrFail($this->confirmingDeleteId)->delete();
            $this->dispatch('toast', message: __('تم حذف التسجيل بنجاح'));
        }
        $this->confirmingDeleteId = null;
        $this->resetPage();
    }

    public function render()
    {
        Enrollment::rolloverDue();

        $enrollments = Enrollment::query()
            ->search($this->search)
            ->courseFilter($this->courseFilter ? (int) $this->courseFilter : null)
            ->when($this->statusFilter === 'overdue',
                fn ($q) => $q->overdue(),
                fn ($q) => $q->statusFilter($this->statusFilter))
            ->with(['student', 'course', 'group'])
            ->latest('date')->latest('id')
            ->paginate(12);

        $stats = [
            'total' => Enrollment::count(),
            'this_month' => Enrollment::whereMonth('date', now()->month)->whereYear('date', now()->year)->count(),
            'total_value' => (int) Enrollment::selectRaw('COALESCE(SUM(price - discount), 0) as v')->value('v'),
            'remaining' => (int) Enrollment::sum('remaining'),
            'overdue' => Enrollment::overdue()->count(),
        ];

        $courses = Course::with('teacher')->orderBy('name')->get();

        $groups = $this->course_id
            ? Group::with('teacher')->where('course_id', $this->course_id)->orderBy('name')->get()
            : collect();

        $selectedCourse = $this->course_id ? $courses->firstWhere('id', (int) $this->course_id) : null;
        $selectedGroup = $this->group_id ? $groups->firstWhere('id', (int) $this->group_id) : null;

        // Students become selectable once a course is chosen: everyone not already
        // enrolled in that course (a student may be in several courses), narrowed by
        // the name filter. When editing, the enrollment's own student stays listed.
        $pinnedStudent = $this->pinnedStudentId ? Student::find($this->pinnedStudentId) : null;

        $students = $this->course_id
            ? Student::orderBy('name')
                ->when($this->studentSearch, fn ($q) => $q->where('name', 'like', "%{$this->studentSearch}%"))
                ->where(function ($w) {
                    $w->whereDoesntHave('enrollments', fn ($e) => $e->where('course_id', $this->course_id));
                    if ($this->editingId) {
                        $w->orWhere('id', $this->student_id);
                    }
                })
                ->get(['id', 'name', 'phone'])
            : collect();

        $preview = Enrollment::settle((int) $this->price, (int) $this->discount, (int) $this->paid);

        return view('livewire.enrollments.index', [
            'enrollments' => $enrollments,
            'stats' => $stats,
            'courses' => $courses,
            'students' => $students,
            'groups' => $groups,
            'selectedCourse' => $selectedCourse,
            'selectedGroup' => $selectedGroup,
            'pinnedStudent' => $pinnedStudent,
            'preview' => $preview,
            'statuses' => Enrollment::STATUSES,
            'durations' => Enrollment::DURATIONS,
        ])->extends('layouts.app')->section('content')->title(__('التسجيلات'));
    }
}
