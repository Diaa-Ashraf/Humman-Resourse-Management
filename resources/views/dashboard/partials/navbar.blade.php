<nav class="hr-topbar">
    <div class="hr-topbar-right">
        <button class="hr-menu-toggle" type="button" aria-label="فتح القائمة" data-hr-menu-toggle>
            <i class="la la-bars"></i>
        </button>
        <a class="hr-brand" href="{{ route('dashboard') }}">
            <span class="hr-brand-mark"><i class="la la-leaf"></i></span>
            <div class="hr-brand-text">
                <span class="hr-brand-title">نَبْت</span>
                <small class="hr-brand-sub">نظام الموارد البشرية</small>
            </div>
        </a>
    </div>

    <div class="hr-topbar-left">
        <span class="hr-today">
            <i class="la la-calendar"></i> {{ now()->locale('ar')->translatedFormat('l، j F Y') }}
        </span>

        @auth
            @if(\Illuminate\Support\Facades\Schema::hasTable('notifications'))
                <div class="dropdown hr-notif-dropdown">
                    <button class="hr-topbar-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="الإشعارات">
                        <i class="la la-bell"></i>
                        @if(auth()->user()->unreadNotifications()->exists())
                            <span class="hr-badge-dot"></span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end hr-dropdown-menu">
                        <div class="dropdown-header d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                            <strong class="text-dark">الإشعارات</strong>
                            @if(auth()->user()->unreadNotifications()->exists())
                                <span class="badge bg-danger rounded-pill">{{ auth()->user()->unreadNotifications()->count() }}</span>
                            @endif
                        </div>
                        <div class="hr-notif-list">
                            @forelse(auth()->user()->unreadNotifications()->latest()->limit(5)->get() as $notice)
                                <form method="post" action="{{ route('notifications.read', $notice->id) }}" class="border-bottom">
                                    @csrf
                                    <button class="dropdown-item py-2 text-wrap text-end w-100" type="submit">
                                        <div class="fw-bold small text-dark">{{ $notice->data['title'] ?? 'تنبيه' }}</div>
                                        <small class="text-muted d-block">{{ $notice->data['message'] ?? '' }}</small>
                                    </button>
                                </form>
                            @empty
                                <div class="p-3 text-center text-muted small">لا توجد إشعارات جديدة</div>
                            @endforelse
                        </div>
                        <a class="dropdown-item text-center text-primary small py-2 fw-bold" href="{{ route('notifications.index') }}">
                            عرض كل الإشعارات
                        </a>
                    </div>
                </div>
            @endif
        @endauth

        <div class="dropdown hr-user-dropdown">
            <button class="hr-user-trigger" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="hr-avatar">{{ mb_substr(auth()->user()->name ?? 'م', 0, 1) }}</span>
                <span class="hr-user-info">
                    <span class="hr-user-name">{{ auth()->user()->name ?? 'المستخدم' }}</span>
                    <small class="hr-user-role">
                        {{ auth()->user()?->getRoleNames()->map(fn($role) => \Spatie\Permission\Models\Role::where('name', $role)->value('display_name') ?? $role)->implode('، ') ?: 'فريق الموارد البشرية' }}
                    </small>
                </span>
                <i class="la la-angle-down hr-user-chevron"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end hr-dropdown-menu">
                <div class="px-3 py-2 border-bottom d-md-none text-end">
                    <div class="fw-bold">{{ auth()->user()->name ?? 'المستخدم' }}</div>
                    <small class="text-muted">{{ auth()->user()->email ?? '' }}</small>
                </div>
                <a class="dropdown-item text-end" href="{{ route('profile.edit') }}"><i class="la la-user ms-2"></i> الملف الشخصي</a>
                <a class="dropdown-item text-end" href="{{ route('security.index') }}"><i class="la la-shield ms-2"></i> الأمان والجلسات</a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="dropdown-item text-danger text-end" type="submit"><i class="la la-sign-out ms-2"></i> تسجيل الخروج</button>
                </form>
            </div>
        </div>
    </div>
</nav>
