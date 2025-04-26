<style>
    .nav-item .nav-link.bg-light:hover {
        background-color: #f8f9fa !important; /* Bootstrap's secondary color */
        color: dark !important; /* Optional: make text white on hover */
    }
</style>

<div class="d-flex flex-column flex-shrink-0 bg-light text-white" style="width: 330px; height: 100vh; position: fixed;">
    <div class="p-3">
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                    <a class="nav-link bg-light text-secondary" href="index.php">
                        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                   </a>
            </li>
            <li class="nav-item">
                    <a class="nav-link bg-light text-secondary" href="project/login.php">
                        <i class="fas fa-map mr-2"></i> Project Management System
                   </a>
            </li>
            <li class="nav-item">
                    <a class="nav-link bg-light text-secondary" href="procurement/login.php">
                        <i class="fas fa-clipboard-list mr-2"></i> &nbsp;Procurement System
                   </a>
            </li>
            <li class="nav-item">
                    <a class="nav-link bg-light text-secondary" href="warehouse/login.php">
                        <i class="fas fa-warehouse mr-2"></i> Warehouse Management System
                   </a>
            </li>
            <li class="nav-item">
                    <a class="nav-link bg-light text-secondary" href="reservation/login.php">
                        <i class="fas fa-car-side mr-2"></i> Vehicle Reservation System
                   </a>
            </li>
        </ul>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://kit.fontawesome.com/your-kit-code.js"></script>
<script>
    $(document).ready(function () {
        var currentPage = window.location.pathname.split('/').pop();
        $('.nav-link').each(function () {
            var href = $(this).attr('href');
            if (href === currentPage) {
                $(this).addClass('active font-weight-bold');
                $(this).closest('ul.collapse').addClass('show');
                $(this).closest('ul.collapse').prev().addClass('active font-weight-bold');
            }
        });
    });
</script>
