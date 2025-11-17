<?php
/**
 * Script kiểm tra PHP extensions cần thiết cho ký số PDF
 * Truy cập: http://your-domain.com/check_extensions.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kiểm tra PHP Extensions - A31 CMS</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        h1 { color: #333; }
        .check { margin: 10px 0; padding: 10px; border-radius: 3px; }
        .ok { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        code { background: #f4f4f4; padding: 2px 5px; border-radius: 2px; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Kiểm tra PHP Extensions cho Ký số PDF</h1>
        
        <?php
        $checks = [];
        
        // Check OpenSSL
        $checks['OpenSSL'] = [
            'required' => true,
            'loaded' => extension_loaded('openssl'),
            'description' => 'Cần thiết để đọc certificate và ký số PDF'
        ];
        
        // Check GD
        $checks['GD'] = [
            'required' => true,
            'loaded' => extension_loaded('gd'),
            'description' => 'Cần thiết để xử lý PNG images với alpha channel trong TCPDF',
            'functions' => ['imagecreatefrompng', 'imagepng', 'imagecreatetruecolor']
        ];
        
        // Check Imagick
        $checks['Imagick'] = [
            'required' => false,
            'loaded' => extension_loaded('imagick'),
            'description' => 'Tùy chọn - tốt hơn GD để xử lý images (nếu có)'
        ];
        
        // Check TCPDF
        $checks['TCPDF'] = [
            'required' => true,
            'loaded' => class_exists('TCPDF'),
            'description' => 'Library để tạo và ký số PDF'
        ];
        
        // Display results
        foreach ($checks as $name => $check) {
            $class = 'error';
            $icon = '❌';
            
            if ($check['loaded']) {
                $class = 'ok';
                $icon = '✅';
            } elseif (!$check['required']) {
                $class = 'warning';
                $icon = '⚠️';
            }
            
            echo "<div class='check $class'>";
            echo "<strong>$icon $name:</strong> ";
            echo $check['loaded'] ? 'Đã cài đặt' : ($check['required'] ? 'CHƯA CÀI ĐẶT' : 'Không có (tùy chọn)');
            echo "<br><small>{$check['description']}</small>";
            
            if (isset($check['functions'])) {
                echo "<br><small>Functions: ";
                $funcs = [];
                foreach ($check['functions'] as $func) {
                    $funcs[] = function_exists($func) ? "<code>$func</code> ✅" : "<code>$func</code> ❌";
                }
                echo implode(', ', $funcs);
                echo "</small>";
            }
            
            echo "</div>";
        }
        
        // GD Details
        if (extension_loaded('gd')) {
            echo "<div class='check info'>";
            echo "<strong>📋 GD Information:</strong><br>";
            $gdInfo = gd_info();
            echo "<pre>";
            echo "GD Version: " . ($gdInfo['GD Version'] ?? 'Unknown') . "\n";
            echo "PNG Support: " . ($gdInfo['PNG Support'] ? '✅ Yes' : '❌ No') . "\n";
            echo "JPEG Support: " . ($gdInfo['JPEG Support'] ? '✅ Yes' : '❌ No') . "\n";
            echo "FreeType Support: " . ($gdInfo['FreeType Support'] ? '✅ Yes' : '❌ No') . "\n";
            echo "</pre>";
            echo "</div>";
        }
        
        // PHP Version
        echo "<div class='check info'>";
        echo "<strong>ℹ️ PHP Version:</strong> " . PHP_VERSION;
        echo "</div>";
        
        // Recommendations
        $hasGd = extension_loaded('gd');
        $hasImagick = extension_loaded('imagick');
        $hasOpenSSL = extension_loaded('openssl');
        
        if (!$hasGd && !$hasImagick) {
            echo "<div class='check error'>";
            echo "<strong>⚠️ VẤN ĐỀ NGHIÊM TRỌNG:</strong><br>";
            echo "Cần cài đặt ít nhất một trong hai: <code>GD</code> hoặc <code>Imagick</code> extension.<br>";
            echo "TCPDF không thể xử lý PNG images với alpha channel nếu thiếu extension này.";
            echo "</div>";
        }
        
        if (!$hasOpenSSL) {
            echo "<div class='check error'>";
            echo "<strong>⚠️ VẤN ĐỀ NGHIÊM TRỌNG:</strong><br>";
            echo "Cần cài đặt <code>OpenSSL</code> extension để đọc certificate và ký số PDF.";
            echo "</div>";
        }
        ?>
        
        <h2>📝 Hướng dẫn sửa lỗi trên Windows Server (IIS)</h2>
        <div class="check info">
            <h3>1. Enable GD Extension:</h3>
            <ol>
                <li>Mở file <code>php.ini</code> (thường ở <code>C:\php\php.ini</code> hoặc trong IIS Manager)</li>
                <li>Tìm dòng: <code>;extension=gd</code></li>
                <li>Bỏ dấu <code>;</code> để uncomment: <code>extension=gd</code></li>
                <li>Lưu file và restart IIS</li>
            </ol>
            
            <h3>2. Enable Imagick (Tùy chọn - tốt hơn GD):</h3>
            <ol>
                <li>Download ImageMagick từ: <a href="https://imagemagick.org/script/download.php#windows" target="_blank">https://imagemagick.org/script/download.php#windows</a></li>
                <li>Cài đặt ImageMagick</li>
                <li>Download PHP Imagick extension từ: <a href="https://pecl.php.net/package/imagick" target="_blank">https://pecl.php.net/package/imagick</a></li>
                <li>Copy file <code>php_imagick.dll</code> vào thư mục <code>ext</code> của PHP</li>
                <li>Thêm vào <code>php.ini</code>: <code>extension=imagick</code></li>
                <li>Restart IIS</li>
            </ol>
            
            <h3>3. Kiểm tra lại:</h3>
            <p>Refresh trang này sau khi đã enable extensions và restart IIS.</p>
        </div>
    </div>
</body>
</html>

