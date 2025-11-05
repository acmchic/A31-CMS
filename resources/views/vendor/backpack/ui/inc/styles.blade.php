@basset(asset('vendor/animate.css/animate.compat.css'))
@basset(asset('vendor/noty/noty.css'))

@basset(asset('vendor/line-awesome/css/line-awesome.min.css'))
@basset(asset('vendor/line-awesome/fonts/la-regular-400.woff2'))
@basset(asset('vendor/line-awesome/fonts/la-solid-900.woff2'))
@basset(asset('vendor/line-awesome/fonts/la-brands-400.woff2'))
{{-- Chỉ cần woff2 cho modern browsers --}}

@basset(base_path('vendor/backpack/crud/src/resources/assets/css/common.css'))

@if (backpack_theme_config('styles') && count(backpack_theme_config('styles')))
    @foreach (backpack_theme_config('styles') as $path)
        @if(is_array($path))
            @basset(...$path)
        @else
            @basset($path)
        @endif
    @endforeach
@endif

@if (backpack_theme_config('mix_styles') && count(backpack_theme_config('mix_styles')))
    @foreach (backpack_theme_config('mix_styles') as $path => $manifest)
        <link rel="stylesheet" type="text/css" href="{{ mix($path, $manifest) }}">
    @endforeach
@endif

@if (backpack_theme_config('vite_styles') && count(backpack_theme_config('vite_styles')))
    @vite(backpack_theme_config('vite_styles'))
@endif

{{-- Custom styles được load sau tất cả CSS để override --}}
@includeIf('vendor.backpack.ui.inc.header_styles')
