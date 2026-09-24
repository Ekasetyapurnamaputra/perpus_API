<?php
require_once "cek_login.php";
require_once "koneksi.php";

function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$error = '';
$sukses = '';
$editMember = null;

$action = $_POST['aksi'] ?? '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if ($action === 'toggle_status') {
        $memberId = (int) ($_POST['member_id'] ?? 0);
        if ($memberId > 0) {
            try {
                $stmt = $koneksi->prepare(
                    "UPDATE members
                     SET status = IF(status = 'aktif', 'nonaktif', 'aktif')
                     WHERE id = ?"
                );
                $stmt->bind_param('i', $memberId);
                $stmt->execute();
                $stmt->close();
                header('Location: anggota.php?status=terupdate');
                exit;
            } catch (Throwable $exception) {
                $error = 'Status anggota gagal diperbarui.';
            }
        }
    } else {
        $memberId = (int) ($_POST['member_id'] ?? 0);
        $kode = trim($_POST['kode_anggota'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $telepon = trim($_POST['telepon'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $status = $_POST['status'] ?? 'aktif';

        if ($kode === '' || $nama === '' || $telepon === '') {
            $error = 'Kode anggota, nama, dan telepon wajib diisi.';
        } elseif (!in_array($status, ['aktif', 'nonaktif'], true)) {
            $error = 'Status anggota tidak valid.';
        } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Format email tidak valid.';
        } else {
            try {
                if ($action === 'update' && $memberId > 0) {
                    $stmt = $koneksi->prepare(
                        "UPDATE members
                         SET kode_anggota = ?, nama = ?, telepon = ?, email = ?, alamat = ?, status = ?
                         WHERE id = ?"
                    );
                    $stmt->bind_param('ssssssi', $kode, $nama, $telepon, $email, $alamat, $status, $memberId);
                } else {
                    $stmt = $koneksi->prepare(
                        "INSERT INTO members (kode_anggota, nama, telepon, email, alamat, status)
                         VALUES (?, ?, ?, ?, ?, ?)"
                    );
                    $stmt->bind_param('ssssss', $kode, $nama, $telepon, $email, $alamat, $status);
                }

                $stmt->execute();
                $stmt->close();
                header('Location: anggota.php?status=' . ($action === 'update' ? 'diperbarui' : 'ditambahkan'));
                exit;
            } catch (Throwable $exception) {
                $error = 'Data anggota gagal disimpan. Pastikan kode anggota belum digunakan.';
            }
        }
    }
}

$notice = [
    'ditambahkan' => ['success', 'Anggota baru berhasil ditambahkan.'],
    'diperbarui' => ['success', 'Data anggota berhasil diperbarui.'],
    'terupdate' => ['success', 'Status anggota berhasil diperbarui.'],
];
$noticeType = $notice[$_GET['status'] ?? ''][0] ?? '';
$noticeMessage = $notice[$_GET['status'] ?? ''][1] ?? '';

if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $stmt = $koneksi->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $editMember = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$search = trim($_GET['q'] ?? '');
$statusFilter = $_GET['status'] ?? '';
$where = [];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = '(m.kode_anggota LIKE ? OR m.nama LIKE ? OR m.telepon LIKE ? OR m.email LIKE ?)';
    $like = '%' . $search . '%';
    $params = array_merge($params, [$like, $like, $like, $like]);
    $types .= 'ssss';
}

if (in_array($statusFilter, ['aktif', 'nonaktif'], true)) {
    $where[] = 'm.status = ?';
    $params[] = $statusFilter;
    $types .= 's';
}

$memberWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$listSql = "SELECT m.*, COUNT(l.id) AS total_pinjaman, SUM(l.status = 'dipinjam') AS peminjaman_aktif
            FROM members m
            LEFT JOIN loans l ON l.member_id = m.id
            $memberWhere
            GROUP BY m.id
            ORDER BY m.nama ASC";
$listStmt = $koneksi->prepare($listSql);
if ($params) {
    $listStmt->bind_param($types, ...$params);
}
$listStmt->execute();
$members = $listStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$listStmt->close();

$totalMembers = (int) $koneksi->query("SELECT COUNT(*) AS total FROM members")->fetch_assoc()['total'];
$activeMembers = (int) $koneksi->query("SELECT COUNT(*) AS total FROM members WHERE status = 'aktif'")->fetch_assoc()['total'];
$newMembers = (int) $koneksi->query("SELECT COUNT(*) AS total FROM members WHERE MONTH(dibuat_at) = MONTH(CURDATE()) AND YEAR(dibuat_at) = YEAR(CURDATE())")->fetch_assoc()['total'];

$adminName = e($_SESSION['admin_username'] ?? 'Admin');
$adminInitial = strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anggota - Perpustakaan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <input type="checkbox" id="sidebar-toggle" class="sidebar-toggle">
    <div class="app-shell">
        <label for="sidebar-toggle" class="sidebar-backdrop" aria-label="Tutup menu navigasi"></label>
        <aside class="sidebar">
            <a href="dashboard.php" class="sidebar-brand" aria-label="Beranda Perpustakaan">
                <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H11v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z"></path><path d="M20 5.5A2.5 2.5 0 0 0 17.5 3H13v16h4.5a2.5 2.5 0 0 1 2.5 2.5v-16Z"></path></svg></span>
                <span class="brand-copy"><span class="brand-name">Perpustakaan</span><span class="brand-subtitle">Admin Workspace</span></span>
            </a>
            <nav class="sidebar-nav" aria-label="Navigasi utama">
                <span class="nav-section-label">Menu Utama</span>
                <a href="dashboard.php" class="sidebar-link"><span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="2"></rect><rect x="14" y="3" width="7" height="7" rx="2"></rect><rect x="3" y="14" width="7" height="7" rx="2"></rect><rect x="14" y="14" width="7" height="7" rx="2"></rect></svg></span>Dashboard</a>
                <a href="data_buku.php" class="sidebar-link"><span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"></path><path d="M8 7h8"></path></svg></span>Data Buku</a>
                <a href="anggota.php" class="sidebar-link active" aria-current="page"><span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"></circle><path d="M4 21a8 8 0 0 1 16 0"></path></svg></span>Anggota</a>
                <a href="peminjaman.php" class="sidebar-link"><span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5h16v14H4z"></path><path d="M8 9h8M8 13h5"></path><path d="M7 3v4M17 3v4"></path></svg></span>Peminjaman</a>
                <a href="tambah.php" class="sidebar-link"><span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 8v8M8 12h8"></path></svg></span>Tambah Buku</a>
                <span class="nav-section-label">Data & Laporan</span>
                <details class="nav-group"><summary class="sidebar-link nav-group-summary"><span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 19V9M10 19V5M16 19v-7M22 19V3"></path></svg></span>Laporan<svg class="nav-group-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg></summary><div class="nav-submenu"><a href="export_excel.php" class="sidebar-sublink">Export Excel</a><a href="export_pdf.php" target="_blank" rel="noopener" class="sidebar-sublink">Export PDF</a></div></details>
                <a href="data_json.php" target="_blank" rel="noopener" class="sidebar-link"><span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m8 9-4 3 4 3M16 9l4 3-4 3M14 5l-4 14"></path></svg></span>JSON API</a>
            </nav>
            <div class="sidebar-footer"><div class="sidebar-user"><span class="user-avatar"><?= e($adminInitial) ?></span><span class="user-copy"><span class="user-label">Administrator</span><span class="user-name"><?= $adminName ?></span></span></div><a href="logout.php" class="sidebar-logout" onclick="return confirm('Yakin ingin logout?')"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3"></path><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path></svg>Keluar dari akun</a></div>
        </aside>
        <div class="main-panel">
<button class="theme-toggle" type="button" data-theme-toggle aria-label="Aktifkan mode gelap" aria-pressed="false">Mode gelap</button>
            <header class="topbar"><div class="topbar-left"><label for="sidebar-toggle" class="menu-toggle" aria-label="Buka menu navigasi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"></path></svg></label><div class="topbar-context"><span class="topbar-title">Anggota</span><span class="topbar-date">Kelola data anggota perpustakaan</span></div></div><div class="topbar-user" title="<?= $adminName ?>"><span class="topbar-avatar"><?= e($adminInitial) ?></span><span class="topbar-user-copy"><?= $adminName ?></span></div></header>
            <main class="page-content">
                <header class="page-heading"><div class="page-heading-copy"><span class="eyebrow"><span class="eyebrow-dot"></span> Layanan_LIBRARY</span><h1 class="page-title">Anggota</h1><p class="page-description">Kelola identitas anggota dan pantau aktivitas peminjaman mereka.</p></div><div class="heading-actions"><a href="peminjaman.php" class="btn btn-primary"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v14H4z"></path><path d="M8 9h8M8 13h5"></path></svg>Buka Peminjaman</a></div></header>
                <?php if ($noticeMessage): ?><div class="alert-<?= e($noticeType) ?>" role="status"><?= e($noticeMessage) ?></div><?php endif; ?>
                <?php if ($error): ?><div class="alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
                <section class="stats-grid member-stats"><article class="stat-card blue"><span class="stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"></circle><path d="M3 20a6 6 0 0 1 12 0M16 11a3 3 0 1 0 0-6M17 14a5 5 0 0 1 4 6"></path></svg></span><div class="stat-info"><h3><?= $totalMembers ?></h3><p>Total Anggota</p></div></article><article class="stat-card green"><span class="stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"></path></svg></span><div class="stat-info"><h3><?= $activeMembers ?></h3><p>Anggota Aktif</p></div></article><article class="stat-card orange"><span class="stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3v18M17 7.5c0-1.7-2.2-3-5-3S7 5.8 7 7.5 9.2 10 12 10s5 1.3 5 3-2.2 3-5 3-5-1.3-5-3"></path></svg></span><div class="stat-info"><h3><?= $newMembers ?></h3><p>Ditambah Bulan Ini</p></div></article><article class="stat-card purple"><span class="stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5h16v14H4z"></path><path d="M8 9h8M8 13h5"></path></svg></span><div class="stat-info"><h3>—</h3><p>Riwayat Pinjam</p></div></article></section>
                <div class="member-layout">
                    <section class="surface-card form-card">
                        <div class="form-card-header"><span class="form-header-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"></circle><path d="M4 21a8 8 0 0 1 16 0"></path></svg></span><div><div class="form-header-title"><?= $editMember ? 'Edit Anggota' : 'Tambah Anggota' ?></div><div class="form-header-description"><?= $editMember ? 'Perbarui informasi anggota.' : 'Lengkapi identitas anggota baru.' ?></div></div></div>
                        <form class="form-body" method="POST" action="anggota.php">
                            <input type="hidden" name="aksi" value="<?= $editMember ? 'update' : 'simpan' ?>">
                            <?php if ($editMember): ?><input type="hidden" name="member_id" value="<?= (int) $editMember['id'] ?>"><?php endif; ?>
                            <div class="form-group"><label for="kode_anggota">Kode Anggota <span class="req">*</span></label><input class="form-control" id="kode_anggota" name="kode_anggota" value="<?= e($editMember['kode_anggota'] ?? '') ?>" placeholder="Contoh: AG001" maxlength="20" required></div>
                            <div class="form-group"><label for="nama">Nama Lengkap <span class="req">*</span></label><input class="form-control" id="nama" name="nama" value="<?= e($editMember['nama'] ?? '') ?>" placeholder="Contoh: Bella Pratama" maxlength="100" required></div>
                            <div class="form-group"><label for="telepon">Nomor Telepon <span class="req">*</span></label><input class="form-control" id="telepon" name="telepon" value="<?= e($editMember['telepon'] ?? '') ?>" placeholder="Contoh: 081234567890" maxlength="30" required></div>
                            <div class="form-group"><label for="email">Email</label><input class="form-control" type="email" id="email" name="email" value="<?= e($editMember['email'] ?? '') ?>" placeholder="nama@email.com" maxlength="100"></div>
                            <div class="form-group"><label for="alamat">Alamat</label><textarea class="form-control" id="alamat" name="alamat" rows="3" placeholder="Alamat lengkap anggota"><?= e($editMember['alamat'] ?? '') ?></textarea></div>
                            <div class="form-group"><label for="status">Status</label><select class="form-control" id="status" name="status"><option value="aktif" <?= (($editMember['status'] ?? 'aktif') === 'aktif') ? 'selected' : '' ?>>Aktif</option><option value="nonaktif" <?= (($editMember['status'] ?? '') === 'nonaktif') ? 'selected' : '' ?>>Nonaktif</option></select></div>
                            <div class="form-actions"><button class="btn btn-primary" type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h12l2 2v14H5z"></path><path d="M8 4v6h8V4M8 20v-6h8v6"></path></svg><?= $editMember ? 'Update Anggota' : 'Simpan Anggota' ?></button><?php if ($editMember): ?><a class="btn btn-secondary" href="anggota.php">Batal</a><?php endif; ?></div>
                        </form>
                    </section>
                    <section class="surface-card member-list-card">
                        <div class="member-list-header"><div><h2 class="panel-title">Daftar Anggota</h2><p class="panel-description"><?= count($members) ?> data ditampilkan</p></div></div>
                        <form class="member-search" method="GET" action="anggota.php"><div class="search-control"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg><input name="q" value="<?= e($search) ?>" placeholder="Cari nama, kode, telepon..." aria-label="Cari anggota"></div><select name="status" aria-label="Filter status"><option value="">Semua status</option><option value="aktif" <?= $statusFilter === 'aktif' ? 'selected' : '' ?>>Aktif</option><option value="nonaktif" <?= $statusFilter === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option></select><button class="btn btn-primary" type="submit">Cari</button></form>
                        <div class="table-wrapper member-table-wrap"><table class="table-data member-table"><thead><tr><th>Anggota</th><th>Kontak</th><th>Status</th><th>Pinjaman</th><th class="text-center">Aksi</th></tr></thead><tbody><?php if (!$members): ?><tr><td colspan="5" class="table-message">Data anggota tidak ditemukan.</td></tr><?php else: foreach ($members as $member): ?><tr><td><div class="member-cell"><span class="member-avatar"><?= e(strtoupper(substr($member['nama'], 0, 1))) ?></span><div><strong><?= e($member['nama']) ?></strong><small><?= e($member['kode_anggota']) ?></small></div></div></td><td><div class="contact-cell"><span><?= e($member['telepon']) ?></span><small><?= e($member['email'] ?: 'Tanpa email') ?></small></div></td><td><span class="status-pill <?= $member['status'] === 'aktif' ? 'status-aktif' : 'status-nonaktif' ?>"><?= e(ucfirst($member['status'])) ?></span></td><td><span class="loan-count"><?= (int) $member['total_pinjaman'] ?></span><small class="table-muted">transaksi</small></td><td><div class="member-actions"><a class="icon-action" href="anggota.php?edit=<?= (int) $member['id'] ?>" title="Edit anggota">✎</a><a class="icon-action icon-action-info" href="peminjaman.php?member_id=<?= (int) $member['id'] ?>" title="Lihat riwayat">↗</a><form method="POST" action="anggota.php" onsubmit="return confirm('Ubah status anggota ini?')"><input type="hidden" name="aksi" value="toggle_status"><input type="hidden" name="member_id" value="<?= (int) $member['id'] ?>"><button class="icon-action icon-action-warning" type="submit" title="Ubah status"><?= $member['status'] === 'aktif' ? '⊘' : '✓' ?></button></form></div></td></tr><?php endforeach; endif; ?></tbody></table></div>
                    </section>
                </div>
            </main>
        </div>
    </div>
<script>
(()=>{const r=document.documentElement,b=document.querySelector("[data-theme-toggle]"),k="perpustakaan-theme",g=()=>{try{return localStorage.getItem(k)}catch(e){}},s=t=>{try{localStorage.setItem(k,t)}catch(e){}},a=(t,x)=>{if(x){document.body.classList.add("theme-transition");setTimeout(()=>document.body.classList.remove("theme-transition"),520)}r.classList.toggle("theme-dark",t==="dark");if(b){b.textContent=t==="dark"?"Mode terang":"Mode gelap";b.setAttribute("aria-pressed",t==="dark"?"true":"false")}s(t)};a(g()||"light",false);if(b)b.addEventListener("click",()=>a(r.classList.contains("theme-dark")?"light":"dark",true))})()
</script>
</body>
</html>