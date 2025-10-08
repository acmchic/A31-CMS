@extends(backpack_view('layouts.auth'))

@section('content')
    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="text-center mb-4 display-6 auth-logo-container">
                {!! backpack_theme_config('project_logo') !!}
                <div class="mt-3">
                    <h3 class="text-dark fw-bold">QUÂN CHỦNG PK-KQ</h3>
                    <h4 class="text-dark fw-bold">NHÀ MÁY A31</h4>
                </div>
            </div>
            <div class="card card-md">
                <div class="card-body pt-0">
                    @include(backpack_view('auth.login.inc.form'))
                </div>
            </div>
            {{-- Bỏ phần đăng ký --}}
        </div>
    </div>
@endsection
