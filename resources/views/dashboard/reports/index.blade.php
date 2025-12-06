@extends('dashboard.master')

@section('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('content')
<div class="container-fluid py-4">

    <h2 class="fw-bold mb-4">التقارير الشاملة للموارد البشرية</h2>

    <!-- ====== Filter Form ====== -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">فلاتر التقرير</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('reports.index') }}" method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="from" class="form-control" value="{{ $from->toDateString() }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" name="to" class="form-control" value="{{ $to->toDateString() }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">القسم</label>
                    <select name="section_id" class="form-select">
                        <option value="">الكل</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">الموظف</label>
                    <select name="employee_id" class="form-select">
                        <option value="">الكل</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" @if($employee && $employee->id == $emp->id) selected @endif>
                                {{ $emp->name }} ({{ $emp->section->name ?? 'بدون قسم' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">نوع التقرير</label>
                    <select name="report_type" class="form-select">
                        <option value="summary" @if($reportType == 'summary') selected @endif>ملخص</option>
                        <option value="detailed" @if($reportType == 'detailed') selected @endif>تفصيلي</option>
                        <option value="attendance" @if($reportType == 'attendance') selected @endif>الحضور</option>
                        <option value="performance" @if($reportType == 'performance') selected @endif>الأداء</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">تصفية</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ====== Summary Cards ====== -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card p-3 text-center bg-primary text-white">
                <h6>إجمالي الرواتب</h6>
                <p class="fs-5 fw-bold">{{ number_format($totalSalaries, 2) }} جنيه</p>
                <small>عدد الرواتب: {{ $salaryCount }}</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card p-3 text-center bg-success text-white">
                <h6>إجمالي البونص</h6>
                <p class="fs-5 fw-bold">{{ number_format($totalBonuses, 2) }} جنيه</p>
                <small>عدد البونص: {{ $bonusCount }}</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card p-3 text-center bg-danger text-white">
                <h6>إجمالي الخصومات</h6>
                <p class="fs-5 fw-bold">{{ number_format($totalDeductions, 2) }} جنيه</p>
                <small>عدد الخصومات: {{ $deductionCount }}</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card p-3 text-center bg-info text-white">
                <h6>الصافي</h6>
                <p class="fs-5 fw-bold">{{ number_format($netPayment, 2) }} جنيه</p>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card p-3 text-center bg-warning text-dark">
                <h6>أيام الحضور</h6>
                <p class="fs-5 fw-bold">{{ $presentDays }} / {{ $totalAttendanceDays }}</p>
                <small>الحضور: {{ $presentDays }} غياب: {{ $absentDays }}</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card p-3 text-center bg-secondary text-white">
                <h6>إجازات</h6>
                <p class="fs-5 fw-bold">{{ $totalLeaveDays }} يوم</p>
                <small>إجمالي الإجازات</small>
            </div>
        </div>
    </div>

    <!-- ====== Charts Section ====== -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">مخطط التوزيع المالي</h5>
                </div>
                <div class="card-body">
                    <canvas id="financialChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">إحصائيات الحضور</h5>
                </div>
                <div class="card-body">
                    <canvas id="attendanceChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    @if(isset($sectionStats) && count($sectionStats) > 0)
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">متوسط الرواتب حسب القسم</h5>
                </div>
                <div class="card-body">
                    <canvas id="sectionChart" width="400" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- ====== Detailed Tables ====== -->
    @if($reportType == 'detailed' || $reportType == 'summary')
    
    <!-- Salaries Table -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">تفاصيل الرواتب</h5>
            <span class="badge bg-light text-dark">{{ $salaries->count() }} سجل</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>الموظف</th>
                            <th>القسم</th>
                            <th>المبلغ</th>
                            <th>تاريخ الدفع</th>
                            <th>ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($salaries as $salary)
                            <tr>
                                <td>{{ $salary->id }}</td>
                                <td>{{ $salary->employee->name }}</td>
                                <td>{{ $salary->employee->section->name ?? 'N/A' }}</td>
                                <td class="fw-bold">{{ number_format($salary->amount, 2) }} جنيه</td>
                                <td>{{ \Carbon\Carbon::parse($salary->payment_date)->format('Y-m-d') }}</td>
                                <td>{{ $salary->notes ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bonuses Table -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">تفاصيل المكافآت</h5>
            <span class="badge bg-light text-dark">{{ $bonuses->count() }} سجل</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>الموظف</th>
                            <th>القسم</th>
                            <th>المبلغ</th>
                            <th>السبب</th>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bonuses as $bonus)
                            <tr>
                                <td>{{ $bonus->id }}</td>
                                <td>{{ $bonus->employee->name }}</td>
                                <td>{{ $bonus->employee->section->name ?? 'N/A' }}</td>
                                <td class="fw-bold text-success">{{ number_format($bonus->amount, 2) }} جنيه</td>
                                <td>{{ $bonus->reason }}</td>
                                <td>{{ \Carbon\Carbon::parse($bonus->bonus_date)->format('Y-m-d') }}</td>
                                <td><span class="badge bg-success">{{ $bonus->status }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Deductions Table -->
    <div class="card mb-4">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">تفاصيل الخصومات</h5>
            <span class="badge bg-light text-dark">{{ $deductions->count() }} سجل</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>الموظف</th>
                            <th>القسم</th>
                            <th>المبلغ</th>
                            <th>السبب</th>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deductions as $deduction)
                            <tr>
                                <td>{{ $deduction->id }}</td>
                                <td>{{ $deduction->employee->name }}</td>
                                <td>{{ $deduction->employee->section->name ?? 'N/A' }}</td>
                                <td class="fw-bold text-danger">{{ number_format($deduction->amount, 2) }} جنيه</td>
                                <td>{{ $deduction->reason }}</td>
                                <td>{{ \Carbon\Carbon::parse($deduction->deduction_date)->format('Y-m-d') }}</td>
                                <td><span class="badge bg-warning">{{ $deduction->status }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- ====== Attendance Report ====== -->
    @if($reportType == 'attendance')
    <div class="card mb-4">
        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">تقرير الحضور والانصراف</h5>
            <span class="badge bg-light text-dark">{{ $attendances->count() }} سجل</span>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="alert alert-success">
                        <strong>الحضور:</strong> {{ $presentDays }} يوم
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-danger">
                        <strong>الغياب:</strong> {{ $absentDays }} يوم
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-warning">
                        <strong>التأخير:</strong> {{ $lateDays }} يوم
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-info">
                        <strong>نسبة الحضور:</strong> {{ $totalAttendanceDays > 0 ? round(($presentDays / $totalAttendanceDays) * 100, 2) : 0 }}%
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>الموظف</th>
                            <th>القسم</th>
                            <th>التاريخ</th>
                            <th>وقت الدخول</th>
                            <th>وقت الخروج</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances as $attendance)
                            <tr>
                                <td>{{ $attendance->employee->name }}</td>
                                <td>{{ $attendance->employee->section->name ?? 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('Y-m-d') }}</td>
                                <td>{{ $attendance->check_in ?? '-' }}</td>
                                <td>{{ $attendance->check_out ?? '-' }}</td>
                                <td>
                                    @switch($attendance->status)
                                        @case('present')
                                            <span class="badge bg-success">حاضر</span>
                                            @break
                                        @case('absent')
                                            <span class="badge bg-danger">غائب</span>
                                            @break
                                        @case('late')
                                            <span class="badge bg-warning">متأخر</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $attendance->status }}</span>
                                    @endswitch
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- ====== Performance Report ====== -->
    @if($reportType == 'performance')
    <!-- Employee Performance -->
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">أداء الموظفين</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>الموظف</th>
                            <th>القسم</th>
                            <th>أيام الحضور</th>
                            <th>الراتب الأساسي</th>
                            <th>المكافآت</th>
                            <th>الخصومات</th>
                            <th>إجمالي المستحق</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employeePerformance as $perf)
                            <tr>
                                <td>{{ $perf['name'] }}</td>
                                <td>{{ $perf['section'] }}</td>
                                <td>{{ $perf['attendance_days'] }}</td>
                                <td>{{ number_format($perf['base_salary'], 2) }}</td>
                                <td class="text-success">{{ number_format($perf['bonuses'], 2) }}</td>
                                <td class="text-danger">{{ number_format($perf['deductions'], 2) }}</td>
                                <td class="fw-bold">{{ number_format($perf['total_earnings'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Section Statistics -->
    @if(isset($sectionStats) && count($sectionStats) > 0)
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">إحصائيات الأقسام</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>القسم</th>
                            <th>عدد الموظفين</th>
                            <th>إجمالي الرواتب</th>
                            <th>متوسط الراتب</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sectionStats as $stat)
                            <tr>
                                <td>{{ $stat['name'] }}</td>
                                <td>{{ $stat['employees'] }}</td>
                                <td>{{ number_format($stat['total_salary'], 2) }} جنيه</td>
                                <td>{{ number_format($stat['avg_salary'], 2) }} جنيه</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
    @endif

    <!-- ====== Leaves Report ====== -->
    @if(isset($leaves) && $leaves->count() > 0)
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">تقرير الإجازات</h5>
            <span class="badge bg-light text-dark">{{ $leaves->count() }} سجل</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>الموظف</th>
                            <th>نوع الإجازة</th>
                            <th>من تاريخ</th>
                            <th>إلى تاريخ</th>
                            <th>عدد الأيام</th>
                            <th>السبب</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leaves as $leave)
                            <tr>
                                <td>{{ $leave->employee->name }}</td>
                                <td>{{ $leave->leaveType->name ?? 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('Y-m-d') }}</td>
                                <td>{{ \Carbon\Carbon::parse($leave->end_date)->format('Y-m-d') }}</td>
                                <td>{{ \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1 }}</td>
                                <td>{{ $leave->reason }}</td>
                                <td><span class="badge bg-info">{{ $leave->status }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- ====== Export Buttons ====== -->
    <div class="row mb-4">
        <div class="col-md-12 text-center">
            <button class="btn btn-success me-2" onclick="exportReport('excel')">
                <i class="fas fa-file-excel"></i> تصدير Excel
            </button>
            <button class="btn btn-danger me-2" onclick="exportReport('pdf')">
                <i class="fas fa-file-pdf"></i> تصدير PDF
            </button>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> طباعة
            </button>
        </div>
    </div>

</div>

<script>
// Financial Distribution Chart
const financialCtx = document.getElementById('financialChart').getContext('2d');
const financialChart = new Chart(financialCtx, {
    type: 'doughnut',
    data: {
        labels: ['الرواتب', 'المكافآت', 'الخصومات'],
        datasets: [{
            data: [{{ $totalSalaries }}, {{ $totalBonuses }}, {{ $totalDeductions }}],
            backgroundColor: [
                'rgba(54, 162, 235, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 99, 132, 0.8)'
            ],
            borderColor: [
                'rgba(54, 162, 235, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(255, 99, 132, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    font: {
                        size: 14
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        if (label) {
                            label += ': ';
                        }
                        label += new Intl.NumberFormat('ar-EG', {
                            style: 'currency',
                            currency: 'EGP'
                        }).format(context.parsed);
                        return label;
                    }
                }
            }
        }
    }
});

// Attendance Chart
const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
const attendanceChart = new Chart(attendanceCtx, {
    type: 'bar',
    data: {
        labels: ['حاضر', 'غائب', 'متأخر'],
        datasets: [{
            label: 'عدد الأيام',
            data: [{{ $presentDays }}, {{ $absentDays }}, {{ $lateDays }}],
            backgroundColor: [
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 99, 132, 0.8)',
                'rgba(255, 206, 86, 0.8)'
            ],
            borderColor: [
                'rgba(75, 192, 192, 1)',
                'rgba(255, 99, 132, 1)',
                'rgba(255, 206, 86, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Section Salary Chart
@if(isset($sectionStats) && count($sectionStats) > 0)
const sectionCtx = document.getElementById('sectionChart').getContext('2d');
const sectionChart = new Chart(sectionCtx, {
    type: 'bar',
    data: {
        labels: [
            @foreach($sectionStats as $stat)
                '{{ $stat['name'] }}',
            @endforeach
        ],
        datasets: [{
            label: 'متوسط الراتب',
            data: [
                @foreach($sectionStats as $stat)
                    {{ $stat['avg_salary'] }},
                @endforeach
            ],
            backgroundColor: 'rgba(153, 102, 255, 0.8)',
            borderColor: 'rgba(153, 102, 255, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('ar-EG').format(value) + ' جنيه';
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'متوسط الراتب: ' + new Intl.NumberFormat('ar-EG', {
                            style: 'currency',
                            currency: 'EGP'
                        }).format(context.parsed.y);
                    }
                }
            }
        }
    }
});
@endif

function exportReport(type) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("reports.export") }}';
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);
    
    const typeInput = document.createElement('input');
    typeInput.type = 'hidden';
    typeInput.name = 'type';
    typeInput.value = type;
    form.appendChild(typeInput);
    
    // Add current filters
    const params = new URLSearchParams(window.location.search);
    for(const [key, value] of params) {
        if(key !== '_token') {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
        }
    }
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>
@endsection