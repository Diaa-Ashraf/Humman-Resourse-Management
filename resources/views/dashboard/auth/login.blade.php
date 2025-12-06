@extends('dashboard.auth.master')
@section('title' , 'login')
@section('content')
<body class="vertical-layout vertical-menu-modern 1-column bg-cyan bg-lighten-2 menu-expanded fixed-navbar"
  data-open="click" data-menu="vertical-menu-modern" data-col="1-column">
  <!-- fixed-top-->
  <nav class="header-navbar navbar-expand-md navbar navbar-with-menu navbar-without-dd-arrow fixed-top navbar-dark navbar-shadow">
    <div class="navbar-wrapper" style="display: flex; justify-content: space-between; width: 100%;">
      <div class="navbar-header">
        <ul class="nav navbar-nav flex-row">
          <li class="nav-item mobile-menu d-md-none mr-auto">
            <a class="nav-link nav-menu-main menu-toggle hidden-xs" href="#">
              <i class="ft-menu font-large-1"></i>
            </a>
          </li>
          <li class="nav-item">
            <a class="navbar-brand" href="index.html">
              {{-- <img class="brand-logo" alt="modern admin logo" src="{{ asset('assets/assets-back')}}/images/logo/logo.png"> --}}
              <h3 class="brand-text">senerm group</h3>
            </a>
          </li>
          <li class="nav-item d-md-none">
            <a class="nav-link open-navbar-container" data-toggle="collapse" data-target="#navbar-mobile">
              <i class="la la-ellipsis-v"></i>
            </a>
          </li>
        </ul>
      </div>

     
      <!-- Language Switcher with right margin -->
    </div>
  </nav>
</body>
  <!-- ////////////////////////////////////////////////////////////////////////////-->
  <div class="app-content content">
    <div class="content-wrapper">
      <div class="content-header row">
      </div>
      <div class="content-body">
        <section class="flexbox-container">
          <div class="col-12 d-flex align-items-center justify-content-center">
            <div class="col-md-4 col-10 box-shadow-2 p-0">
              <div class="card border-grey border-lighten-3 m-0">
                <div class="card-header border-0">
                  <div class="card-title text-center">
                    {{-- <img src="{{ asset('assets/assets-back')}}/images/logo/logo-dark.png" alt="branding logo"> --}}
                  </div>
                  <p class="card-subtitle line-on-side text-muted text-center font-small-3 pt-2">
                    <span>{{ __('تسجيل الدخول') }}</span>
                  </p>
                </div>
                <div class="card-content">
                  <div class="text-center">
                    <a href="#" class="btn btn-social-icon mr-1 mb-1 btn-outline-facebook">
                      <span class="la la-facebook"></span>
                    </a>
                    <a href="#" class="btn btn-social-icon mr-1 mb-1 btn-outline-twitter">
                      <span class="la la-twitter"></span>
                    </a>
                    <a href="#" class="btn btn-social-icon mr-1 mb-1 btn-outline-linkedin">
                      <span class="la la-linkedin font-medium-4"></span>
                    </a>
                    <a href="#" class="btn btn-social-icon mr-1 mb-1 btn-outline-github">
                      <span class="la la-github font-medium-4"></span>
                    </a>
                  </div>
                  <p class="card-subtitle line-on-side text-muted text-center font-small-3 mx-2 my-2">
                    <span>{{ __('استخدم ايميلك للدخول') }}</span>
                  </p>
                  <div class="card-body pt-0">
                    <form class="form-horizontal" action="{{ route('login') }}" method="POST">
                      @csrf
                        <fieldset class="form-group position-relative has-icon-left">
                        <input type="text" name="email" class="form-control input-lg" id="user-name" placeholder="{{ __('ادخل الايميل') }}"
                        required>
                        @error('email')
                            <strong class="text-danger">{{ $message }}</strong>
                        @enderror
                        <div class="form-control-position">
                          <i class="la la-user"></i>
                        </div>
                      </fieldset>
                      <fieldset class="form-group position-relative has-icon-left">
                        <input type="password" name="password" class="form-control input-lg" id="user-password" placeholder="{{ __('ادخل الباسورد') }}" required>
                        @error('password')
                            <strong class="text-danger">{{ $message }}</strong>
                        @enderror
                        <div class="form-control-position">
                          <i class="la la-key"></i>
                        </div>
                      </fieldset>
                      <fieldset>
                            <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                            <span class="ms-2 text-sm text-gray-600">{{ __('تذكرني') }}</span><br/><br/>
                      </fieldset>

                      @error('g-recaptcha-response')
                        <strong class="text-danger">{{ $message }}</strong>
                      @enderror
                      <br/>
                      <button type="submit" class="btn btn-info btn-lg btn-block"><i class="ft-unlock"></i> {{ __('تسجيل') }}</button>
                    </form>
                  </div>
                    {{-- <p class="text-center"><a href="{{ route('admin.password.email') }}" class="card-link">{{ __('auth.forget_password') }}</a></p> --}}
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
@endsection
