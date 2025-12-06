<div class="content-header row">
  <div class="col-12">
    <h2 class="content-header-title mb-0 mb-md-2">لوحة تحكم الموارد البشرية</h2>
    <div class="row breadcrumbs-top">
      <div class="col-12">
        <h5 class="content-header-subtitle mt-1 mb-0">نظرة عامة على إحصائيات الموارد البشرية</h5>
      </div>
    </div>
  </div>
</div>

<div class="content-body">
  <!-- HR Statistics Cards -->
  <div class="row">
    <!-- Employees Statistics -->
    <div class="col-xl-3 col-md-6 col-12">
      <div class="card pull-up">
        <div class="card-content">
          <div class="card-body">
            <div class="media d-flex">
              <div class="media-body text-left">
                <h6 class="text-muted">إجمالي الموظفين</h6>
                <h3 class="text-primary">{{ $totalEmployees }}</h3>
                <small class="text-muted">نشط: {{ $activeEmployees }}</small>
              </div>
              <div class="align-self-center">
                <i class="icon-users success font-large-2 float-right"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-xl-3 col-md-6 col-12">
      <div class="card pull-up">
        <div class="card-content">
          <div class="card-body">
            <div class="media d-flex">
              <div class="media-body text-left">
                <h6 class="text-muted">موظفين جدد</h6>
                <h3 class="text-success">{{ $newEmployeesThisMonth }}</h3>
                <small class="text-muted">هذا الشهر</small>
              </div>
              <div class="align-self-center">
                <i class="icon-user-follow success font-large-2 float-right"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-xl-3 col-md-6 col-12">
      <div class="card pull-up">
        <div class="card-content">
          <div class="card-body">
            <div class="media d-flex">
              <div class="media-body text-left">
                <h6 class="text-muted">الأقسام</h6>
                <h3 class="text-info">{{ $totalDepartments }}</h3>
                <small class="text-muted">نشط: {{ $departmentsWithEmployees }}</small>
              </div>
              <div class="align-self-center">
                <i class="icon-layers info font-large-2 float-right"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-xl-3 col-md-6 col-12">
      <div class="card pull-up">
        <div class="card-content">
          <div class="card-body">
            <div class="media d-flex">
              <div class="media-body text-left">
                <h6 class="text-muted">إجازات معلقة</h6>
                <h3 class="text-warning">{{ $pendingLeaves }}</h3>
                <small class="text-muted">تحتاج موافقة</small>
              </div>
              <div class="align-self-center">
                <i class="icon-clock warning font-large-2 float-right"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Financial Statistics -->
  <div class="row">
    <div class="col-xl-8 col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">ملخص مالي شهري</h4>
          <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
        </div>
        <div class="card-content collapse show">
          <div class="card-body">
            <div class="row">
              <div class="col-md-3 col-6">
                <div class="text-center">
                  <h6 class="text-muted">الرواتب</h6>
                  <h3 class="text-primary">{{ number_format($monthlySalaries, 2) }}</h3>
                  <small class="text-muted">جنيه</small>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="text-center">
                  <h6 class="text-muted">المكافآت</h6>
                  <h3 class="text-success">{{ number_format($monthlyBonuses, 2) }}</h3>
                  <small class="text-muted">جنيه</small>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="text-center">
                  <h6 class="text-muted">الخصومات</h6>
                  <h3 class="text-danger">{{ number_format($monthlyDeductions, 2) }}</h3>
                  <small class="text-muted">جنيه</small>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="text-center">
                  <h6 class="text-muted">الصافي</h6>
                  <h3 class="text-info">{{ number_format($netMonthlyPayment, 2) }}</h3>
                  <small class="text-muted">جنيه</small>
                </div>
              </div>
            </div>
            
            <!-- Financial Chart -->
            <div class="chartjs mt-3">
              <canvas id="financialChart" height="100"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-xl-4 col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">إحصائيات الحضور</h4>
        </div>
        <div class="card-content collapse show">
          <div class="card-body">
            <div class="chartjs">
              <canvas id="attendanceChart" height="200"></canvas>
            </div>
            <div class="text-center mt-2">
              <span class="badge badge-success">حاضر: {{ $presentDays }}</span>
              <span class="badge badge-danger">غائب: {{ $absentDays }}</span>
              <span class="badge badge-warning">متأخر: {{ $lateDays }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Activities -->
  <div class="row">
    <div class="col-xl-4 col-md-6 col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">آخر الرواتب</h4>
          <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
        </div>
        <div class="card-content collapse show">
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover table-sm">
                <thead>
                  <tr>
                    <th>الموظف</th>
                    <th>المبلغ</th>
                    <th>التاريخ</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($recentSalaries as $salary)
                    <tr>
                      <td>{{ $salary->employee->name }}</td>
                      <td class="text-primary">{{ number_format($salary->amount, 2) }}</td>
                      <td>{{ \Carbon\Carbon::parse($salary->payment_date)->format('d/m') }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-xl-4 col-md-6 col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">آخر المكافآت</h4>
          <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
        </div>
        <div class="card-content collapse show">
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover table-sm">
                <thead>
                  <tr>
                    <th>الموظف</th>
                    <th>المبلغ</th>
                    <th>السبب</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($recentBonuses as $bonus)
                    <tr>
                      <td>{{ $bonus->employee->name }}</td>
                      <td class="text-success">{{ number_format($bonus->amount, 2) }}</td>
                      <td>{{ Str::limit($bonus->reason, 15) }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-xl-4 col-md-6 col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">آخر الحضور</h4>
          <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
        </div>
        <div class="card-content collapse show">
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover table-sm">
                <thead>
                  <tr>
                    <th>الموظف</th>
                    <th>التاريخ</th>
                    <th>الحالة</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($recentAttendances as $attendance)
                    <tr>
                      <td>{{ $attendance->employee->name }}</td>
                      <td>{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('d/m') }}</td>
                      <td>
                        @switch($attendance->status)
                          @case('present')
                            <span class="badge badge-success">حاضر</span>
                            @break
                          @case('absent')
                            <span class="badge badge-danger">غائب</span>
                            @break
                          @case('late')
                            <span class="badge badge-warning">متأخر</span>
                            @break
                          @default
                            <span class="badge badge-secondary">{{ $attendance->status }}</span>
                        @endswitch
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Department Statistics -->
  @if(count($departmentStats) > 0)
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">إحصائيات الأقسام</h4>
          <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
        </div>
        <div class="card-content collapse show">
          <div class="card-body">
            <div class="row">
              @foreach($departmentStats as $stat)
                <div class="col-md-3 col-6 mb-3">
                  <div class="card text-center">
                    <div class="card-body">
                      <h6 class="text-muted">{{ $stat['name'] }}</h6>
                      <h4 class="text-primary">{{ $stat['employees'] }}</h4>
                      <small class="text-muted">موظف</small>
                      <hr>
                      <h5 class="text-success">{{ number_format($stat['total_salary'], 2) }}</h5>
                      <small class="text-muted">إجمالي الرواتب</small>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
            
            <!-- Department Chart -->
            <div class="chartjs mt-3">
              <canvas id="departmentChart" height="100"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Financial Chart
const financialCtx = document.getElementById('financialChart').getContext('2d');
new Chart(financialCtx, {
    type: 'bar',
    data: {
        labels: ['الرواتب', 'المكافآت', 'الخصومات', 'الصافي'],
        datasets: [{
            label: 'المبالغ بالجنيه',
            data: [{{ $monthlySalaries }}, {{ $monthlyBonuses }}, {{ $monthlyDeductions }}, {{ $netMonthlyPayment }}],
            backgroundColor: [
                'rgba(54, 162, 235, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 99, 132, 0.8)',
                'rgba(153, 102, 255, 0.8)'
            ],
            borderColor: [
                'rgba(54, 162, 235, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(255, 99, 132, 1)',
                'rgba(153, 102, 255, 1)'
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
                    callback: function(value) {
                        return new Intl.NumberFormat('ar-EG').format(value);
                    }
                }
            }
        }
    }
});

// Attendance Chart
const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
new Chart(attendanceCtx, {
    type: 'doughnut',
    data: {
        labels: ['حاضر', 'غائب', 'متأخر'],
        datasets: [{
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
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Department Chart
@if(count($departmentStats) > 0)
const departmentCtx = document.getElementById('departmentChart').getContext('2d');
new Chart(departmentCtx, {
    type: 'bar',
    data: {
        labels: [
            @foreach($departmentStats as $stat)
                '{{ $stat['name'] }}',
            @endforeach
        ],
        datasets: [{
            label: 'عدد الموظفين',
            data: [
                @foreach($departmentStats as $stat)
                    {{ $stat['employees'] }},
                @endforeach
            ],
            backgroundColor: 'rgba(54, 162, 235, 0.8)',
            borderColor: 'rgba(54, 162, 235, 1)',
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
        }
    }
});
@endif
</script>
