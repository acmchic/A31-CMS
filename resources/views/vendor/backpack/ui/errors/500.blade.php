@extends(backpack_view('blank'))

@php
  $default_error_message = 'Đã xảy ra lỗi nội bộ của máy chủ.';
  $error_number = 500;
@endphp

@section('after_styles')
<style>
    .error-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 60vh;
        text-align: center;
        padding: 2rem;
    }
    .error-code {
        font-size: 8rem;
        font-weight: bold;
        color: #e74c3c;
        margin: 0;
        line-height: 1;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }
    .error-title {
        font-size: 2rem;
        color: #2c3e50;
        margin: 1rem 0 0.5rem 0;
        font-weight: 600;
    }
    .error-message {
        font-size: 1.1rem;
        color: #7f8c8d;
        margin: 0 0 2rem 0;
        line-height: 1.6;
        max-width: 500px;
    }
    .back-button {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    .back-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        text-decoration: none;
        color: white;
    }
    .back-button:active {
        transform: translateY(0);
    }
    .icon {
        width: 16px;
        height: 16px;
    }
</style>
@endsection

@section('content')
<div class="error-container">
    <div class="error-code">{{ $error_number }}</div>
    <h1 class="error-title">Lỗi máy chủ</h1>
    <p class="error-message">
        {{ $default_error_message }}<br>
        Chúng tôi đang khắc phục sự cố này. Vui lòng thử lại sau.
    </p>
    <a href="javascript:history.back()" class="back-button">
        <svg class="icon" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
        </svg>
        Quay lại trang trước
    </a>
</div>
@endsection












