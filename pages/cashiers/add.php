<?php
define('ROOTPATH', $_SERVER['DOCUMENT_ROOT'] . '/starbuck');
include ROOTPATH . '/include/header.php';
?>

<div class="container-fluid px-0">
    <div class="card-soft card-narrow mt-2">
        <div class="mb-4">
            <h1 class="page-title">Tambah Kasir</h1>
            <p class="page-subtitle">Masukkan identitas petugas kasir baru</p>
        </div>

        <form action="../../process/process_cashiers.php" method="post">
            <input type="hidden" name="action" value="add">

            <div class="mb-3">
                <label for="nama_kasir" class="form-label fw-medium">Nama Kasir</label>
                <input type="text" name="nama_kasir" class="form-control" id="nama_kasir"
                    placeholder="Contoh: Putu Ayu" required>
            </div>

            <div class="mb-3">
                <label for="no_telp" class="form-label fw-medium">No. Telepon</label>
                <input type="number" name="no_telp" class="form-control tabular-nums" id="no_telp"
                    placeholder="Contoh: 081234567890" required>
            </div>

            <div class="mb-3">
                <label for="alamat" class="form-label fw-medium">Alamat</label>
                <input type="text" name="alamat" class="form-control" id="alamat"
                    placeholder="Contoh: Jl. Raya Puputan No. 10, Denpasar" required>
            </div>

            <div class="mb-4">
                <label for="email" class="form-label fw-medium">Email</label>
                <input type="email" name="email" class="form-control" id="email"
                    placeholder="nama@starbuck.id" required>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn-coffee">Simpan</button>
                <a href="list.php" class="btn-coffee-outline">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php
include ROOTPATH . '/include/footer.php';
?>
