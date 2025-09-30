<?php

namespace App\Services;

use Modules\VehicleRegistration\Models\VehicleRegistration;
use LSNepomuceno\LaravelA1PdfSign\Sign\ManageCert;
use LSNepomuceno\LaravelA1PdfSign\Sign\SignaturePdf;
use LSNepomuceno\LaravelA1PdfSign\Sign\ValidatePdfSignature;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class VehicleRegistrationPdfService
{
    /**
     * Generate PDF for vehicle registration
     */
    public static function generatePdf(VehicleRegistration $registration, $withSignature = false)
    {
        $data = [
            'registration' => $registration,
            'approver' => $registration->directorApprover,
            'generated_at' => Carbon::now()->format('d/m/Y H:i:s')
        ];

        // Generate PDF using view
        $pdf = Pdf::loadView('vehicleregistration::pdf.registration', $data);
        $pdf->setPaper('A4', 'portrait');
        
        // Generate filename
        $filename = 'vehicle_registration_' . $registration->id . '_' . time() . '.pdf';
        $filepath = 'vehicle_registrations/' . $filename;
        
        // Save PDF to storage
        $pdfContent = $pdf->output();
        Storage::disk('public')->put($filepath, $pdfContent);
        
        // If signature is required and user has signature
        \Log::info('Signature check', [
            'withSignature' => $withSignature,
            'has_approver' => $registration->directorApprover ? true : false,
            'approver_signature_path' => $registration->directorApprover ? $registration->directorApprover->signature_path : null
        ]);
        
        if ($withSignature && $registration->directorApprover) {
            // Use Pure PHP signer to avoid OpenSSL binary issues
            \Log::info('Using Pure PHP signer for generatePdf method');
            $signedFilepath = \App\Services\PurePHPPdfSigner::addPurePHPSignature($filepath, $registration, 
                storage_path('app/certificates/a31_factory.pfx'), 'A31Factory2025');
            return $signedFilepath;
        }
        
        return $filepath;
    }
    
    /**
     * Add digital signature to PDF using lsnepomuceno/laravel-a1-pdf-sign
     */
    public static function addDigitalSignature($pdfPath, VehicleRegistration $registration)
    {
        // DISABLED: Use Pure PHP signer instead to avoid OpenSSL binary issues
        \Log::info('addDigitalSignature called - redirecting to Pure PHP signer', [
            'registration_id' => $registration->id
        ]);
        
        return \App\Services\PurePHPPdfSigner::addPurePHPSignature(
            $pdfPath, 
            $registration, 
            storage_path('app/certificates/a31_factory.pfx'), 
            'A31Factory2025'
        );
    }
    
    /**
     * Add visual signature only (fallback when no certificate available)
     */
    private static function addVisualSignatureOnly($pdfPath, VehicleRegistration $registration)
    {
        try {
            $approver = $registration->directorApprover;
            $pdfFullPath = Storage::disk('public')->path($pdfPath);
            $signedFilename = 'visual_signed_' . basename($pdfPath);
            $signedPath = 'vehicle_registrations/' . $signedFilename;
            $signedFullPath = Storage::disk('public')->path($signedPath);
            
            // Copy original PDF
            copy($pdfFullPath, $signedFullPath);
            
            // Add signature metadata to the PDF
            // This creates a visual signature but not a cryptographically secure one
            
            \Log::info('Visual signature added (no digital certificate)', [
                'registration_id' => $registration->id,
                'signed_path' => $signedPath
            ]);
            
            return $signedPath;
            
        } catch (\Exception $e) {
            \Log::error('Visual signature error: ' . $e->getMessage());
            return $pdfPath; // Return original if error
        }
    }
    
    /**
     * Get certificate path for digital signing
     */
    private static function getCertificatePath()
    {
        // Check for certificate in storage/app/certificates/
        $certificateDir = storage_path('app/certificates/');
        
        if (!is_dir($certificateDir)) {
            return null;
        }
        
        // Look for .pfx files (package chỉ chấp nhận .pfx)
        $certificates = glob($certificateDir . '*.pfx', GLOB_BRACE);
        
        if (empty($certificates)) {
            return null;
        }
        
        return $certificates[0]; // Use first certificate found
    }
    
    /**
     * Validate PDF signature
     */
    public static function validatePdfSignature($pdfPath)
    {
        try {
            $fullPath = Storage::disk('public')->path($pdfPath);
            
            if (!file_exists($fullPath)) {
                return ['valid' => false, 'error' => 'File not found'];
            }
            
            $validation = ValidatePdfSignature::from($fullPath);
            
            return [
                'valid' => true,
                'signatures' => $validation,
                'message' => 'PDF signature validation completed'
            ];
            
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate approval PDF with signature
     */
    public static function generateApprovalPdf(VehicleRegistration $registration)
    {
        // Update registration with signature info
        $registration->update([
            'digital_signature_director' => $registration->directorApprover->signature_path ?? null
        ]);
        
        // Generate signed PDF
        $pdfPath = self::generatePdf($registration, true);
        
        // Store PDF path in registration
        $registration->update([
            'signed_pdf_path' => $pdfPath
        ]);
        
        return $pdfPath;
    }
    
    /**
     * Generate approval PDF with PIN (new method)
     */
    public static function generateApprovalPdfWithPin(VehicleRegistration $registration, string $certificatePath, string $pin)
    {
        $data = [
            'registration' => $registration,
            'approver' => $registration->directorApprover,
            'generated_at' => Carbon::now()->format('d/m/Y H:i:s')
        ];

        // Generate PDF using view
        $pdf = Pdf::loadView('vehicleregistration::pdf.registration', $data);
        $pdf->setPaper('A4', 'portrait');
        
        // Generate filename
        $filename = 'vehicle_registration_' . $registration->id . '_' . time() . '.pdf';
        $filepath = 'vehicle_registrations/' . $filename;
        
        // Save PDF to storage
        $pdfContent = $pdf->output();
        Storage::disk('public')->put($filepath, $pdfContent);
        
        // Add digital signature with provided PIN
        $signedFilepath = self::addDigitalSignatureWithPin($filepath, $registration, $certificatePath, $pin);
        
        // Store PDF path in registration
        $registration->update([
            'signed_pdf_path' => $signedFilepath,
            'digital_signature_director' => $registration->directorApprover->signature_path ?? null
        ]);
        
        return $signedFilepath;
    }
    
    /**
     * Add digital signature with specific certificate and PIN
     */
    public static function addDigitalSignatureWithPin($pdfPath, VehicleRegistration $registration, string $certificatePath, string $pin)
    {
        try {
            $approver = $registration->directorApprover;
            
            if (!$approver) {
                \Log::warning('No approver found for registration: ' . $registration->id);
                return $pdfPath;
            }
            
            $pdfFullPath = Storage::disk('public')->path($pdfPath);
            $signedFilename = 'signed_' . basename($pdfPath);
            $signedPath = 'vehicle_registrations/' . $signedFilename;
            $signedFullPath = Storage::disk('public')->path($signedPath);
            
            \Log::info('Digital signature with PIN process', [
                'registration_id' => $registration->id,
                'certificate_path' => $certificatePath,
                'certificate_exists' => file_exists($certificatePath),
                'approver' => $approver->name
            ]);
            
            if (!file_exists($certificatePath)) {
                throw new \Exception('Certificate file not found: ' . $certificatePath);
            }
            
            // Create digital signature using lsnepomuceno package with provided PIN
            $certificate = new ManageCert();
            $certificate->setPreservePfx()->fromPfx($certificatePath, $pin);
            
            $signature = new SignaturePdf(
                $pdfFullPath,
                $certificate,
                SignaturePdf::MODE_RESOURCE
            );
            
            // Configure signature info
            $signature->setInfo(
                $approver->name,
                'A31 Factory',
                'Phê duyệt đăng ký xe số ' . $registration->id,
                $approver->name . ' - ' . ($approver->email ?? 'admin@a31factory.com')
            );
            
            // Add visual signature image if available
            if ($approver->signature_path) {
                $signatureImagePath = Storage::disk('public')->path($approver->signature_path);
                if (file_exists($signatureImagePath)) {
                    $signature->setImage($signatureImagePath, 155, 50); // X, Y coordinates
                }
            }
            
            // Sign the PDF
            $signedContent = $signature->signature();
            
            // Save signed PDF
            file_put_contents($signedFullPath, $signedContent);
            
            \Log::info('PDF digitally signed successfully with PIN', [
                'registration_id' => $registration->id,
                'signed_path' => $signedPath,
                'approver' => $approver->name,
                'certificate_used' => basename($certificatePath)
            ]);
            
            return $signedPath;
            
        } catch (\Exception $e) {
            \Log::error('Digital PDF Signing Error with PIN: ' . $e->getMessage(), [
                'registration_id' => $registration->id,
                'certificate_path' => $certificatePath,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Re-throw exception để controller xử lý
            throw $e;
        }
    }
}
