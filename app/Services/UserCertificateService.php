<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class UserCertificateService
{
    /**
     * Get user certificate path
     */
    public static function getUserCertificatePath(User $user): ?string
    {
        // Thứ tự ưu tiên tìm certificate:
        // 1. Certificate riêng của user (nếu có)
        // 2. Certificate chung của công ty
        
        $userCertDir = storage_path('app/certificates/users/');
        $companyCertPath = storage_path('app/certificates/a31_factory.pfx');
        
        // Tìm certificate riêng của user
        $userCertPath = $userCertDir . 'user_' . $user->id . '.pfx';
        if (file_exists($userCertPath)) {
            return $userCertPath;
        }
        
        // Fallback về certificate chung
        if (file_exists($companyCertPath)) {
            return $companyCertPath;
        }
        
        return null;
    }
    
    /**
     * Validate certificate with PIN
     */
    public static function validateCertificate(string $certificatePath, string $pin): array
    {
        try {
            if (!file_exists($certificatePath)) {
                return [
                    'valid' => false,
                    'error' => 'Certificate file not found'
                ];
            }
            
            // Test certificate với PIN using PHP OpenSSL extension
            $certContent = file_get_contents($certificatePath);
            $certs = [];
            if (!openssl_pkcs12_read($certContent, $certs, $pin)) {
                throw new \Exception('Invalid PIN or certificate');
            }
            
            // Nếu không có exception, certificate hợp lệ
            return [
                'valid' => true,
                'certificate_path' => $certificatePath
            ];
            
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            
            // Phân loại lỗi
            if (strpos($errorMessage, 'invalid password') !== false || 
                strpos($errorMessage, 'MAC verification failed') !== false ||
                strpos($errorMessage, 'PKCS12') !== false) {
                return [
                    'valid' => false,
                    'error' => 'PIN không đúng'
                ];
            }
            
            if (strpos($errorMessage, 'Invalid file extension') !== false) {
                return [
                    'valid' => false,
                    'error' => 'File certificate không hợp lệ'
                ];
            }
            
            return [
                'valid' => false,
                'error' => 'Lỗi certificate: ' . $errorMessage
            ];
        }
    }
    
    /**
     * Get default PIN for company certificate
     */
    public static function getDefaultPin(): string
    {
        return config('pdf-sign.certificate_password', 'A31Factory2025');
    }
    
    /**
     * Check if user has personal certificate
     */
    public static function userHasPersonalCertificate(User $user): bool
    {
        $userCertPath = storage_path('app/certificates/users/user_' . $user->id . '.pfx');
        return file_exists($userCertPath);
    }
    
    /**
     * Upload user certificate
     */
    public static function uploadUserCertificate(User $user, $certificateFile): array
    {
        try {
            $userCertDir = storage_path('app/certificates/users/');
            
            // Tạo thư mục nếu chưa có
            if (!is_dir($userCertDir)) {
                mkdir($userCertDir, 0755, true);
            }
            
            $userCertPath = $userCertDir . 'user_' . $user->id . '.pfx';
            
            // Lưu file
            if (is_uploaded_file($certificateFile['tmp_name'])) {
                move_uploaded_file($certificateFile['tmp_name'], $userCertPath);
            } else {
                file_put_contents($userCertPath, $certificateFile);
            }
            
            return [
                'success' => true,
                'path' => $userCertPath
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

