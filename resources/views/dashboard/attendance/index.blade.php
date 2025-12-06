@extends('dashboard.master')

@section('content')
<div class="content-wrapper py-4 ps-4" dir="rtl">    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">إدارة الحضور والانصراف</h2>
            <p class="text-muted mb-0">تتبع حضور الموظفين بسهولة</p>
        </div>
        <div>
            <button class="btn btn-primary btn-lg shadow-sm me-2" data-bs-toggle="modal" data-bs-target="#checkInModal">
                <i class="fas fa-sign-in-alt me-2"></i>تسجيل حضور
            </button>
            <button class="btn btn-success btn-lg shadow-sm" data-bs-toggle="modal" data-bs-target="#checkOutModal">
                <i class="fas fa-sign-out-alt me-2"></i>تسجيل انصراف
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stats-card stats-success">
                <div class="stats-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stats-info">
                    <h3>{{ $attendances->where('status', 'present')->count() }}</h3>
                    <p>حاضر اليوم</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card stats-danger">
                <div class="stats-icon">
                    <i class="fas fa-user-times"></i>
                </div>
                <div class="stats-info">
                    <h3>{{ $attendances->where('status', 'absent')->count() }}</h3>
                    <p>غائب</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card stats-warning">
                <div class="stats-icon">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <div class="stats-info">
                    <h3>{{ $attendances->where('status', 'leave')->count() }}</h3>
                    <p>في إجازة</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card stats-info">
                <div class="stats-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stats-info">
                    <h3>{{ $employees->count() }}</h3>
                    <p>إجمالي الموظفين</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('attendances.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-calendar text-primary me-1"></i>التاريخ
                    </label>
                    <input type="date" name="date" class="form-control" value="{{ request('date', today()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-filter text-primary me-1"></i>الحالة
                    </label>
                    <select name="status" class="form-select">
                        <option value="">الكل</option>
                        <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>حاضر</option>
                        <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>غائب</option>
                        <option value="leave" {{ request('status') == 'leave' ? 'selected' : '' }}>إجازة</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-building text-primary me-1"></i>القسم
                    </label>
                    <select name="section" class="form-select">
                        <option value="">كل الأقسام</option>
                        @foreach($sections as $section)
                        <option value="{{ $section->id }}" {{ request('section') == $section->id ? 'selected' : '' }}>
                            {{ $section->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>بحث
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="fw-semibold">الموظف</th>
                            <th class="fw-semibold">القسم</th>
                            <th class="fw-semibold">التاريخ</th>
                            <th class="fw-semibold">وقت الحضور</th>
                            <th class="fw-semibold">وقت الانصراف</th>
                            <th class="fw-semibold">الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $att)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle">
                                        {{ strtoupper(substr($att->employee->name, 0, 2)) }}
                                    </div>
                                    <span class="fw-semibold ms-2">{{ $att->employee->name }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="text-muted">
                                    <i class="fas fa-briefcase me-1"></i>{{ $att->employee->section->name ?? '-' }}
                                </span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($att->attendance_date)->format('d/m/Y') }}</td>
                            <td>
                                @if($att->check_in)
                                    <span class="time-badge time-in">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ \Carbon\Carbon::parse($att->check_in)->format('h:i A') }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($att->check_out)
                                    <span class="time-badge time-out">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ \Carbon\Carbon::parse($att->check_out)->format('h:i A') }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($att->status == 'present')
                                    <span class="status-badge status-present">
                                        <i class="fas fa-check-circle me-1"></i>حاضر
                                    </span>
                                @elseif($att->status == 'absent')
                                    <span class="status-badge status-absent">
                                        <i class="fas fa-times-circle me-1"></i>غائب
                                    </span>
                                @else
                                    <span class="status-badge status-leave">
                                        <i class="fas fa-calendar-alt me-1"></i>إجازة
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">لا توجد سجلات حضور</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($attendances->hasPages())
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    عرض {{ $attendances->firstItem() }} إلى {{ $attendances->lastItem() }} من {{ $attendances->total() }} سجل
                </div>
                {{ $attendances->links() }}
            </div>
            @endif
        </div>
    </div>

</div>

<!-- Check In Modal -->
<div class="modal fade" id="checkInModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title">
                    <i class="fas fa-sign-in-alt me-2"></i>تسجيل حضور موظف
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info border-0 mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    سيتم تسجيل الحضور بالوقت الحالي تلقائياً
                </div>
                <form id="checkInForm">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-user me-2 text-primary"></i>اختر الموظف
                        </label>
                        <select id="checkInEmployee" class="form-select form-select-lg" required>
                            <option value="">-- اختر الموظف --</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }} - {{ $emp->section->name ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="current-time-display">
                        <i class="fas fa-clock me-2"></i>
                        <span>الوقت الحالي: </span>
                        <strong id="currentTime"></strong>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>إلغاء
                </button>
                <button type="button" class="btn btn-primary px-4" onclick="submitCheckIn()">
                    <i class="fas fa-check me-2"></i>تسجيل الحضور
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Check Out Modal -->
<div class="modal fade" id="checkOutModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white border-0">
                <h5 class="modal-title">
                    <i class="fas fa-sign-out-alt me-2"></i>تسجيل انصراف موظف
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info border-0 mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    سيتم تسجيل الانصراف بالوقت الحالي تلقائياً
                </div>
                <form id="checkOutForm">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-user me-2 text-success"></i>اختر الموظف
                        </label>
                        <select id="checkOutEmployee" class="form-select form-select-lg" required>
                            <option value="">-- اختر الموظف --</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }} - {{ $emp->section->name ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="current-time-display">
                        <i class="fas fa-clock me-2"></i>
                        <span>الوقت الحالي: </span>
                        <strong id="currentTimeOut"></strong>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>إلغاء
                </button>
                <button type="button" class="btn btn-success px-4" onclick="submitCheckOut()">
                    <i class="fas fa-check me-2"></i>تسجيل الانصراف
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const CSRF_TOKEN = "{{ csrf_token() }}";

    // تحديث الوقت الحالي كل ثانية
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('ar-EG', { 
            hour: '2-digit', 
            minute: '2-digit',
            second: '2-digit',
            hour12: true 
        });
        
        const timeEl = document.getElementById('currentTime');
        const timeOutEl = document.getElementById('currentTimeOut');
        
        if (timeEl) timeEl.textContent = timeString;
        if (timeOutEl) timeOutEl.textContent = timeString;
    }

    // تحديث الوقت عند فتح الـ Modal
    const checkInModal = document.getElementById('checkInModal');
    const checkOutModal = document.getElementById('checkOutModal');

    if (checkInModal) {
        checkInModal.addEventListener('shown.bs.modal', function() {
            updateTime();
            setInterval(updateTime, 1000);
        });
    }

    if (checkOutModal) {
        checkOutModal.addEventListener('shown.bs.modal', function() {
            updateTime();
            setInterval(updateTime, 1000);
        });
    }

    // تسجيل حضور
    function submitCheckIn() {
        const employee_id = document.getElementById('checkInEmployee').value;
        
        if (!employee_id) {
            alert('الرجاء اختيار الموظف');
            return;
        }
        
        const now = new Date();
        const check_in = now.toTimeString().split(' ')[0].substring(0, 5);
        
        fetch('/dashboard/attendance/check-in', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ employee_id, check_in })
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.message.includes('بنجاح')) {
                const modal = bootstrap.Modal.getInstance(checkInModal);
                modal?.hide();
                location.reload();
            }
        })
        .catch(() => alert('حدث خطأ في الاتصال'));
    }

    // تسجيل انصراف
    function submitCheckOut() {
        const employee_id = document.getElementById('checkOutEmployee').value;
        
        if (!employee_id) {
            alert('الرجاء اختيار الموظف');
            return;
        }
        
        const now = new Date();
        const check_out = now.toTimeString().split(' ')[0].substring(0, 5);
        
        fetch('/dashboard/attendance/check-out', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ employee_id, check_out })
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.message.includes('بنجاح')) {
                const modal = bootstrap.Modal.getInstance(checkOutModal);
                modal?.hide();
                location.reload();
            }
        })
        .catch(() => alert('حدث خطأ في الاتصال'));
    }
</script>

@push('styles')
<style>
/* إعدادات أساسية للصفحة */
.container-fluid {
    padding-right: 20px !important;
    padding-left: 320px !important;
    transition: all 0.3s ease;
    min-height: 100vh;
}
/* اجعل الـ content ياخد كل المساحة بعد الـ sidebar الثابت */
.content-wrapper {
    margin-right: 280px !important;     /* عرض الـ sidebar */
    margin-left: 0 !important;
    transition: margin 0.3s ease;
    min-height: 100vh;
    background-color: #f8f9fa;
}

/* لما الـ sidebar يبقى مخفي أو مطوي */
.sidebar-collapsed .content-wrapper,
.sidebar-mini .content-wrapper {
    margin-right: 80px !important; /* عرض الـ sidebar لما يكون مطوي */
}

/* على الموبايل خلي الـ content ياخد 100% */
@media (max-width: 768px) {
    .content-wrapper {
        margin-right: 0 !important;
        padding-left: 15px !important;
        padding-right: 15px !important;
    }
}

/* Stats Cards */
.stats-card { 
    background: white; 
    border-radius: 12px; 
    padding: 1.5rem; 
    display: flex; 
    align-items: center; 
    gap: 1.5rem; 
    box-shadow: 0 2px 8px rgba(0,0,0,0.08); 
    transition: transform 0.2s; 
    margin-bottom: 1rem;
}
.stats-card:hover { 
    transform: translateY(-4px); 
    box-shadow: 0 4px 12px rgba(0,0,0,0.12); 
}
.stats-icon { 
    width: 60px; 
    height: 60px; 
    border-radius: 12px; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    font-size: 1.5rem; 
}
.stats-success .stats-icon { 
    background: rgba(25, 135, 84, 0.1); 
    color: #198754; 
}
.stats-danger .stats-icon { 
    background: rgba(220, 53, 69, 0.1); 
    color: #dc3545; 
}
.stats-warning .stats-icon { 
    background: rgba(255, 193, 7, 0.1); 
    color: #ffc107; 
}
.stats-info .stats-icon { 
    background: rgba(13, 202, 240, 0.1); 
    color: #0dcaf0; 
}
.stats-info h3 { 
    font-size: 2rem; 
    font-weight: 700; 
    margin: 0; 
    color: #2c3e50; 
}
.stats-info p { 
    margin: 0; 
    color: #6c757d; 
    font-size: 0.9rem; 
}
/* Avatar */
.avatar-circle { 
    width: 40px; 
    height: 40px; 
    border-radius: 50%; 
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
    color: white; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    font-weight: 600; 
    font-size: 0.85rem; 
}
/* Time Badges */
.time-badge { 
    display: inline-block; 
    padding: 0.4rem 0.8rem; 
    border-radius: 20px; 
    font-size: 0.85rem; 
    font-weight: 500; 
}
.time-in { 
    background: rgba(25, 135, 84, 0.1); 
    color: #198754; 
    border: 1px solid rgba(25, 135, 84, 0.2); 
}
.time-out { 
    background: rgba(13, 110, 253, 0.1); 
    color: #0d6efd; 
    border: 1px solid rgba(13, 110, 253, 0.2); 
}
/* Status Badges */
.status-badge { 
    display: inline-block; 
    padding: 0.4rem 0.8rem; 
    border-radius: 20px; 
    font-size: 0.85rem; 
    font-weight: 500; 
}
.status-present { 
    background: rgba(25, 135, 84, 0.1); 
    color: #198754; 
    border: 1px solid rgba(25, 135, 84, 0.2); 
}
.status-absent { 
    background: rgba(220, 53, 69, 0.1); 
    color: #dc3545; 
    border: 1px solid rgba(220, 53, 69, 0.2); 
}
.status-leave { 
    background: rgba(255, 193, 7, 0.1); 
    color: #ffc107; 
    border: 1px solid rgba(255, 193, 7, 0.2); 
}

/* تحسين العرض على الشاشات المختلفة */
@media (max-width: 1200px) {
    .container-fluid {
        padding-left: 300px !important;
    }
}

@media (max-width: 992px) {
    .container-fluid {
        padding-left: 280px !important;
    }
}

@media (max-width: 768px) {
    .container-fluid {
        padding-right: 15px !important;
        padding-left: 15px !important;
    }
    
    /* إخفاء الـ sidebar على الشاشات الصغيرة */
    .sidebar {
        transform: translateX(-100%);
    }
    
    .sidebar.active {
        transform: translateX(0);
    }
    
    /* تحسين الجدول */
    .table-responsive {
        font-size: 0.85rem;
    }
    
    .avatar-circle {
        width: 35px;
        height: 35px;
        font-size: 0.8rem;
    }
    
    .stats-card {
        padding: 1rem;
        gap: 1rem;
    }
    
    .stats-icon {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
    }
    
    .stats-info h3 {
        font-size: 1.5rem;
    }
}

@media (max-width: 576px) {
    .container-fluid {
        padding-right: 10px !important;
        padding-left: 10px !important;
    }
    
    .d-flex.justify-content-between.align-items-center.mb-4 {
        flex-direction: column;
        align-items: flex-start !important;
    }
    
    .d-flex.justify-content-between.align-items-center.mb-4 > div:last-child {
        margin-top: 1rem;
        width: 100%;
    }
    
    .btn-lg {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
}
</style>
@endpush
@endsection