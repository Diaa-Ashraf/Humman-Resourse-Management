<div class="main-menu menu-fixed menu-dark menu-accordion menu-shadow" data-scroll-to-active="true" style="font-family: 'Tajawal', sans-serif;">
    <div class="main-menu-content">
        <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">

            <!-- Dashboard -->
            <li class="nav-item">
                <a href="{{ route('dashboard') }}">
                    <i class="la la-home" style="color:#2ecc71;"></i>
                    <span class="menu-title">الرئيسية</span>
                </a>
            </li>

            <!-- Users -->
            {{-- @can('show_users') --}}
            <li class="nav-item">
                <a href="#">
                    <i class="ft-users" style="color:#9b59b6;"></i>
                    <span class="menu-title">المستخدمين</span>
                </a>
                <ul class="menu-content">
                     <li><a class="menu-item" href="{{ route('users.index') }}">عرض المستخدمين</a></li> 
                     <li><a class="menu-item" href="{{ route('users.create') }}">إضافة مستخدم</a></li> 
                </ul>
            </li>

            <!-- Sections -->
            @can('show_sections')
            <li class="nav-item">
                <a href="#">
                    <i class="ft-layers" style="color:#0d6efd;"></i>
                    <span class="menu-title">الأقسام</span>
                </a>
                <ul class="menu-content">
                    <li><a class="menu-item" href="{{ route('sections.index') }}">عرض الأقسام</a></li>
                    @can('store_sections') <li><a class="menu-item" href="{{ route('sections.create') }}">إضافة قسم</a></li> @endcan
                </ul>
            </li>
            @endcan

            <!-- Shifts -->
            @can('show_shifts')
            <li class="nav-item">
                <a href="#">
                    <i class="la la-clock-o" style="color:#f39c12;"></i>
                    <span class="menu-title">الورديات</span>
                </a>
                <ul class="menu-content">
                    <li><a class="menu-item" href="{{ route('shifts.index') }}">عرض الورديات</a></li>
                    @can('store_shifts') <li><a class="menu-item" href="{{ route('shifts.create') }}">إضافة وردية</a></li> @endcan
                </ul>
            </li>
            @endcan

            <!-- Employees -->
            @can('show_employees')
            <li class="nav-item">
                <a href="#">
                    <i class="ft-user-check" style="color:#27ae60;"></i>
                    <span class="menu-title">الموظفون</span>
                </a>
                <ul class="menu-content">
                    <li><a class="menu-item" href="{{ route('employees.index') }}">كل الموظفين</a></li>
                    @can('store_employees') <li><a class="menu-item" href="{{ route('employees.create') }}">إضافة موظف</a></li> @endcan
                </ul>
            </li>
            @endcan

            <!-- Attendance -->
            {{-- @can('show_attendance') --}}
            <li class="nav-item">
                <a href="#">
                    <i class="la la-calendar-check-o" style="color:#f1c40f;"></i>
                    <span class="menu-title">الحضور والانصراف</span>
                </a>
                <ul class="menu-content">
                    <li><a class="menu-item" href="{{ url('/dashboard/attendance') }}">سجل الحضور</a></li>
                </ul>
            </li>
            {{-- @endcan --}}

            <!-- Bonuses -->
            @can('show_bonuses')
            <li class="nav-item">
                <a href="#">
                    <i class="la la-plus-circle" style="color:#8e44ad;"></i>
                    <span class="menu-title">البدلات</span>
                </a>
                <ul class="menu-content">
                    <li><a class="menu-item" href="{{ route('bonuses.index') }}">عرض البدلات</a></li>
                    @can('store_bonuses') <li><a class="menu-item" href="{{ route('bonuses.create') }}">إضافة بدل</a></li> @endcan
                </ul>
            </li>
            @endcan

            <!-- Salaries -->
            @can('show_salaries')
            <li class="nav-item">
                <a href="#">
                    <i class="la la-money" style="color:#16a085;"></i>
                    <span class="menu-title">الرواتب</span>
                </a>
                <ul class="menu-content">
                    <li><a class="menu-item" href="{{ route('salaries.index') }}">عرض الرواتب</a></li>
                    @can('store_salaries') <li><a class="menu-item" href="{{ route('salaries.create') }}">إضافة راتب</a></li> @endcan
                </ul>
            </li>
            @endcan

            <!-- Leaves -->
            @can('show_leaves')
            <li class="nav-item">
                <a href="#">
                    <i class="la la-plane" style="color:#2980b9;"></i>
                    <span class="menu-title">الإجازات</span>
                </a>
                <ul class="menu-content">
                    <li><a class="menu-item" href="{{ route('leaves.index') }}">عرض الإجازات</a></li>
                    <li><a class="menu-item" href="{{ route('leaves.create') }}">طلب إجازة</a></li>
                </ul>
            </li>
            @endcan

            <!-- Leave Types -->
            @can('show_leave_types')
            <li class="nav-item">
                <a href="#">
                    <i class="la la-list-alt" style="color:#c0392b;"></i>
                    <span class="menu-title">أنواع الإجازات</span>
                </a>
                <ul class="menu-content">
                    <li><a class="menu-item" href="{{ route('leave-types.index') }}">عرض الأنواع</a></li>
                    @can('store_leave_types') <li><a class="menu-item" href="{{ route('leave-types.create') }}">إضافة نوع</a></li> @endcan
                </ul>
            </li>
            @endcan

            <!-- Customers -->
            @can('show_customers')
            <li class="nav-item">
                <a href="#">
<i class="la la-users"></i>
                     <span class="menu-title">العملاء</span>
                </a>
                <ul class="menu-content">
                    <li><a class="menu-item" href="{{ route('customers.index') }}">عرض العملاء</a></li>
                    @can('store_customers') <li><a class="menu-item" href="{{ route('customers.create') }}">إضافة عميل</a></li> @endcan
                </ul>
            </li>
            @endcan

            <!-- Deductions -->
            @can('show_deductions')
            <li class="nav-item">
                <a href="#">
                    <i class="la la-minus-circle" style="color:#e74c3c;"></i>
                    <span class="menu-title">الخصومات</span>
                </a>
                <ul class="menu-content">
                    <li><a class="menu-item" href="{{ route('deductions.index') }}">عرض الخصومات</a></li>
                    @can('store_deductions') <li><a class="menu-item" href="{{ route('deductions.create') }}">إضافة خصم</a></li> @endcan
                </ul>
            </li>
            @endcan

            <!-- Industries -->
            @can('show_industries')
            <li class="nav-item">
                <a href="#">
                    <i class="la la-industry" style="color:#6f42c1;"></i>
                    <span class="menu-title">التخصصات</span>
                </a>
                <ul class="menu-content">
                    <li><a class="menu-item" href="{{ route('industry.index') }}">عرض التخصص</a></li>
                    @can('store_industries') <li><a class="menu-item" href="{{ route('industry.create') }}">إضافة تخصص</a></li> @endcan
                </ul>
            </li>
            @endcan

            <!-- Leads -->
            @can('show_leads')
            <li class="nav-item">
                <a href="#">
<i class="la la-user-plus"></i>
                    <span class="menu-title">العملاء المقترحون</span>
                </a>
                <ul class="menu-content">
                    <li><a class="menu-item" href="{{ route('leads.index') }}">عرض العملاء</a></li>
                    @can('store_leads') <li><a class="menu-item" href="{{ route('leads.create') }}">إضافة عميل مقترح</a></li> @endcan
                </ul>
            </li>
            @endcan

            <!-- Visits -->
            @can('show_visits')
            <li class="nav-item">
                <a href="#">
<i class="la la-map-marker"></i>
                    <span class="menu-title">الزيارات</span>
                </a>
                <ul class="menu-content">
                    <li><a class="menu-item" href="{{ route('visits.index') }}">عرض الزيارات</a></li>
                    @can('store_visits') <li><a class="menu-item" href="{{ route('visits.create') }}">إضافة زياره</a></li> @endcan
                </ul>
            </li>
            @endcan

            <!-- Reports -->
            @can('show_reports')
            <li class="nav-item">
                <a href="{{ route('reports.index') }}">
                    <i class="la la-file-text" style="color:#3498db;"></i>
                    <span class="menu-title">تقارير الموارد البشرية</span>
                </a>
            </li>
            @endcan

        </ul>
    </div>
</div>
