    </main>

    <div class="modal fade sb-modal" id="sbConfirmModal" tabindex="-1" aria-labelledby="sbConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title h5 fw-semibold mb-0" id="sbConfirmModalLabel">Konfirmasi Hapus</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0" id="sbConfirmModalMessage">Apakah Anda yakin ingin menghapus data ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-neutral-outline btn-sm-soft" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn-danger-solid btn-sm-soft" id="sbConfirmModalOk">Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <?php
    $sb_flash_toast = null;
    if (function_exists('getFlashToast')) {
        $sb_flash_toast = getFlashToast();
    } else if (!empty($_SESSION['sb_flash_toast']) && is_array($_SESSION['sb_flash_toast'])) {
        $sb_flash_toast = $_SESSION['sb_flash_toast'];
        unset($_SESSION['sb_flash_toast']);
    }
    $sb_footer_path = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $sb_footer_base = isset($sb_base_url) ? $sb_base_url : ((strpos($sb_footer_path, '/starbuck/') === 0) ? '/starbuck' : '');
    ?>
    <script>
        window.SB_FLASH_TOAST = <?php echo json_encode($sb_flash_toast, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $sb_footer_base; ?>/assets/js/app.js"></script>
</body>

</html>
