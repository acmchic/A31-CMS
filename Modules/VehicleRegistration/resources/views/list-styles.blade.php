{{-- Custom CSS cho trang list Vehicle Registration --}}
<style>
/* ===== CẢI THIỆN GIAO DIỆN TRANG VEHICLE REGISTRATION LIST ===== */

/* Tăng height mặc định cho row trong table vehicle registration */
#crudTable tbody tr,
.table tbody tr {
    min-height: 60px !important;
    height: auto !important;
}

/* Đảm bảo cell trong table có padding đủ để hiển thị 2 dòng */
#crudTable tbody td,
.table tbody td {
    padding: 12px 8px !important;
    vertical-align: middle !important;
    line-height: 1.6 !important;
}

/* Cải thiện phần hành động (action buttons/badges) cho cân đối */
/* Chia action column thành 2 dòng: workflow buttons (dòng 1) và CRUD buttons (dòng 2) */
#crudTable tbody td:last-child,
.table tbody td:last-child {
    white-space: normal !important;
    text-align: center !important;
    vertical-align: middle !important;
    padding: 12px 8px !important;
}

/* Wrap tất cả buttons trong action column thành flex column */
#crudTable tbody td:last-child,
.table tbody td:last-child {
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
}

/* Dòng 1: Workflow buttons wrapper (Phê duyệt, Từ chối, Phân xe, Tải về) */
#crudTable tbody td:last-child .workflow-buttons,
.table tbody td:last-child .workflow-buttons {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 4px !important;
    justify-content: center !important;
    align-items: center !important;
}

/* Dòng 2: CRUD buttons wrapper (Xem, Sửa, Xóa) */
#crudTable tbody td:last-child .crud-buttons,
.table tbody td:last-child .crud-buttons {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 4px !important;
    justify-content: center !important;
    align-items: center !important;
}

/* Button actions trong vehicle registration table */
#crudTable .btn,
.table .btn {
    margin: 2px !important;
    vertical-align: middle !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 4px !important;
    flex-shrink: 0 !important;
}

/* Badge trong vehicle registration table */
#crudTable .badge,
.table .badge {
    margin: 2px !important;
    vertical-align: middle !important;
    display: inline-block !important;
}

/* Đảm bảo column "Ngày đi / Ngày về" hiển thị đẹp với 2 dòng */
#crudTable td[data-column-name="date_range"],
.table td {
    white-space: normal !important;
    word-wrap: break-word !important;
}
</style>
