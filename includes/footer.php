    </div><!-- End content-wrapper -->
    <footer class="footer mt-auto py-3 bg-light border-top">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <p class="text-muted mb-0">&copy; 2026 <?php echo APP_NAME; ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="text-muted mb-0">Developed with <i class="bi bi-heart-fill text-danger"></i> by Your Company</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo APP_URL; ?>public/js/script.js"></script>
    <?php if(isset($additional_js)) echo $additional_js; ?>
</body>
</html>
