<!DOCTYPE html>
<html lang="ar" dir="rtl">

@include('dashboard.partials.head')
@flasher_render

<body>
  @include('dashboard.partials.navbar')
  @include('dashboard.partials.sidebar')
  <div class="app-content content">
      <div class="content-wrapper">
          @yield('content')
      </div>
  </div>
  <!-- BEGIN VENDOR JS-->
  @include('dashboard.partials.scripts')
  @include('dashboard.partials.footer')
  @include('dashboard.partials.delete_modal')
</body>

</html>
