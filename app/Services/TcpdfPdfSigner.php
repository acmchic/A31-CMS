<?php

namespace App\Services;

use Modules\VehicleRegistration\Models\VehicleRegistration;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use TCPDF;

class TcpdfPdfSigner
{
    /**
     * Generate PDF with TCPDF digital signature (Adobe Reader compatible)
     */
    public static function generateApprovalPdfWithPin(VehicleRegistration $registration, string $certificatePath, string $pin)
    {
        $approver = $registration->directorApprover;
        
        if (!$approver) {
            throw new \Exception('No approver found for registration: ' . $registration->id);
        }
        
        \Log::info('TCPDF signing process started', [
            'registration_id' => $registration->id,
            'certificate_path' => $certificatePath,
            'approver' => $approver->name
        ]);
        
        // Validate certificate
        if (!file_exists($certificatePath)) {
            throw new \Exception('Certificate file not found: ' . $certificatePath);
        }
        
        // Read and parse certificate
        $certContent = file_get_contents($certificatePath);
        $certs = [];
        $success = openssl_pkcs12_read($certContent, $certs, $pin);
        
        if (!$success) {
            throw new \Exception('Không thể đọc certificate với PIN đã cung cấp. Vui lòng kiểm tra lại PIN.');
        }
        
        // Extract certificate and private key
        $certificate = $certs['cert'];
        $privateKey = $certs['pkey'];
        
        if (!$certificate || !$privateKey) {
            throw new \Exception('Certificate hoặc private key không hợp lệ.');
        }
        
        // Create TCPDF instance
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('A31 Factory CMS');
        $pdf->SetAuthor($approver->name);
        $pdf->SetTitle('Đăng ký xe số ' . $registration->id);
        $pdf->SetSubject('Vehicle Registration Approval');
        $pdf->SetKeywords('Vehicle, Registration, Approval, Digital Signature');
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('dejavusans', '', 12);
        
        // Generate HTML content
        $html = self::generatePdfHtml($registration, $approver);
        
        // Write HTML content
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Set signature certificate
        $certificateInfo = openssl_x509_parse($certificate);
        
        // Create signature info
        $signatureInfo = [
            'Name' => $approver->name,
            'Location' => 'A31 Factory',
            'Reason' => 'Phê duyệt đăng ký xe số ' . $registration->id,
            'ContactInfo' => $approver->email ?? 'admin@a31factory.com',
        ];
        
        // Set digital signature
        $pdf->setSignature($certificate, $privateKey, $pin, '', 2, $signatureInfo);
        
        // Add signature appearance (optional - visual representation)
        if ($approver->signature_path) {
            $signatureImagePath = Storage::disk('public')->path($approver->signature_path);
            if (file_exists($signatureImagePath)) {
                // Add signature image at the bottom of the page
                $pdf->Image($signatureImagePath, 140, 250, 50, 20, '', '', '', false, 300, '', false, false, 1);
            }
        }
        
        // Generate filename - save theo username của người ký
        $approverUsername = $approver->username ?? 'user_' . $approver->id;
        $filename = 'vehicle_registration_' . $registration->id . '_' . time() . '.pdf';
        $signedPath = 'vehicle_registrations/' . $approverUsername . '/' . $filename;
        $signedFullPath = Storage::disk('public')->path($signedPath);
        
        // Ensure directory exists - tạo folder riêng cho mỗi user
        $directory = dirname($signedFullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Output signed PDF
        $pdfContent = $pdf->Output('', 'S'); // Get as string
        file_put_contents($signedFullPath, $pdfContent);
        
        // Update registration
        $registration->update([
            'signed_pdf_path' => $signedPath,
            'digital_signature_director' => 'TCPDF Digital Signature - Adobe Compatible'
        ]);
        
        \Log::info('TCPDF PDF signed successfully', [
            'registration_id' => $registration->id,
            'signed_path' => $signedPath,
            'approver' => $approver->name,
            'cert_subject' => $certificateInfo['subject']['CN'] ?? 'Unknown',
            'file_size' => filesize($signedFullPath)
        ]);
        
        return $signedPath;
    }
    
    /**
     * Generate PDF HTML content
     */
    private static function generatePdfHtml(VehicleRegistration $registration, $approver)
    {
        $html = '
        <style>
            h1 { text-align: center; color: #2c3e50; font-size: 20px; }
            h2 { color: #34495e; font-size: 16px; margin-top: 20px; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #3498db; color: white; }
            .info-row td:first-child { font-weight: bold; width: 40%; background-color: #ecf0f1; }
            .signature-section { margin-top: 30px; }
            .signature-box { text-align: center; margin-top: 50px; }
        </style>
        
        <h1>PHIẾU ĐĂNG KÝ SỬ DỤNG XE</h1>
        <h2>A31 FACTORY</h2>
        
        <h2>I. THÔNG TIN ĐĂNG KÝ</h2>
        <table>
            <tr class="info-row">
                <td>Mã đăng ký:</td>
                <td>#' . $registration->id . '</td>
            </tr>
            <tr class="info-row">
                <td>Người đăng ký:</td>
                <td>' . htmlspecialchars($registration->user->name ?? 'N/A') . '</td>
            </tr>
            <tr class="info-row">
                <td>Phòng ban:</td>
                <td>' . htmlspecialchars($registration->user->department->name ?? 'N/A') . '</td>
            </tr>
            <tr class="info-row">
                <td>Ngày giờ đi:</td>
                <td>' . Carbon::parse($registration->departure_datetime)->format('d/m/Y H:i') . '</td>
            </tr>
            <tr class="info-row">
                <td>Ngày giờ về:</td>
                <td>' . Carbon::parse($registration->return_datetime)->format('d/m/Y H:i') . '</td>
            </tr>
            <tr class="info-row">
                <td>Tuyến đường:</td>
                <td>' . nl2br(htmlspecialchars($registration->route)) . '</td>
            </tr>
            <tr class="info-row">
                <td>Mục đích:</td>
                <td>' . nl2br(htmlspecialchars($registration->purpose)) . '</td>
            </tr>
            <tr class="info-row">
                <td>Số người:</td>
                <td>' . $registration->passenger_count . '</td>
            </tr>
        </table>
        
        <h2>II. THÔNG TIN XE VÀ LÁI XE</h2>
        <table>
            <tr class="info-row">
                <td>Xe được phân:</td>
                <td>' . htmlspecialchars($registration->vehicle->full_name ?? 'Chưa phân') . '</td>
            </tr>
            <tr class="info-row">
                <td>Lái xe:</td>
                <td>' . htmlspecialchars($registration->driver_name ?? 'Chưa phân') . '</td>
            </tr>
            <tr class="info-row">
                <td>Bằng lái:</td>
                <td>' . htmlspecialchars($registration->driver_license ?? 'N/A') . '</td>
            </tr>
        </table>
        
        <h2>III. PHÊ DUYỆT</h2>
        <table>
            <tr class="info-row">
                <td>Trạng thái:</td>
                <td><strong style="color: green;">' . $registration->status_display . '</strong></td>
            </tr>
            <tr class="info-row">
                <td>Người phê duyệt:</td>
                <td>' . htmlspecialchars($approver->name) . '</td>
            </tr>
            <tr class="info-row">
                <td>Thời gian phê duyệt:</td>
                <td>' . Carbon::now()->format('d/m/Y H:i:s') . '</td>
            </tr>
        </table>
        
        <div class="signature-section">
            <table style="border: none;">
                <tr style="border: none;">
                    <td style="border: none; width: 50%;"></td>
                    <td style="border: none; width: 50%; text-align: center;">
                        <p><strong>BAN GIÁM ĐỐC</strong></p>
                        <p style="font-style: italic; font-size: 10px;">(Đã ký số)</p>
                        <br><br><br>
                        <p><strong>' . htmlspecialchars($approver->name) . '</strong></p>
                    </td>
                </tr>
            </table>
        </div>
        ';
        
        return $html;
    }
}
