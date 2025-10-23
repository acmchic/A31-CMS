{{-- Global Inter Font Styles --}}
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap');

/* Apply Inter font to all elements globally, but exclude icon fonts */
*:not([class*="la-"]):not([class*="fa-"]):not([class*="icon-"]):not(.icon):not(i) {
    font-family: 'Inter', ui-sans-serif, system-ui, sans-serif !important;
}

/* Specific overrides for better rendering */
body, html {
    font-family: 'Inter', ui-sans-serif, system-ui, sans-serif !important;
    font-weight: 400;
}

/* Headers with Inter */
h1, h2, h3, h4, h5, h6 {
    font-family: 'Inter', ui-sans-serif, system-ui, sans-serif !important;
    font-weight: 600;
}

/* Form elements */
input, textarea, select, button {
    font-family: 'Inter', ui-sans-serif, system-ui, sans-serif !important;
}

/* Table elements */
table, th, td {
    font-family: 'Inter', ui-sans-serif, system-ui, sans-serif !important;
}

/* Navigation elements */
.navbar, .nav-link, .navbar-brand {
    font-family: 'Inter', ui-sans-serif, system-ui, sans-serif !important;
}

/* Backpack specific elements */
.card, .card-header, .card-body, .card-footer {
    font-family: 'Inter', ui-sans-serif, system-ui, sans-serif !important;
}

/* Button elements */
.btn {
    font-family: 'Inter', ui-sans-serif, system-ui, sans-serif !important;
    font-weight: 500;
}

/* Modal and dropdown elements */
.modal, .dropdown-menu, .dropdown-item {
    font-family: 'Inter', ui-sans-serif, system-ui, sans-serif !important;
}

/* Module specific elements */
.module-content, .module-header, .module-body {
    font-family: 'Inter', ui-sans-serif, system-ui, sans-serif !important;
}

/* PDF content */
.pdf-content {
    font-family: 'Inter', 'DejaVu Sans', sans-serif !important;
}
</style>
