<?php
define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/starbuck');
include ROOTPATH . '/include/header.php';
include ROOTPATH . '/config/config.php';

$query = "SELECT id_kasir, nama_kasir, no_telp, alamat, email FROM kasir ORDER BY id_kasir ASC";
$result = mysqli_query($koneksi, $query);
$daftar_kasir = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $daftar_kasir[] = $row;
    }
}
?>

<div class="container-fluid px-0">

    <div class="page-header">
        <div>
            <h1 class="page-title">Kasir</h1>
            <p class="page-subtitle">Kelola data petugas kasir operasional Starbuck</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <div class="search-box flex-grow-1" style="min-width: 240px;">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" id="search-kasir" class="form-control"
                    placeholder="Cari nama atau email kasir" autocomplete="off" aria-label="Cari nama atau email kasir">
            </div>
            <a href="/starbuck/pages/cashiers/add.php" class="btn-coffee">+ Tambah Kasir</a>
        </div>
    </div>

    <div class="card-soft card-soft-flush">
        <div class="table-wrap">
            <table class="table-soft">
                <thead>
                    <tr>
                        <th scope="col" class="col-index">No</th>
                        <th scope="col">Nama Kasir</th>
                        <th scope="col">No. Telepon</th>
                        <th scope="col">Alamat</th>
                        <th scope="col">Email</th>
                        <th scope="col" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody id="kasir-tbody">
                    <?php if (empty($daftar_kasir)): ?>
                    <tr>
                        <td colspan="6" class="empty-state">
                            Belum ada data kasir yang terdaftar. Klik "+ Tambah Kasir" untuk menambahkan petugas.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php $no = 1; foreach ($daftar_kasir as $row):
                        $id_k      = (int)$row['id_kasir'];
                        $nama_raw  = trim($row['nama_kasir'] ?? '');
                        $email_raw = trim($row['email'] ?? '');
                        $nama_esc  = htmlspecialchars($nama_raw, ENT_QUOTES, 'UTF-8');
                        $search_key = strtolower($nama_raw . ' ' . $email_raw);
                    ?>
                    <tr class="kasir-item-row" data-kasir-search="<?php echo htmlspecialchars($search_key, ENT_QUOTES, 'UTF-8'); ?>">
                        <td class="col-index py-3"><?php echo $no++; ?></td>
                        <td class="fw-semibold py-3"><?php echo $nama_esc; ?></td>
                        <td class="tabular-nums py-3"><?php echo htmlspecialchars($row['no_telp'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="py-3"><?php echo htmlspecialchars($row['alamat'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="py-3"><?php echo htmlspecialchars($email_raw, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="text-end py-3">
                            <div class="d-inline-flex gap-2">
                                <a href="edit.php?id_kasir=<?php echo urlencode((string)$id_k); ?>"
                                   class="btn-neutral-outline btn-sm-soft">Edit</a>
                                <a href="/starbuck/process/process_cashiers.php?action=delete&id_kasir=<?php echo urlencode((string)$id_k); ?>"
                                   class="btn-soft-danger btn-sm-soft"
                                   data-confirm-delete
                                   data-confirm-message="Apakah Anda yakin ingin menghapus kasir <?php echo $nama_esc; ?>?">Hapus</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="d-none" id="kasir-search-empty">
                        <td colspan="6" class="empty-state">
                            Tidak ada data kasir yang cocok dengan kata kunci pencarian Anda.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
(function () {
    var searchInput = document.getElementById('search-kasir');
    var rows        = document.querySelectorAll('.kasir-item-row');
    var emptyRow    = document.getElementById('kasir-search-empty');

    if (searchInput && rows.length > 0) {
        searchInput.addEventListener('input', function () {
            var keyword = searchInput.value.trim().toLowerCase();
            var visibleCount = 0;

            rows.forEach(function (row) {
                var key = row.getAttribute('data-kasir-search') || '';
                if (keyword === '' || key.indexOf(keyword) !== -1) {
                    row.classList.remove('d-none');
                    visibleCount++;
                } else {
                    row.classList.add('d-none');
                }
            });

            if (emptyRow) {
                if (visibleCount === 0) {
                    emptyRow.classList.remove('d-none');
                } else {
                    emptyRow.classList.add('d-none');
                }
            }
        });
    }
})();
</script>

<?php
include ROOTPATH . '/include/footer.php';
?>
