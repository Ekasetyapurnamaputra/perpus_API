<?php
require_once "cek_login.php";
require_once "koneksi.php";

$error  = "";
$sukses = "";

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: data_buku.php");
    exit;
}

$stmt = $koneksi->prepare("SELECT * FROM books WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$buku = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$buku) {
    header("Location: data_buku.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_buku    = trim($_POST['kode_buku'] ?? '');
    $judul        = trim($_POST['judul'] ?? '');
    $penulis      = trim($_POST['penulis'] ?? '');
    $kategori     = trim($_POST['kategori'] ?? '');
    $tahun_terbit = trim($_POST['tahun_terbit'] ?? '');
    $penerbit     = trim($_POST['penerbit'] ?? '');

    if ($kode_buku === '' || $judul === '' || $penulis === '' ||
        $kategori === '' || $tahun_terbit === '' || $penerbit === '') {
        $error = "Semua field wajib diisi!";
    } elseif (!is_numeric($tahun_terbit) || $tahun_terbit < 1900 || $tahun_terbit > 2100) {
        $error = "Tahun terbit tidak valid!";
    } else {
        $cek = $koneksi->prepare("SELECT id FROM books WHERE kode_buku = ? AND id != ?");
        $cek->bind_param("si", $kode_buku, $id);
        $cek->execute();

        if ($cek->get_result()->num_rows > 0) {
            $error = "Kode buku '$kode_buku' sudah digunakan oleh buku lain!";
        } else {
            $stmt = $koneksi->prepare(
                "UPDATE books
                 SET kode_buku = ?, judul = ?, penulis = ?, kategori = ?, tahun_terbit = ?, penerbit = ?
                 WHERE id = ?"
            );
            $stmt->bind_param("ssssisi", $kode_buku, $judul, $penulis, $kategori, $tahun_terbit, $penerbit, $id);

            if ($stmt->execute()) {
                $sukses = "Data buku berhasil diperbarui!";
                $buku['kode_buku']    = $kode_buku;
                $buku['judul']        = $judul;
                $buku['penulis']      = $penulis;
                $buku['kategori']     = $kategori;
                $buku['tahun_terbit'] = $tahun_terbit;
                $buku['penerbit']     = $penerbit;
            } else {
                $error = "Gagal memperbarui data: " . $stmt->error;
            }
            $stmt->close();
        }
        $cek->close();
    }
}

$admin_name = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
$admin_initial = strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1));
$book_title = htmlspecialchars($buku['judul'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku - Perpustakaan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <input type="checkbox" id="sidebar-toggle" class="sidebar-toggle">

    <div class="app-shell">
        <label for="sidebar-toggle" class="sidebar-backdrop" aria-label="Tutup menu navigasi"></label>

        <aside class="sidebar">
            <a href="dashboard.php" class="sidebar-brand" aria-label="Beranda Perpustakaan">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H11v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z"></path>
                        <path d="M20 5.5A2.5 2.5 0 0 0 17.5 3H13v16h4.5a2.5 2.5 0 0 1 2.5 2.5v-16Z"></path>
                    </svg>
                </span>
                <span class="brand-copy">
                    <span class="brand-name">Perpustakaan</span>
                    <span class="brand-subtitle">Admin Workspace</span>
                </span>
            </a>

            <nav class="sidebar-nav" aria-label="Navigasi utama">
                <span class="nav-section-label">Menu Utama</span>
                <a href="dashboard.php" class="sidebar-link">
                    <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="2"></rect><rect x="14" y="3" width="7" height="7" rx="2"></rect><rect x="3" y="14" width="7" height="7" rx="2"></rect><rect x="14" y="14" width="7" height="7" rx="2"></rect></svg></span>
                    Dashboard
                </a>
                <a href="data_buku.php" class="sidebar-link active" aria-current="page">
                    <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"></path><path d="M8 7h8"></path></svg></span>
                    Data Buku
                </a>
                <a href="anggota.php" class="sidebar-link">
                    <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"></circle><path d="M4 21a8 8 0 0 1 16 0"></path></svg></span>
                    Anggota
                </a>
                <a href="peminjaman.php" class="sidebar-link">
                    <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5h16v14H4z"></path><path d="M8 9h8M8 13h5"></path><path d="M7 3v4M17 3v4"></path></svg></span>
                    Peminjaman
                </a>
                <a href="tambah.php" class="sidebar-link">
                    <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 8v8M8 12h8"></path></svg></span>
                    Tambah Buku
                </a>

                <span class="nav-section-label">Data & Laporan</span>
                <details class="nav-group">
                    <summary class="sidebar-link nav-group-summary">
                        <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 19V9M10 19V5M16 19v-7M22 19V3"></path></svg></span>
                        Laporan
                        <svg class="nav-group-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg>
                    </summary>
                    <div class="nav-submenu">
                        <a href="export_excel.php" class="sidebar-sublink">Export Excel</a>
                        <a href="export_pdf.php" target="_blank" rel="noopener" class="sidebar-sublink">Export PDF</a>
                    </div>
                </details>
                <a href="data_json.php" target="_blank" rel="noopener" class="sidebar-link">
                    <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m8 9-4 3 4 3M16 9l4 3-4 3M14 5l-4 14"></path></svg></span>
                    JSON API
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <span class="user-avatar"><?= htmlspecialchars($admin_initial, ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="user-copy"><span class="user-label">Administrator</span><span class="user-name"><?= $admin_name ?></span></span>
                </div>
                <a href="logout.php" class="sidebar-logout" onclick="return confirm('Yakin ingin logout?')">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3"></path><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path></svg>
                    Keluar dari akun
                </a>
            </div>
        </aside>

        <div class="main-panel">
            <header class="topbar">
                <div class="topbar-left">
                    <label for="sidebar-toggle" class="menu-toggle" aria-label="Buka menu navigasi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"></path></svg></label>
                    <div class="topbar-context">
                        <span class="topbar-title">Edit Buku</span>
                        <span class="topbar-date">Perbarui informasi koleksi</span>
                    </div>
                </div>
<button class="theme-toggle" type="button" data-theme-toggle aria-label="Aktifkan mode gelap" aria-pressed="false">Mode gelap</button>
                <div class="topbar-user" title="<?= $admin_name ?>">
                    <span class="topbar-avatar"><?= htmlspecialchars($admin_initial, ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="topbar-user-copy"><?= $admin_name ?></span>
                </div>
            </header>

            <main class="page-content">
                <header class="page-heading">
                    <div class="page-heading-copy">
                        <span class="eyebrow"><span class="eyebrow-dot"></span> Katalog Digital</span>
                        <h1 class="page-title">Edit Buku</h1>
                        <p class="page-description">Perbarui data “<?= $book_title ?>” dan pastikan informasi tetap akurat.</p>
                    </div>
                </header>

                <?php if ($error): ?>
                    <div class="alert-error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <?php if ($sukses): ?>
                    <div class="alert-success" role="status">
                        <?= htmlspecialchars($sukses, ENT_QUOTES, 'UTF-8') ?>
                        <a href="data_buku.php">Lihat Data Buku</a>
                    </div>
                <?php endif; ?>

                <div class="form-layout">
                    <section class="surface-card form-card">
                        <div class="form-card-header">
                            <span class="form-header-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"></path><path d="M8 7h8"></path></svg>
                            </span>
                            <div>
                                <div class="form-header-title">Informasi Buku</div>
                                <div class="form-header-description">ID buku: <?= (int) $buku['id'] ?> • <?= $book_title ?></div>
                            </div>
                        </div>

                        <form class="form-body" method="POST" action="edit.php?id=<?= (int) $buku['id'] ?>">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="kode_buku">Kode Buku <span class="req">*</span></label>
                                    <input class="form-control" type="text" id="kode_buku" name="kode_buku" value="<?= htmlspecialchars($buku['kode_buku'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: BK021" maxlength="20" required autofocus>
                                    <span class="form-hint">Kode harus unik dan berbeda dari buku lain.</span>
                                </div>
                                <div class="form-group">
                                    <label for="kategori">Kategori <span class="req">*</span></label>
                                    <input class="form-control" type="text" id="kategori" name="kategori" value="<?= htmlspecialchars($buku['kategori'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: Novel" maxlength="50" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="judul">Judul Buku <span class="req">*</span></label>
                                <input class="form-control" type="text" id="judul" name="judul" value="<?= htmlspecialchars($buku['judul'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: Laskar Pelangi" maxlength="150" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="penulis">Penulis <span class="req">*</span></label>
                                    <input class="form-control" type="text" id="penulis" name="penulis" value="<?= htmlspecialchars($buku['penulis'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: Andrea Hirata" maxlength="100" required>
                                </div>
                                <div class="form-group">
                                    <label for="tahun_terbit">Tahun Terbit <span class="req">*</span></label>
                                    <input class="form-control" type="number" id="tahun_terbit" name="tahun_terbit" value="<?= htmlspecialchars($buku['tahun_terbit'], ENT_QUOTES, 'UTF-8') ?>" min="1900" max="2100" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="penerbit">Penerbit <span class="req">*</span></label>
                                <input class="form-control" type="text" id="penerbit" name="penerbit" value="<?= htmlspecialchars($buku['penerbit'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: Gramedia" maxlength="100" required>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-warning">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h12l2 2v14H5z"></path><path d="M8 4v6h8V4M8 20v-6h8v6"></path></svg>
                                    Update Buku
                                </button>
                                <a href="data_buku.php" class="btn btn-secondary">Batal</a>
                            </div>
                        </form>
                    </section>

                    <aside class="surface-card form-aside">
                        <h2 class="panel-title">Sebelum Menyimpan</h2>
                        <p class="panel-description">Pastikan perubahan tidak mengganggu data lain.</p>
                        <div class="requirements">
                            <div class="requirement"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"></path></svg><span>Kode buku tidak boleh digunakan buku lain.</span></div>
                            <div class="requirement"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"></path></svg><span>Tahun terbit harus berada antara 1900–2100.</span></div>
                            <div class="requirement"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"></path></svg><span>Periksa kembali judul, penulis, dan penerbit.</span></div>
                        </div>
                    </aside>
                </div>
            </main>
        </div>
    </div>
<script>
(()=>{const r=document.documentElement,b=document.querySelector("[data-theme-toggle]"),k="perpustakaan-theme",g=()=>{try{return localStorage.getItem(k)}catch(e){}},s=t=>{try{localStorage.setItem(k,t)}catch(e){}},a=(t,x)=>{if(x){document.body.classList.add("theme-transition");setTimeout(()=>document.body.classList.remove("theme-transition"),520)}r.classList.toggle("theme-dark",t==="dark");if(b){b.textContent=t==="dark"?"Mode terang":"Mode gelap";b.setAttribute("aria-pressed",t==="dark"?"true":"false")}s(t)};a(g()||"light",false);if(b)b.addEventListener("click",()=>a(r.classList.contains("theme-dark")?"light":"dark",true))})()
</script>
</body>
</html>