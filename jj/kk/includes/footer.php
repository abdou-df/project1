                            </div> <!-- End of .content -->
        </div> <!-- End of .main-content -->
    </div> <!-- End of .wrapper -->

    <!-- JavaScript Dependencies -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/jquery.dataTables.min.js"></script>
    <script src="assets/js/chart.min.js"></script>
    <script src="assets/js/sidebar.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/theme.js"></script>
    <script src="assets/js/sidebar-fix.js"></script>
    
    <script>
    $(document).ready(function() {
        // Toggle user dropdown menu
        $('#userDropdown').on('click', function() {
            $('#userDropdownMenu').toggleClass('show');
        });
        
        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#userDropdown').length && !$(e.target).closest('#userDropdownMenu').length) {
                $('#userDropdownMenu').removeClass('show');
            }
        });
        
        // Mobile sidebar toggle
        $('.menu-toggle').on('click', function() {
            $('.sidebar').toggleClass('shown');
            $('.main-content').toggleClass('sidebar-shown');
        });
        
        // Handle dropdown toggles in sidebar
        $('.nav-link[data-bs-toggle="collapse"]').on('click', function(e) {
            e.preventDefault();
            var target = $(this).attr('href');
            $(target).toggleClass('show');
        });
    });
    </script>
</body>
</html>
