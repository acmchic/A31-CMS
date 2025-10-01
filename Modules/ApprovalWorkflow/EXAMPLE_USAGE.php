<?php

/**
 * EXAMPLE: Cách sử dụng ApprovalWorkflow Module
 * 
 * File này demo cách implement approval workflow cho 1 module mới
 */

// ============================================================
// 1. MODEL EXAMPLE
// ============================================================

namespace Modules\YourModule\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\ApprovalWorkflow\Traits\HasApprovalWorkflow;
use Modules\ApprovalWorkflow\Traits\HasDigitalSignature;
use Modules\ApprovalWorkflow\Traits\ApprovalButtons;

class YourModel extends Model
{
    use HasApprovalWorkflow, HasDigitalSignature, ApprovalButtons;
    
    // ✅ Configure workflow type
    protected $workflowType = 'two_level'; // 'single', 'two_level', 'three_level'
    
    // ✅ Configure PDF view (optional - có default template)
    protected $pdfView = 'yourmodule::pdf.your-template';
    
    // ✅ Configure PDF directory (optional)
    protected $pdfDirectory = 'your_module_pdfs';
    
    // ✅ Override module permission prefix (optional)
    protected function getModulePermission(): string
    {
        return 'your_module'; // Sẽ check permissions: your_module.approve, your_module.reject
    }
    
    // ✅ Custom PDF title (optional)
    public function getPdfTitle(): string
    {
        return 'Your Document Title #' . $this->id;
    }
    
    // ✅ Custom PDF data (optional - override nếu cần thêm data)
    public function getPdfData(): array
    {
        return array_merge(parent::getPdfData(), [
            'custom_field' => $this->custom_field,
            'another_data' => 'value',
        ]);
    }
}

// ============================================================
// 2. MIGRATION EXAMPLE
// ============================================================

/**
 * Migration file: database/migrations/xxxx_create_your_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('your_table', function (Blueprint $table) {
            $table->id();
            
            // Your business fields
            $table->string('title');
            $table->text('description');
            // ...
            
            // ✅ Required: Workflow status
            $table->string('workflow_status')->default('pending');
            
            // ✅ Required: Level 1 approval (for all workflow types)
            $table->unsignedBigInteger('workflow_level1_by')->nullable();
            $table->timestamp('workflow_level1_at')->nullable();
            $table->string('workflow_level1_signature')->nullable();
            
            // ✅ Optional: Level 2 approval (for two_level, three_level)
            $table->unsignedBigInteger('workflow_level2_by')->nullable();
            $table->timestamp('workflow_level2_at')->nullable();
            $table->string('workflow_level2_signature')->nullable();
            
            // ✅ Optional: Level 3 approval (for three_level only)
            $table->unsignedBigInteger('workflow_level3_by')->nullable();
            $table->timestamp('workflow_level3_at')->nullable();
            $table->string('workflow_level3_signature')->nullable();
            
            // ✅ Required: Rejection
            $table->text('rejection_reason')->nullable();
            
            // ✅ Required: Signed PDF path
            $table->string('signed_pdf_path')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('workflow_level1_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('workflow_level2_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('workflow_level3_by')->references('id')->on('users')->onDelete('set null');
        });
    }
};

// ============================================================
// 3. CONTROLLER EXAMPLE
// ============================================================

namespace Modules\YourModule\Http\Controllers;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Helpers\PermissionHelper;

class YourCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    
    public function setup()
    {
        CRUD::setModel(\Modules\YourModule\Models\YourModel::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/your-module');
        CRUD::setEntityNameStrings('your item', 'your items');
        
        // Setup approval buttons
        $this->setupApprovalButtons();
    }
    
    protected function setupListOperation()
    {
        CRUD::column('id');
        CRUD::column('title');
        
        // ✅ Show workflow status
        CRUD::column('workflow_status_display')->label('Trạng thái duyệt');
        
        // ✅ Show approvers
        CRUD::column('level1Approver')->type('relationship')->label('Người duyệt cấp 1');
        CRUD::column('level2Approver')->type('relationship')->label('Người duyệt cấp 2');
    }
    
    // ✅ Setup approval buttons - SỬ DỤNG TRAITS
    private function setupApprovalButtons()
    {
        $user = backpack_user();
        
        // Add approve & reject buttons
        if (PermissionHelper::can($user, 'your_module.approve')) {
            CRUD::addButtonFromModelFunction('line', 'approve', 'approveButton', 'beginning');
            CRUD::addButtonFromModelFunction('line', 'reject', 'rejectButton', 'beginning');
        }
        
        // Add download PDF button
        if (PermissionHelper::can($user, 'your_module.view')) {
            CRUD::addButtonFromModelFunction('line', 'download_pdf', 'downloadPdfButton', 'beginning');
        }
    }
    
    // ❌ KHÔNG CẦN implement approve/reject methods!
    // ✅ ApprovalWorkflow module đã handle tất cả!
}

// ============================================================
// 4. PROGRAMMATIC USAGE EXAMPLE
// ============================================================

use Modules\ApprovalWorkflow\Services\ApprovalService;
use Modules\YourModule\Models\YourModel;

// Get approval service
$approvalService = app(ApprovalService::class);

// Example 1: Simple approve (without signature)
$model = YourModel::find(1);
$approver = auth()->user();

$approvalService->approve($model, $approver, [
    'comment' => 'Approved by manager',
    'metadata' => ['reason' => 'All documents are complete']
]);

// Example 2: Approve with digital signature
$approvalService->approveWithSignature(
    $model,
    $approver,
    $certificatePin = '123456',
    ['comment' => 'Approved with digital signature']
);

// Example 3: Reject
$approvalService->reject(
    $model,
    $approver,
    'Missing required documents',
    ['comment' => 'Please resubmit with all documents']
);

// Example 4: Get approval history
$history = $approvalService->getHistory($model);
foreach ($history as $record) {
    echo "Action: {$record->action_display}\n";
    echo "By: {$record->user->name}\n";
    echo "At: {$record->created_at}\n";
    echo "Level: {$record->level}\n";
}

// ============================================================
// 5. PDF TEMPLATE EXAMPLE
// ============================================================

/**
 * File: Modules/YourModule/resources/views/pdf/your-template.blade.php
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $model->getPdfTitle() }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; }
        h1 { text-align: center; color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #3498db; color: white; }
    </style>
</head>
<body>
    <h1>{{ $model->getPdfTitle() }}</h1>
    
    <h2>Thông tin chung</h2>
    <table>
        <tr>
            <td><strong>Mã số:</strong></td>
            <td>#{{ $model->id }}</td>
        </tr>
        <tr>
            <td><strong>Tiêu đề:</strong></td>
            <td>{{ $model->title }}</td>
        </tr>
        <tr>
            <td><strong>Trạng thái:</strong></td>
            <td>{{ $model->workflow_status_display }}</td>
        </tr>
    </table>
    
    @if($model->isApproved())
    <h2>Thông tin phê duyệt</h2>
    <table>
        @if($model->level1Approver)
        <tr>
            <td><strong>Người duyệt cấp 1:</strong></td>
            <td>{{ $model->level1Approver->name }}</td>
        </tr>
        @endif
        
        @if($model->level2Approver)
        <tr>
            <td><strong>Người duyệt cấp 2:</strong></td>
            <td>{{ $model->level2Approver->name }}</td>
        </tr>
        @endif
    </table>
    
    <div style="margin-top: 50px; text-align: center;">
        <p><strong>{{ $approver ? strtoupper($approver->name) : '' }}</strong></p>
        <p style="font-style: italic;">(Đã ký số)</p>
    </div>
    @endif
</body>
</html>
<?php

// ============================================================
// 6. CONFIG EXAMPLE
// ============================================================

/**
 * Custom workflow configuration
 * 
 * File: config/yourmodule.php
 */

return [
    'approval' => [
        // Override default workflow type for this module
        'workflow_type' => 'three_level',
        
        // Custom workflow steps (override default)
        'custom_steps' => [
            'pending' => 'Chờ duyệt',
            'manager_approved' => 'Quản lý đã duyệt',
            'director_approved' => 'Giám đốc đã duyệt',
            'ceo_approved' => 'Tổng giám đốc đã duyệt',
            'rejected' => 'Đã từ chối',
        ],
    ],
];

// ============================================================
// 7. PERMISSIONS EXAMPLE
// ============================================================

/**
 * Seeder: database/seeders/YourModulePermissionSeeder.php
 */

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class YourModulePermissionSeeder extends Seeder
{
    public function run()
    {
        // Create permissions
        $permissions = [
            'your_module.view',
            'your_module.create',
            'your_module.edit',
            'your_module.delete',
            'your_module.approve', // ✅ Required for approval
            'your_module.reject',  // ✅ Required for rejection
        ];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        
        // Assign to roles
        $managerRole = Role::firstOrCreate(['name' => 'Manager']);
        $managerRole->givePermissionTo([
            'your_module.view',
            'your_module.approve', // Manager can approve level 1
        ]);
        
        $directorRole = Role::firstOrCreate(['name' => 'Director']);
        $directorRole->givePermissionTo([
            'your_module.view',
            'your_module.approve', // Director can approve level 2
        ]);
    }
}

