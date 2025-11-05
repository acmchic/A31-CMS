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
</style>