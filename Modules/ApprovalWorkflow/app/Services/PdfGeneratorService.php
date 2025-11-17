<?php

namespace Modules\ApprovalWorkflow\Services;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use TCPDF;

/**
 * PdfGeneratorService
 * 
 * Service for generating PDFs with digital signatures
 */
class PdfGeneratorService
{
    /**
     * Generate signed PDF with digital signature
     */
    public function generateSignedPdf(
        Model $model,
        User $approver,
        string $certificatePath,
        string $certificatePassword
    ): string {
        $engine = config('approvalworkflow::approval.pdf.engine', 'tcpdf');

        if ($engine === 'tcpdf') {
            return $this->generateWithTcpdf($model, $approver, $certificatePath, $certificatePassword);
        }

        return $this->generateWithDompdf($model, $approver);
    }

    /**
     * Generate PDF with TCPDF (supports digital signatures)
     */
    protected function generateWithTcpdf(
        Model $model,
        User $approver,
        string $certificatePath,
        string $certificatePassword
    ): string {
        // Read and parse certificate
        $certContent = file_get_contents($certificatePath);
        $certs = [];
        $success = openssl_pkcs12_read($certContent, $certs, $certificatePassword);

        if (!$success) {
            throw new \Exception('Không thể đọc certificate');
        }

        $certificate = $certs['cert'];
        $privateKey = $certs['pkey'];

        // Create TCPDF instance
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('A31 Factory CMS - ApprovalWorkflow Module');
        $pdf->SetAuthor($approver->name);
        $pdf->SetTitle($this->getPdfTitle($model));

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
        $html = $this->generatePdfHtml($model, $approver);

        // Write HTML content
        $pdf->writeHTML($html, true, false, true, false, '');

        // Set digital signature BEFORE finalizing the document
        $certificateInfo = openssl_x509_parse($certificate);

        $signatureInfo = [
            'Name' => $approver->name,
            'Location' => 'A31 Factory',
            'Reason' => $this->getSignatureReason($model),
            'ContactInfo' => $approver->email ?? 'admin@a31factory.com',
        ];

        // Set signature with visible type but positioned correctly
        $pdf->setSignature($certificate, $privateKey, $certificatePassword, '', 2, $signatureInfo);

        // Add signature image if available - position it near the signature area
        if ($approver->signature_path) {
            $signatureImagePath = Storage::disk('public')->path($approver->signature_path);
            if (file_exists($signatureImagePath)) {
                try {
                    $imagePath = $this->prepareImageForTcpdf($signatureImagePath);
                    if ($imagePath) {
                        $pdf->Image($imagePath, 130, 220, 50, 20, '', '', '', false, 300, '', false, false, 1);
                    }
                } catch (\Exception $e) {
                    \Log::warning('Cannot add signature image to PDF: ' . $e->getMessage());
                }
            }
        }

        // Generate file path
        $filename = $model->getSignedPdfFilename();
        $directory = $model->getSignedPdfDirectory();
        $filepath = "{$directory}/{$filename}";
        $fullPath = Storage::disk('public')->path($filepath);

        // Ensure directory exists
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Save PDF
        $pdfContent = $pdf->Output('', 'S');
        file_put_contents($fullPath, $pdfContent);

        \Log::info('PDF generated with digital signature', [
            'model' => get_class($model),
            'id' => $model->id,
            'path' => $filepath,
            'approver' => $approver->name,
        ]);

        return $filepath;
    }

    /**
     * Generate PDF with DomPDF (no digital signature support)
     */
    protected function generateWithDompdf(Model $model, User $approver): string
    {
        $viewName = $model->getPdfViewName();
        $data = $model->getPdfData();

        $pdf = Pdf::loadView($viewName, $data);
        $pdf->setPaper('A4', 'portrait');

        // Generate filename and path
        $filename = $model->getSignedPdfFilename();
        $directory = $model->getSignedPdfDirectory();
        $filepath = "{$directory}/{$filename}";

        // Save PDF
        $pdfContent = $pdf->output();
        Storage::disk('public')->put($filepath, $pdfContent);

        return $filepath;
    }

    /**
     * Generate PDF HTML content
     */
    protected function generatePdfHtml(Model $model, User $approver): string
    {
        $viewName = $model->getPdfViewName();
        $data = $model->getPdfData();

        // If custom view exists, use it
        if (view()->exists($viewName)) {
            return view($viewName, $data)->render();
        }

        // Otherwise use default template
        return view('approvalworkflow::pdf.default', $data)->render();
    }

    /**
     * Get PDF title
     */
    protected function getPdfTitle(Model $model): string
    {
        $modelName = class_basename($model);
        return "{$modelName} #{$model->id}";
    }

    /**
     * Get signature reason text
     */
    protected function getSignatureReason(Model $model): string
    {
        $modelName = class_basename($model);
        return "Phê duyệt {$modelName} số {$model->id}";
    }

    /**
     * Prepare image for TCPDF - convert PNG with alpha to JPG if needed
     */
    protected function prepareImageForTcpdf($imagePath)
    {
        if (!file_exists($imagePath)) {
            return null;
        }

        $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        
        if ($extension !== 'png') {
            return $imagePath;
        }

        $hasGd = extension_loaded('gd') && function_exists('imagecreatefrompng');
        $hasImagick = extension_loaded('imagick');

        if (!$hasGd && !$hasImagick) {
            \Log::warning('No GD or Imagick extension available. PNG image may cause TCPDF error.');
            return null;
        }

        $imageInfo = @getimagesize($imagePath);
        if (!$imageInfo) {
            return null;
        }

        $hasAlpha = ($imageInfo[2] === IMAGETYPE_PNG && ($imageInfo['channels'] ?? 0) === 4);

        if (!$hasAlpha) {
            return $imagePath;
        }

        $jpgPath = str_replace(['.png', '.PNG'], '.jpg', $imagePath);

        try {
            if ($hasImagick) {
                $imagick = new \Imagick($imagePath);
                $imagick->setImageBackgroundColor(new \ImagickPixel('white'));
                $imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
                $imagick->setImageFormat('jpeg');
                $imagick->setImageCompressionQuality(90);
                $imagick->writeImage($jpgPath);
                $imagick->clear();
                $imagick->destroy();
            } elseif ($hasGd) {
                $png = imagecreatefrompng($imagePath);
                if (!$png) {
                    return null;
                }

                $width = imagesx($png);
                $height = imagesy($png);
                $jpg = imagecreatetruecolor($width, $height);
                
                imagefill($jpg, 0, 0, imagecolorallocate($jpg, 255, 255, 255));
                imagecopyresampled($jpg, $png, 0, 0, 0, 0, $width, $height, $width, $height);
                
                imagejpeg($jpg, $jpgPath, 90);
                imagedestroy($png);
                imagedestroy($jpg);
            }

            if (file_exists($jpgPath)) {
                return $jpgPath;
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to convert PNG to JPG: ' . $e->getMessage());
        }

        return null;
    }
}


