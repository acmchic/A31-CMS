<?php

namespace Modules\ApprovalWorkflow\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ApprovalWorkflow\Services\ApprovalService;
use App\Helpers\PermissionHelper;

/**
 * ApprovalController
 *
 * Generic controller for handling approval actions
 */
class ApprovalController extends Controller
{
    protected $approvalService;

    public function __construct(ApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Approve with PIN (generic endpoint)
     *
     * POST /approval/approve/{modelClass}/{id}
     */
    public function approveWithPin(Request $request, string $modelClass, int $id)
    {
        try {
            // Decode model class from base64
            $modelClass = base64_decode($modelClass);

            if (!class_exists($modelClass)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid model class'
                ], 400);
            }

            // Find model
            $model = $modelClass::findOrFail($id);

            // Check permission
            $user = backpack_user();
            $modulePermission = $this->getModulePermission($modelClass);

            if (!PermissionHelper::can($user, "{$modulePermission}.approve")) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền phê duyệt'
                ], 403);
            }

            // Validate PIN
            $request->validate([
                'certificate_pin' => 'required|string|min:1'
            ]);

            // Approve with signature
            $result = $this->approvalService->approveWithSignature(
                $model,
                $user,
                $request->certificate_pin,
                $request->only(['comment', 'metadata'])
            );

            return response()->json([
                'success' => true,
                'message' => 'Phê duyệt thành công! PDF đã được ký số.',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            \Log::error('Approval with PIN error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject (generic endpoint)
     *
     * POST /approval/reject/{modelClass}/{id}
     */
    public function reject(Request $request, string $modelClass, int $id)
    {
        try {
            // Decode model class
            $modelClass = base64_decode($modelClass);

            if (!class_exists($modelClass)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid model class'
                ], 400);
            }

            // Find model
            $model = $modelClass::findOrFail($id);

            // Check permission
            $user = backpack_user();
            $modulePermission = $this->getModulePermission($modelClass);

            if (!PermissionHelper::can($user, "{$modulePermission}.reject")) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền từ chối'
                ], 403);
            }

            // Validate reason
            $request->validate([
                'reason' => 'required|string|min:5'
            ]);

            // Reject
            $this->approvalService->reject(
                $model,
                $user,
                $request->reason,
                $request->only(['comment', 'metadata'])
            );

            return response()->json([
                'success' => true,
                'message' => 'Đã từ chối thành công'
            ]);

        } catch (\Exception $e) {
            \Log::error('Rejection error:', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get module permission prefix from model class
     */
    protected function getModulePermission(string $modelClass): string
    {
        // Extract module name from namespace
        // Example: Modules\VehicleRegistration\Models\VehicleRegistration -> vehicle_registration

        $parts = explode('\\', $modelClass);
        $moduleName = $parts[1] ?? 'unknown';

        // Convert to snake_case
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $moduleName));
    }

    /**
     * Download signed PDF
     *
     * GET /approval/download-pdf/{modelClass}/{id}
     */
    public function downloadPdf(string $modelClass, int $id)
    {
        try {
            // Decode model class
            $modelClass = base64_decode($modelClass);

            if (!class_exists($modelClass)) {
                abort(404, 'Invalid model class');
            }

            // Find model
            $model = $modelClass::findOrFail($id);

            // Check if has signed PDF
            if (!$model->signed_pdf_path) {
                abort(404, 'PDF chưa được tạo');
            }

            // Get PDF file path
            $filePath = \Storage::disk('public')->path($model->signed_pdf_path);

            if (!file_exists($filePath)) {
                abort(404, 'File PDF không tồn tại');
            }

            // Get filename from model (if method exists) or use default
            $filename = method_exists($model, 'getPdfFilename')
                ? $model->getPdfFilename()
                : 'document_' . $model->id . '.pdf';

            return response()->download($filePath, $filename);

        } catch (\Exception $e) {
            \Log::error('PDF Download Error:', [
                'error' => $e->getMessage(),
                'modelClass' => $modelClass ?? 'unknown',
                'id' => $id
            ]);

            abort(404, 'Không thể Tải về: ' . $e->getMessage());
        }
    }

    /**
     * Get approval history for a model
     *
     * GET /approval/history/{modelClass}/{id}
     */
    public function history(string $modelClass, int $id)
    {
        try {
            $modelClass = base64_decode($modelClass);
            $model = $modelClass::findOrFail($id);

            $history = $this->approvalService->getHistory($model);

            return response()->json([
                'success' => true,
                'data' => $history
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

