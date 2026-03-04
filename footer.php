</div> </div> <footer class="sticky-footer" style="background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(15px); border-top: 1px solid rgba(255, 255, 255, 0.08); margin-top: auto; padding: 1.5rem 0;">
            <div class="container my-auto">
                <div class="copyright text-center my-auto">
                    <span class="text-white-50 small">Copyright &copy; 
                        <a href="https://www.instagram.com/b_linker09?igsh=MTJkMWdtODhmMWwxaA==" 
                           class="text-info font-weight-bold ml-1" 
                           target="_blank" 
                           style="text-decoration: none; transition: 0.3s; text-shadow: 0 0 10px rgba(14, 165, 233, 0.5);">
                           b_linker09
                        </a> 
                        <span class="mx-1">•</span> <?php echo date('Y'); ?>
                    </span>
                </div>
            </div>
        </footer>
    </div> </div> <a class="scroll-to-top rounded-circle bg-info shadow-lg" href="#page-top" style="display: none; border: 2px solid rgba(255,255,255,0.1); width: 45px; height: 45px; line-height: 43px; transition: all 0.3s ease-in-out;">
    <i class="fas fa-angle-up text-white"></i>
</a>

<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(25px); border: 1px solid rgba(14, 165, 233, 0.3); color: white; border-radius: 20px; box-shadow: 0 0 30px rgba(0,0,0,0.5);">
            <div class="modal-header" style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                <h5 class="modal-title font-weight-bold" id="logoutModalLabel">Ready to Leave?</h5>
                <button class="close text-white opacity-50" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-power-off fa-3x text-danger animate__animated animate__pulse animate__infinite" style="filter: drop-shadow(0 0 10px rgba(231, 74, 59, 0.4));"></i>
                </div>
                <p class="text-white-50">Select "Logout" below if you are ready to end your current session.</p>
            </div>
            <div class="modal-footer justify-content-center" style="border-top: 1px solid rgba(255, 255, 255, 0.05);">
                <button class="btn btn-link text-white-50 btn-sm text-decoration-none mr-3" type="button" data-dismiss="modal">Cancel</button>
                <a class="btn btn-info px-4 rounded-pill shadow-sm" href="logout.php" style="font-weight: 600; letter-spacing: 0.5px;">Logout Now</a>
            </div>
        </div>
    </div>
</div>

<script src="js/sb-admin-2.min.js"></script>
<script src="vendor/datatables/jquery.dataTables.min.js"></script>
<script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script type="text/javascript" src="vendor/parsley/dist/parsley.min.js"></script>
<script type="text/javascript" src="vendor/bootstrap-select/bootstrap-select.min.js"></script>

<script>
    $(document).ready(function() {
        // Global DataTable Settings to respect "Fit Content"
        $.extend( true, $.fn.dataTable.defaults, {
            "autoWidth": false, // Important: allows CSS 1% width to work
            "responsive": true,
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Search records..."
            }
        });

        // Apply Glass UI to Table Controls
        if ( $.fn.dataTable ) {
            $('.dataTables_filter input').addClass('form-control-sm border-0 bg-dark text-white ml-2').css('background', 'rgba(255,255,255,0.05)');
            $('.dataTables_length select').addClass('form-control-sm border-0 bg-dark text-white mx-2').css('background', 'rgba(255,255,255,0.05)');
        }

        // Smooth Scroll-to-top Fade
        $(window).scroll(function() {
            if ($(this).scrollTop() > 100) {
                $('.scroll-to-top').fadeIn();
            } else {
                $('.scroll-to-top').fadeOut();
            }
        });
    });
</script>

<style>
    /* Pulse animation for the logout icon */
    @keyframes pulse-red {
        0% { transform: scale(0.95); opacity: 0.8; }
        50% { transform: scale(1); opacity: 1; }
        100% { transform: scale(0.95); opacity: 0.8; }
    }
    .fa-power-off { animation: pulse-red 2s infinite; }
</style>

</body>
</html>