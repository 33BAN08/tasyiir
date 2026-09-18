<?php

namespace App\Livewire\Enrollments;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Notification;
use App\Models\Student;
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
                    $fail('يجب أن يكون تاريخ الاستحقاق بعد تاريخ التسجيل أو مساوياً له.');
                }
            }],
            'price' => ['required', 'integer', 'min:0'],
            'discount' => ['required', 'integer', 'min:0', 'lte:price'],
            'paid' => ['required', 'integer', 'min:0'],
        ];
    }

    protected array $validationAttributes = [
        'student_id' => 'الطالب',
        'course_id' => 'الدورة',
        'group_id' => 'المجموعة',
        'date' => 'تاريخ التسجيل',
        'due_date' => 'تاريخ الاستحقاق',
        'price' => 'السعر',
        'discount' => 'الخصم',
        'paid' => 'المبلغ المؤدى',
    ];

    public function mount(): void
    {
        $studentId = request()->integer('student');
        if ($studentId && ($student = Student::find($studentId))) {
            $this->openCreate();
            $this->pinnedStudentId = $student->id;
            $this->student_id = $student->id;
        }
    }

    public function unpinStudent(): void
    {
        $this->pinnedStudentId = null;
        $this->student_id = null;
    }

    /** Default the due date to one month after the enrollment date while creating. */
    public function updatedDate($value): void
    {
        if (! $this->editingId && $value) {
            $this->due_date = \Carbon\Carbon::parse($value)->addMonth()->toDateString();
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
                $this->price = (int) $course->price;
            }
        }
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->date = now()->toDateString();
        $this->due_date = now()->addMonth()->toDateString();
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
        $this->reset(['studentSearch', 'pinnedStudentId', 'student_id', 'course_id', 'group_id', 'date', 'due_date', 'price', 'discount', 'paid']);
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
            'price' => (int) $data['price'],
            'discount' => (int) $data['discount'],
        ] + Enrollment::settle((int) $data['price'], (int) $data['discount'], (int) $data['paid']);

        if ($this->editingId) {
            $enrollment = Enrollment::findOrFail($this->editingId);

            // Settling a period that is due (or overdue) in full moves the
            // deadline to the next month.
            $dueDate = $attributes['due_date'] ? \Carbon\Carbon::parse($attributes['due_date']) : null;
            if ($attributes['status'] === 'مكتمل' && $enrollment->status !== 'مكتمل' && $dueDate && $dueDate->lte(today())) {
                $attributes['due_date'] = $dueDate->addMonth()->toDateString();
            }

            $enrollment->update($attributes);
            $this->dispatch('toast', message: 'تم تحديث التسجيل بنجاح');
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
            $this->dispatch('toast', message: 'تم تسجيل الطالب بنجاح');
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
            $this->dispatch('toast', message: 'تم حذف التسجيل بنجاح');
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
        ])->extends('layouts.app')->section('content')->title('التسجيلات');
    }
}
