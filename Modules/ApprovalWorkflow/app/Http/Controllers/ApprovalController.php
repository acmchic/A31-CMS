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
                    'message' => getUserTitle($user) . ' không có quyền phê duyệt'
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
                'message' => 'Phê duyệt thành công! Tài liệu đã được ký số.',
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
     * Approve without PIN (for reviewer step - intermediate step without signature)
     *
     * POST /approval/approve-without-pin/{modelClass}/{id}
     */
    public function approveWithoutPin(Request $request, string $modelClass, int $id)
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

            // Xác định module type
            $moduleType = $this->getModuleTypeForModel($modelClass);
            if (!$moduleType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không xác định được module type'
                ], 400);
            }

            // Get ApprovalRequest - QUAN TRỌNG: filter theo module_type
            $approvalRequest = \Modules\ApprovalWorkflow\Models\ApprovalRequest::where('model_type', $modelClass)
                ->where('model_id', $id)
                ->where('module_type', $moduleType) // ⚠️ QUAN TRỌNG: filter theo module_type
                ->first();

            if (!$approvalRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy yêu cầu phê duyệt'
                ], 404);
            }

            // Check permission
            $user = backpack_user();
            $modulePermission = $this->getModulePermission($modelClass);

            $hasApprovePermission = PermissionHelper::can($user, "{$modulePermission}.approve");
            
            // Only check review permission for Leave requests
            $hasReviewPermission = false;
            if ($modelClass === \Modules\PersonnelReport\Models\EmployeeLeave::class) {
                $hasReviewPermission = PermissionHelper::can($user, "{$modulePermission}.review");
            }

            if (!$hasApprovePermission && !$hasReviewPermission) {
                return response()->json([
                    'success' => false,
                    'message' => getUserTitle($user) . ' không có quyền phê duyệt'
                ], 403);
            }

            // Check if user can approve at current step
            if (!$approvalRequest->canBeApprovedBy($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền phê duyệt ở bước này'
                ], 403);
            }

            // Approve using ApprovalRequest
            $this->approvalService->approveRequest(
                $approvalRequest,
                $user,
                $request->only(['comment', 'metadata'])
            );

            // Reload approvalRequest to get updated status
            $approvalRequest->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Đã gửi lên BGD thành công!',
                'data' => [
                    'status' => $approvalRequest->status,
                    'current_step' => $approvalRequest->current_step,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Approval without PIN error:', [
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

            // Xác định module type
            $moduleType = $this->getModuleTypeForModel($modelClass);
            if (!$moduleType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không xác định được module type'
                ], 400);
            }

            // Get ApprovalRequest - QUAN TRỌNG: filter theo module_type
            $approvalRequest = \Modules\ApprovalWorkflow\Models\ApprovalRequest::where('model_type', $modelClass)
                ->where('model_id', $id)
                ->where('module_type', $moduleType) // ⚠️ QUAN TRỌNG: filter theo module_type
                ->first();

            if (!$approvalRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy yêu cầu phê duyệt'
                ], 404);
            }

            // Check permission
            $user = backpack_user();
            $modulePermission = $this->getModulePermission($modelClass);

            $hasApprovePermission = PermissionHelper::can($user, "{$modulePermission}.approve");
            
            // Only check review permission for Leave requests
            $hasReviewPermission = false;
            if ($modelClass === \Modules\PersonnelReport\Models\EmployeeLeave::class) {
                $hasReviewPermission = PermissionHelper::can($user, "{$modulePermission}.review");
            }

            if (!$hasApprovePermission && !$hasReviewPermission) {
                return response()->json([
                    'success' => false,
                    'message' => getUserTitle($user) . ' không có quyền từ chối'
                ], 403);
            }

            // Check if user can reject at current step
            if (!$approvalRequest->canBeApprovedBy($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền từ chối ở bước này'
                ], 403);
            }

            // Validate reason
            $request->validate([
                'reason' => 'required|string'
            ]);

            // Reject using ApprovalRequest
            $this->approvalService->rejectRequest(
                $approvalRequest,
                $user,
                $request->reason,
                $request->only(['comment', 'metadata'])
            );

            // Reload approvalRequest to get updated status
            $approvalRequest->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Đã từ chối thành công',
                'data' => [
                    'status' => $approvalRequest->status,
                    'rejection_reason' => $approvalRequest->rejection_reason,
                ]
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
        // Special cases - map model to correct permission prefix
        $modelName = class_basename($modelClass);

        // Map specific models to their permission prefixes
        $permissionMap = [
            'EmployeeLeave' => 'leave',
            'VehicleRegistration' => 'vehicle_registration',
            'RecordManagement' => 'record_management',
        ];

        if (isset($permissionMap[$modelName])) {
            return $permissionMap[$modelName];
        }

        // Fallback: Extract module name from namespace
        // Example: Modules\VehicleRegistration\Models\VehicleRegistration -> vehicle_registration
        $parts = explode('\\', $modelClass);
        $moduleName = $parts[1] ?? 'unknown';

        // Convert to snake_case
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $moduleName));
    }

    /**
     * Get module type from model class
     */
    protected function getModuleTypeForModel(string $modelClass): ?string
    {
        $modelName = class_basename($modelClass);

        // Map specific models to their module types
        $moduleTypeMap = [
            'EmployeeLeave' => 'leave',
            'VehicleRegistration' => 'vehicle',
            'MaterialPlan' => 'material',
        ];

        if (isset($moduleTypeMap[$modelName])) {
            return $moduleTypeMap[$modelName];
        }

        return null;
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
     * Preview PDF in browser (for iframe/print)
     *
     * GET /approval/preview-pdf/{modelClass}/{id}
     */
    public function previewPdf(string $modelClass, int $id)
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

            // Return PDF with inline disposition for preview
            return response()->file($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . (method_exists($model, 'getPdfFilename') ? $model->getPdfFilename() : 'document_' . $model->id . '.pdf') . '"'
            ]);

        } catch (\Exception $e) {
            \Log::error('PDF Preview Error:', [
                'error' => $e->getMessage(),
                'modelClass' => $modelClass ?? 'unknown',
                'id' => $id
            ]);

            abort(404, 'Không thể xem PDF: ' . $e->getMessage());
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

