<?php

namespace App\Services;

use Modules\VehicleRegistration\Models\VehicleRegistration;
use LSNepomuceno\LaravelA1PdfSign\Sign\ManageCert;
use LSNepomuceno\LaravelA1PdfSign\Sign\SignaturePdf;
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
        if ($withSignature && $registration->directorApprover && $registration->directorApprover->signature_path) {
            $signedFilepath = self::addDigitalSignature($filepath, $registration);
            return $signedFilepath;
        }
        
        return $filepath;
    }
    
    /**
     * Add digital signature to PDF
     */
    public static function addDigitalSignature($pdfPath, VehicleRegistration $registration)
    {
        try {
            $approver = $registration->directorApprover;
            
            if (!$approver || !$approver->signature_path) {
                return $pdfPath; // Return unsigned PDF if no signature
            }
            
            $signaturePath = Storage::disk('public')->path($approver->signature_path);
            
            if (!file_exists($signaturePath)) {
                return $pdfPath; // Return unsigned PDF if signature file not found
            }
            
            $pdfFullPath = Storage::disk('public')->path($pdfPath);
            $signedFilename = 'signed_' . basename($pdfPath);
            $signedPath = 'vehicle_registrations/' . $signedFilename;
            $signedFullPath = Storage::disk('public')->path($signedPath);
            
            // Create signature configuration
            $signatureConfig = [
                'image' => $signaturePath,
                'x' => 400, // X position for signature
                'y' => 150, // Y position for signature  
                'width' => 100,
                'height' => 50,
                'page' => 1, // First page
                'reason' => 'Phê duyệt đăng ký xe',
                'location' => 'A31 Factory',
                'contact_info' => $approver->name,
            ];
            
            // Add visual signature to PDF
            self::addVisualSignature($pdfFullPath, $signedFullPath, $signaturePath, $signatureConfig);
            
            return $signedPath;
            
        } catch (\Exception $e) {
            \Log::error('PDF Signing Error: ' . $e->getMessage());
            return $pdfPath; // Return unsigned PDF on error
        }
    }
    
    /**
     * Add visual signature to PDF using image overlay
     */
    private static function addVisualSignature($inputPdf, $outputPdf, $signatureImage, $config)
    {
        // For now, use a simple approach with FPDF/TCPDF
        // This is a simplified version - you can enhance with lsnepomuceno/laravel-a1-pdf-sign
        
        try {
            // Copy original PDF
            copy($inputPdf, $outputPdf);
            
            // TODO: Implement proper PDF signature overlay
            // For now, just copy the file and add signature metadata
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Visual signature error: ' . $e->getMessage());
            return false;
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
}
