<?php

namespace Modules\ApprovalWorkflow\Traits;

use App\Services\UserCertificateService;

/**
 * Trait HasDigitalSignature
 * 
 * Add digital signature capabilities to approval workflow
 */
trait HasDigitalSignature
{
    /**
     * Generate signed PDF path based on date and owner username
     */
    public function getSignedPdfDirectory(): string
    {
        // Allow model to override username logic
        $username = $this->getPdfOwnerUsername();
        
        $baseDir = $this->getPdfBaseDirectory();
        
        // Get date folder (YYYY_MM_DD format)
        $dateFolder = now()->format('Y_m_d');
        
        return "{$baseDir}/{$dateFolder}/{$username}";
    }
    
    /**
     * Get PDF owner username (can be overridden in model)
     * By default uses approver username, but model can override to use document owner
     */
    public function getPdfOwnerUsername(): string
    {
        // Check if model has custom logic
        if (method_exists($this, 'getCustomPdfOwnerUsername')) {
            return $this->getCustomPdfOwnerUsername();
        }
        
        // Default: use approver username
        $approver = $this->getCurrentLevelApprover();
        return $approver ? ($approver->username ?? 'user_' . $approver->id) : 'system';
    }

    /**
     * Get PDF base directory (override in model if needed)
     */
    public function getPdfBaseDirectory(): string
    {
        return property_exists($this, 'pdfDirectory') 
            ? $this->pdfDirectory 
            : strtolower(class_basename($this)) . 's';
    }

    /**
     * Get PDF filename
     * Override this method in your model to customize filename
     */
    public function getSignedPdfFilename(): string
    {
        // Check if model has custom filename pattern
        if (method_exists($this, 'getCustomPdfFilename')) {
            return $this->getCustomPdfFilename();
        }
        
        // Default pattern: modelname_id_timestamp.pdf
        $modelName = strtolower(class_basename($this));
        return "{$modelName}_{$this->id}_" . time() . '.pdf';
    }

    /**
     * Get full signed PDF path
     */
    public function getFullSignedPdfPath(): string
    {
        return $this->getSignedPdfDirectory() . '/' . $this->getSignedPdfFilename();
    }

    /**
     * Check if has signed PDF
     */
    public function hasSignedPdf(): bool
    {
        return !empty($this->signed_pdf_path) && 
               \Storage::disk('public')->exists($this->signed_pdf_path);
    }

    /**
     * Get PDF view name (override in model)
     */
    public function getPdfViewName(): string
    {
        return property_exists($this, 'pdfView') 
            ? $this->pdfView 
            : 'approvalworkflow::pdf.default';
    }

    /**
     * Get PDF data for generation
     */
    public function getPdfData(): array
    {
        return [
            'model' => $this,
            'approver' => $this->getCurrentLevelApprover(),
            'generated_at' => \Carbon\Carbon::now()->format('d/m/Y H:i:s'),
        ];
    }
}

