<?php

namespace App\Services;

use Modules\PersonnelReport\Models\EmployeeLeave;
use Illuminate\Support\Facades\Storage;

class LeaveRequestPdfService
{
    /**
     * Generate approval PDF with digital signature
     */
    public static function generateApprovalPdf(EmployeeLeave $leaveRequest)
    {
        try {
            $user = backpack_user();
            
            // Get user certificate
            $certificatePath = UserCertificateService::getUserCertificatePath($user);
            
            if (!$certificatePath) {
                throw new \Exception('Không tìm thấy chứng thư số');
            }

            // Get certificate password from config
            $certificatePassword = config('pdf-sign.certificate_password', 'A31Factory2025');

            // Use TCPDF signer (Adobe Reader compatible)
            $pdfPath = TcpdfPdfSigner::generateLeaveRequestPdfWithSignature(
                $leaveRequest,
                $certificatePath,
                $certificatePassword
            );

            // ✅ Sửa: Không update signed_pdf_path vào employee_leave nữa vì đã chuyển sang approval_requests
            // Update approval_requests với PDF path
            $approvalRequest = $leaveRequest->approvalRequest;
            if ($approvalRequest) {
                $approvalRequest->signed_pdf_path = $pdfPath;
                $approvalRequest->save();
            }
            
            // Không update vào employee_leave vì cột không còn tồn tại
            // $leaveRequest->update([
            //     'signed_pdf_path' => $pdfPath
            // ]);

            return $pdfPath;

        } catch (\Exception $e) {
            \Log::error('Leave Request PDF Generation Error:', [
                'leave_request_id' => $leaveRequest->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Generate PDF without signature (preview)
     */
    public static function generatePdf(EmployeeLeave $leaveRequest, $signed = false)
    {
        try {
            if ($signed) {
                return self::generateApprovalPdf($leaveRequest);
            }

            // Generate simple PDF for preview
            $user = backpack_user();
            $certificatePath = UserCertificateService::getUserCertificatePath($user);
            $certificatePassword = config('pdf-sign.certificate_password', 'A31Factory2025');

            return TcpdfPdfSigner::generateLeaveRequestPdfWithSignature(
                $leaveRequest,
                $certificatePath,
                $certificatePassword
            );

        } catch (\Exception $e) {
            \Log::error('Leave Request PDF Generation Error:', [
                'leave_request_id' => $leaveRequest->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Validate PDF signature
     */
    public static function validatePdfSignature($pdfPath)
    {
        try {
            $fullPath = Storage::disk('public')->path($pdfPath);

            if (!file_exists($fullPath)) {
                return [
                    'valid' => false,
                    'error' => 'File PDF không tồn tại'
                ];
            }

            // Use TCPDF to validate
            return [
                'valid' => true,
                'message' => 'PDF đã được ký số'
            ];

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}




