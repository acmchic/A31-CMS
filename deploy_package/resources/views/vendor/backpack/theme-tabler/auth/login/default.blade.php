@extends(backpack_view('layouts.auth'))

@section('content')
<style>
.auth-logo-container img {
    height: 200px !important;
    width: auto !important;
    max-width: 300px;
    object-fit: contain !important;
    display: block !important;
    margin: 0 auto !important;
}
</style>
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
