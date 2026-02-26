</div>
            </div>
        <footer class="sticky-footer" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border-top: 1px solid rgba(255, 255, 255, 0.1); margin-top: auto;">
            <div class="container my-auto">
                <div class="copyright text-center my-auto">
                    <span class="text-white">Copyright &copy; 
                        <a href="https://www.instagram.com/b_linker09?igsh=MTJkMWdtODhmMWwxaA==" class="text-info font-weight-bold" target="_blank" style="text-decoration: none;">b_linker09</a> 
                        <?php echo date('Y'); ?>
                    </span>
                </div>
            </div>
        </footer>
        </div>
    </div>
<a class="scroll-to-top rounded bg-info shadow-lg" href="#page-top" style="display: inline; border: 1px solid rgba(255,255,255,0.2);">
    <i class="fas fa-angle-up text-white"></i>
</a>

<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="background: rgba(25, 25, 30, 0.9); backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.15); color: white; border-radius: 15px;">
            <div class="modal-header" style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                <h5 class="modal-title" id="logoutModalLabel">Ready to Leave?</h5>
                <button class="close text-white" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-sign-out-alt fa-3x text-info mb-3"></i>
                <p>Select "Logout" below if you are ready to end your current session.</p>
            </div>
            <div class="modal-footer" style="border-top: 1px solid rgba(255, 255, 255, 0.1);">
                <button class="btn btn-outline-light btn-sm px-3" type="button" data-dismiss="modal">Cancel</button>
                <a class="btn btn-info btn-sm px-4 shadow-sm" href="logout.php">Logout</a>
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
    // Custom Datatables Styling for Glass UI
    $(document).ready(function() {
        if ( $.fn.dataTable && $.fn.dataTable.isDataTable( 'table' ) ) {
            $('.dataTables_filter input').addClass('form-control-sm border-secondary text-white');
            $('.dataTables_length select').addClass('form-control-sm border-secondary text-white');
        }
    });
</script>

</body>
</html>