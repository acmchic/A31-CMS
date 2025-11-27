{{-- Custom styles chỉ áp dụng cho table --}}
<style>
/* ===== CHỈ ÁP DỤNG CHO TABLE ===== */
/* Override CSS variables chỉ cho table và các element bên trong table */
.table,
.table *,
.table th,
.table td,
.table thead th,
.table tbody td,
.table tbody tr td,
table.table,
table.table th,
table.table td,
table.table thead th,
table.table tbody td,
.card-table,
.card-table *,
.card-table td,
.card-table th,
.card-table span,
.card-table div,
.card-table a,
.d-table,
.d-table *,
.d-table td,
.d-table th,
.d-table span,
.d-table div,
.d-table a {
    --tblr-body-color: #000000 !important;
    --tblr-emphasis-color: #000000 !important;
    color: #000000 !important;
}

/* Override text-muted chỉ trong table */
.table .text-muted,
.table .text-muted *,
table.table .text-muted,
table.table .text-muted * {
    color: #000000 !important;
}

/* ===== BADGE VÀ BUTTON TRONG TABLE - TEXT TRẮNG ===== */
/* Badge text luôn trắng - override table CSS */
.table .badge,
table.table .badge,
.card .card-body .table .badge,
.card .card-body table .badge,
.table td .badge,
table.table td .badge,
.card .card-body .table td .badge,
.card .card-body table td .badge,
.badge {
    color: #ffffff !important;
}

/* Button text luôn trắng - override table CSS */
.table .btn,
table.table .btn,
.card .card-body .table .btn,
.card .card-body table .btn,
.table td .btn,
table.table td .btn,
.card .card-body .table td .btn,
.card .card-body table td .btn,
.table tbody td .btn,
table.table tbody td .btn,
.card .card-body .table tbody td .btn,
.card .card-body table tbody td .btn,
.btn-success,
.btn-danger,
.btn-primary,
.btn-secondary {
    color: #ffffff !important;
}

/* Icon và text trong button - text trắng */
.table .btn *,
table.table .btn *,
.table td .btn *,
table.table td .btn *,
.card .card-body .table td .btn *,
.card .card-body table td .btn *,
.btn-success *,
.btn-danger *,
.btn-primary *,
.btn-secondary *,
.btn i,
.btn .la,
.btn .fa,
.btn span {
    color: #ffffff !important;
}

/* Button warning và info - text đen (background sáng) */
.btn-warning,
.btn-info,
.btn-warning *,
.btn-info * {
    color: #000000 !important;
}

/* Material Plan Inline Form - Label và input cùng dòng */
.material-plan-inline-field {
    display: flex;
    align-items: center;
    gap: 10px;
}

.material-plan-inline-field label {
    margin-bottom: 0;
    min-width: 150px;
    flex-shrink: 0;
}

.material-plan-inline-field input,
.material-plan-inline-field select,
.material-plan-inline-field textarea {
    flex: 1;
}

/* Initialize Select2 for multiple select */
.select2-multiple {
    width: 100% !important;
}

/* ===== SIDEBAR BADGE STYLING ===== */
/* Fix badge in sidebar menu - ensure it's fully visible and properly positioned */
.nav-link .badge {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 6px;
    font-size: 11px;
    font-weight: 600;
    line-height: 1;
    border-radius: 9px;
    vertical-align: middle;
    margin-left: 6px;
    z-index: 1;
    overflow: visible;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

/* Ensure badge doesn't get cut off */
.nav-item {
    overflow: visible;
}

.nav-link {
    overflow: visible;
    position: relative;
}

/* Badge positioning for better visibility */
.nav-link .badge.bg-danger {
    background-color: #dc3545 !important;
    color: #ffffff !important;
}
</style>