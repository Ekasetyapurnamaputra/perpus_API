<?php
require_once "cek_login.php";
require_once "koneksi.php";

function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function formatTanggal($value): string
{
    if (!$value) {
        return '—';
    }

    $timestamp = strtotime((string) $value);
    return $timestamp ? date('d/m/Y', $timestamp) : (string) $value;
}

$error = '';
$action = $_POST['aksi'] ?? '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if ($action === 'kembalikan') {
        $loanId = (int) ($_POST['loan_id'] ?? 0);

        try {
            $stmt = $koneksi->prepare(
                "UPDATE loans
                 SET status = 'dikembalikan', tanggal_kembali = NOW()
                 WHERE id = ? AND status = 'dipinjam'"
            );
            $stmt->bind_param('i', $loanId);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                header('Location: peminjaman.php?status=dikembalikan');
                exit;
            }

            $error = 'Pinjaman tidak dapat dikembalikan. Data mungkin sudah selesai.';
        } catch (Throwable $exception) {
            $error = 'Pengembalian gagal diproses.';
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    } elseif ($action === 'pinjam') {
        $memberId = (int) ($_POST['member_id'] ?? 0);
        $bookId = (int) ($_POST['book_id'] ?? 0);
        $borrowDate = trim($_POST['tanggal_pinjam'] ?? '');
        $dueDate = trim($_POST['batas_pengembalian'] ?? '');
        $notes = trim($_POST['catatan'] ?? '');

        $borrowDateObject = DateTime::createFromFormat('!Y-m-d', $borrowDate);
        $dueDateObject = DateTime::createFromFormat('!Y-m-d', $dueDate);
        $validBorrowDate = $borrowDateObject && $borrowDateObject->format('Y-m-d') === $borrowDate;
        $validDueDate = $dueDateObject && $dueDateObject->format('Y-m-d') === $dueDate;

        if ($memberId <= 0 || $bookId <= 0) {
            $error = 'Anggota dan buku wajib dipilih.';
        } elseif (!$validBorrowDate || !$validDueDate) {
            $error = 'Tanggal pinjam dan batas pengembalian tidak valid.';
        } elseif ($dueDate < $borrowDate) {
            $error = 'Batas pengembalian tidak boleh sebelum tanggal pinjam.';
        } else {
            $transactionStarted = false;
            try {
                $koneksi->begin_transaction();
                $transactionStarted = true;

                $memberStmt = $koneksi->prepare("SELECT id, status FROM members WHERE id = ? FOR UPDATE");
                $memberStmt->bind_param('i', $memberId);
                $memberStmt->execute();
                $member = $memberStmt->get_result()->fetch_assoc();
                $memberStmt->close();

                if (!$member || $member['status'] !== 'aktif') {
                    throw new RuntimeException('Anggota tidak ditemukan atau sedang nonaktif.');
                }

                $bookStmt = $koneksi->prepare("SELECT id FROM books WHERE id = ? FOR UPDATE");
                $bookStmt->bind_param('i', $bookId);
                $bookStmt->execute();
                $book = $bookStmt->get_result()->fetch_assoc();
                $bookStmt->close();

                if (!$book) {
                    throw new RuntimeException('Buku tidak ditemukan.');
                }

                $activeStmt = $koneksi->prepare(
                    "SELECT id FROM loans WHERE book_id = ? AND status = 'dipinjam' LIMIT 1 FOR UPDATE"
                );
                $activeStmt->bind_param('i', $bookId);
                $activeStmt->execute();
                $activeLoan = $activeStmt->get_result()->fetch_assoc();
                $activeStmt->close();

                if ($activeLoan) {
                    throw new RuntimeException('Buku ini sedang dipinjam dan belum tersedia.');
                }

                $adminId = (int) $_SESSION['admin_id'];
                $stmt = $koneksi->prepare(
                    "INSERT INTO loans (member_id, book_id, admin_id, tanggal_pinjam, batas_pengembalian, catatan)
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                $catatan = $notes !== '' ? $notes : null;
                $stmt->bind_param('iissss', $memberId, $bookId, $adminId, $borrowDate, $dueDate, $catatan);
                $stmt->execute();
                $stmt->close();

                $koneksi->commit();
                header('Location: peminjaman.php?status=dipinjam');
                exit;
            } catch (RuntimeException $exception) {
                if ($transactionStarted) {
                    $koneksi->rollback();
                }
                $error = $exception->getMessage();
            } catch (Throwable $exception) {
                if ($transactionStarted) {
                    $koneksi->rollback();
                }
                $error = 'Peminjaman gagal diproses. Silakan coba kembali.';
            }
        }
    }
}

$notice = [
    'dipinjam' => ['success', 'Peminjaman berhasil dicatat.'],
    'dikembalikan' => ['success', 'Buku berhasil dikembalikan.'],
];
$noticeType = $notice[$_GET['status'] ?? ''][0] ?? '';
$noticeMessage = $notice[$_GET['status'] ?? ''][1] ?? '';

$memberId = (int) ($_GET['member_id'] ?? $_POST['member_id'] ?? 0);
$memberSearch = trim($_GET['member_search'] ?? '');
$memberWhere = ["m.status = 'aktif'"];
$memberParams = [];
$memberTypes = '';

if ($memberSearch !== '') {
    $memberWhere[] = '(m.nama LIKE ? OR m.kode_anggota LIKE ?)';
    $like = '%' . $memberSearch . '%';
    $memberParams = [$like, $like];
    $memberTypes = 'ss';
}

$memberSql = "SELECT m.id, m.kode_anggota, m.nama
              FROM members m
              WHERE " . implode(' AND ', $memberWhere) . "
              ORDER BY m.nama ASC";
$memberStmt = $koneksi->prepare($memberSql);
if ($memberParams) {
    $memberStmt->bind_param($memberTypes, ...$memberParams);
}
$memberStmt->execute();
$members = $memberStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$memberStmt->close();

$books = $koneksi->query(
    "SELECT b.id, b.kode_buku, b.judul
     FROM books b
     LEFT JOIN loans l ON l.book_id = b.id AND l.status = 'dipinjam'
     WHERE l.id IS NULL
     ORDER BY b.judul ASC"
)->fetch_all(MYSQLI_ASSOC);

$activeLoans = (int) $koneksi->query("SELECT COUNT(*) AS total FROM loans WHERE status = 'dipinjam'")->fetch_assoc()['total'];
$overdueLoans = (int) $koneksi->query("SELECT COUNT(*) AS total FROM loans WHERE status = 'dipinjam' AND batas_pengembalian < CURDATE()")->fetch_assoc()['total'];
$returnedThisMonth = (int) $koneksi->query("SELECT COUNT(*) AS total FROM loans WHERE status = 'dikembalikan' AND MONTH(tanggal_kembali) = MONTH(CURDATE()) AND YEAR(tanggal_kembali) = YEAR(CURDATE())")->fetch_assoc()['total'];

$activeWhere = ["l.status = 'dipinjam'"];
$activeParams = [];
$activeTypes = '';
if ($memberId > 0) {
    $activeWhere[] = 'l.member_id = ?';
    $activeParams[] = $memberId;
    $activeTypes .= 'i';
}
$activeSql = "SELECT l.*, m.kode_anggota, m.nama AS nama_anggota, m.telepon,
                     b.kode_buku, b.judul
              FROM loans l
              JOIN members m ON m.id = l.member_id
              JOIN books b ON b.id = l.book_id
              WHERE " . implode(' AND ', $activeWhere) . "
              ORDER BY l.batas_pengembalian ASC, l.id DESC";
$activeStmt = $koneksi->prepare($activeSql);
if ($activeParams) {
    $activeStmt->bind_param($activeTypes, ...$activeParams);
}
$activeStmt->execute();
$activeLoansList = $activeStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$activeStmt->close();

$historyWhere = ["l.status = 'dikembalikan'"];
$historyParams = [];
$historyTypes = '';
if ($memberId > 0) {
    $historyWhere[] = 'l.member_id = ?';
    $historyParams[] = $memberId;
    $historyTypes .= 'i';
}
$historySql = "SELECT l.*, m.kode_anggota, m.nama AS nama_anggota,
                      b.kode_buku, b.judul
               FROM loans l
               JOIN members m ON m.id = l.member_id
               JOIN books b ON b.id = l.book_id
               WHERE " . implode(' AND ', $historyWhere) . "
               ORDER BY l.tanggal_kembali DESC, l.id DESC
               LIMIT 100";
$historyStmt = $koneksi->prepare($historySql);
if ($historyParams) {
    $historyStmt->bind_param($historyTypes, ...$historyParams);
}
$historyStmt->execute();
$history = $historyStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$historyStmt->close();

$selectedMember = null;
if ($memberId > 0) {
    foreach ($members as $memberOption) {
        if ((int) $memberOption['id'] === $memberId) {
            $selectedMember = $memberOption;
            break;
        }
    }
    if (!$selectedMember) {
        $selectedMemberStmt = $koneksi->prepare("SELECT id, kode_anggota, nama FROM members WHERE id = ?");
        $selectedMemberStmt->bind_param('i', $memberId);
        $selectedMemberStmt->execute();
        $selectedMember = $selectedMemberStmt->get_result()->fetch_assoc();
        $selectedMemberStmt->close();
    }
}

$adminName = e($_SESSION['admin_username'] ?? 'Admin');
$adminInitial = strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1));
$today = date('Y-m-d');
$defaultDueDate = date('Y-m-d', strtotime('+14 days'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman - Perpustakaan</title>
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
                <a href="anggota.php" class="sidebar-link"><span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"></circle><path d="M4 21a8 8 0 0 1 16 0"></path></svg></span>Anggota</a>
                <a href="peminjaman.php" class="sidebar-link active" aria-current="page"><span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5h16v14H4z"></path><path d="M8 9h8M8 13h5"></path><path d="M7 3v4M17 3v4"></path></svg></span>Peminjaman</a>
                <a href="tambah.php" class="sidebar-link"><span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 8v8M8 12h8"></path></svg></span>Tambah Buku</a>
                <span class="nav-section-label">Data & Laporan</span>
                <details class="nav-group"><summary class="sidebar-link nav-group-summary"><span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 19V9M10 19V5M16 19v-7M22 19V3"></path></svg></span>Laporan<svg class="nav-group-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg></summary><div class="nav-submenu"><a href="export_excel.php" class="sidebar-sublink">Export Excel</a><a href="export_pdf.php" target="_blank" rel="noopener" class="sidebar-sublink">Export PDF</a></div></details>
                <a href="data_json.php" target="_blank" rel="noopener" class="sidebar-link"><span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m8 9-4 3 4 3M16 9l4 3-4 3M14 5l-4 14"></path></svg></span>JSON API</a>
            </nav>
            <div class="sidebar-footer"><div class="sidebar-user"><span class="user-avatar"><?= e($adminInitial) ?></span><span class="user-copy"><span class="user-label">Administrator</span><span class="user-name"><?= $adminName ?></span></span></div><a href="logout.php" class="sidebar-logout" onclick="return confirm('Yakin ingin logout?')"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3"></path><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path></svg>Keluar dari akun</a></div>
        </aside>
        <div class="main-panel">
<button class="theme-toggle" type="button" data-theme-toggle aria-label="Aktifkan mode gelap" aria-pressed="false">Mode gelap</button>
            <header class="topbar"><div class="topbar-left"><label for="sidebar-toggle" class="menu-toggle" aria-label="Buka menu navigasi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"></path></svg></label><div class="topbar-context"><span class="topbar-title">Peminjaman</span><span class="topbar-date">Transaksi dan pengembalian buku</span></div></div><div class="topbar-user" title="<?= $adminName ?>"><span class="topbar-avatar"><?= e($adminInitial) ?></span><span class="topbar-user-copy"><?= $adminName ?></span></div></header>
            <main class="page-content">
                <header class="page-heading"><div class="page-heading-copy"><span class="eyebrow"><span class="eyebrow-dot"></span> Layanan Perpustakaan</span><h1 class="page-title">Peminjaman Buku</h1><p class="page-description">Catat peminjaman, pantau buku yang aktif, danelola pengembalian anggota.</p></div><div class="heading-actions"><a href="anggota.php" class="btn btn-secondary"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"></circle><path d="M4 21a8 8 0 0 1 16 0"></path></svg>Kelola Anggota</a></div></header>
                <?php if ($noticeMessage): ?><div class="alert-<?= e($noticeType) ?>" role="status"><?= e($noticeMessage) ?></div><?php endif; ?>
                <?php if ($error): ?><div class="alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
                <section class="stats-grid loan-stats"><article class="stat-card blue"><span class="stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5h16v14H4z"></path><path d="M8 9h8M8 13h5"></path></svg></span><div class="stat-info"><h3><?= $activeLoans ?></h3><p>Sedang Dipinjam</p></div></article><article class="stat-card red"><span class="stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3 2.5 20h19L12 3Z"></path><path d="M12 9v5M12 17h.01"></path></svg></span><div class="stat-info"><h3><?= $overdueLoans ?></h3><p>Terlambat</p></div></article><article class="stat-card green"><span class="stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"></path></svg></span><div class="stat-info"><h3><?= $returnedThisMonth ?></h3><p>Dikembalikan Bulan Ini</p></div></article><article class="stat-card purple"><span class="stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"></path></svg></span><div class="stat-info"><h3><?= count($books) ?></h3><p>Buku Tersedia</p></div></article></section>
                <div class="loan-layout">
                    <section class="surface-card form-card loan-form-card">
                        <div class="form-card-header"><span class="form-header-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5h16v14H4z"></path><path d="M8 9h8M8 13h5"></path><path d="M7 3v4M17 3v4"></path></svg></span><div><div class="form-header-title">Buat Peminjaman</div><div class="form-header-description">Pilih anggota dan buku yang tersedia.</div></div></div>
                        <form class="form-body" method="POST" action="peminjaman.php">
                            <input type="hidden" name="aksi" value="pinjam">
                            <div class="form-group"><label for="member_id">Anggota Peminjam <span class="req">*</span></label><select class="form-control" id="member_id" name="member_id" required><option value="">— Pilih anggota —</option><?php foreach ($members as $memberOption): ?><option value="<?= (int) $memberOption['id'] ?>" <?= $memberId === (int) $memberOption['id'] ? 'selected' : '' ?>><?= e($memberOption['kode_anggota']) ?> — <?= e($memberOption['nama']) ?></option><?php endforeach; ?></select><span class="form-hint"><?= $members ? 'Hanya anggota aktif yang dapat dipilih.' : 'Tambahkan anggota aktif terlebih dahulu.' ?></span></div>
                            <div class="form-group"><label for="book_id">Buku <span class="req">*</span></label><select class="form-control" id="book_id" name="book_id" required><option value="">— Pilih buku tersedia —</option><?php foreach ($books as $book): ?><option value="<?= (int) $book['id'] ?>"><?= e($book['kode_buku']) ?> — <?= e($book['judul']) ?></option><?php endforeach; ?></select><span class="form-hint">Buku yang sedang dipinjam tidak ditampilkan.</span></div>
                            <div class="form-row"><div class="form-group"><label for="tanggal_pinjam">Tanggal Pinjam <span class="req">*</span></label><input class="form-control" type="date" id="tanggal_pinjam" name="tanggal_pinjam" value="<?= $today ?>" required></div><div class="form-group"><label for="batas_pengembalian">Batas Pengembalian <span class="req">*</span></label><input class="form-control" type="date" id="batas_pengembalian" name="batas_pengembalian" value="<?= $defaultDueDate ?>" min="<?= $today ?>" required></div></div>
                            <div class="form-group"><label for="catatan">Catatan</label><textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Catatan peminjaman (opsional)"></textarea></div>
                            <div class="form-actions"><button class="btn btn-success" type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h12l2 2v14H5z"></path><path d="M8 4v6h8V4M8 20v-6h8v6"></path></svg>Simpan Peminjaman</button></div>
                        </form>
                    </section>
                    <section class="surface-card active-loans-card">
                        <div class="member-list-header"><div><h2 class="panel-title">Peminjaman Aktif</h2><p class="panel-description"><?= $activeLoans ?> buku sedang dipinjam</p></div><?php if ($memberId > 0): ?><a class="btn btn-secondary btn-compact" href="peminjaman.php">Tampilkan Semua</a><?php endif; ?></div>
                        <?php if ($selectedMember): ?><div class="filter-chip">Riwayat anggota: <strong><?= e($selectedMember['nama']) ?></strong> <a href="peminjaman.php" aria-label="Hapus filter">×</a></div><?php endif; ?>
                        <div class="active-loan-list"><?php if (!$activeLoansList): ?><div class="empty-state"><span class="empty-icon">✓</span><strong>Tidak ada peminjaman aktif</strong><span>Buku yang dipinjam akan muncul di sini.</span></div><?php else: foreach ($activeLoansList as $loan): $isOverdue = $loan['batas_pengembalian'] < $today; ?><article class="active-loan-item"><div class="loan-book-mark"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v14H4z"></path><path d="M8 9h8M8 13h5"></path></svg></div><div class="loan-item-copy"><strong><?= e($loan['judul']) ?></strong><span><?= e($loan['kode_buku']) ?> • <?= e($loan['nama_anggota']) ?></span><small>Batas: <b><?= formatTanggal($loan['batas_pengembalian']) ?></b></small></div><div class="loan-item-action"><span class="status-pill <?= $isOverdue ? 'status-terlambat' : 'status-dipinjam' ?>"><?= $isOverdue ? 'Terlambat' : 'Dipinjam' ?></span><form method="POST" action="peminjaman.php" onsubmit="return confirm('Kembalikan buku ini?')"><input type="hidden" name="aksi" value="kembalikan"><input type="hidden" name="loan_id" value="<?= (int) $loan['id'] ?>"><button class="btn btn-secondary btn-compact" type="submit">Kembalikan</button></form></div></article><?php endforeach; endif; ?></div>
                    </section>
                </div>
                <section class="surface-card history-card">
                    <div class="member-list-header"><div><h2 class="panel-title">Riwayat Pengembalian</h2><p class="panel-description">100 transaksi terakhir yang sudah selesai.</p></div><?php if ($memberId > 0): ?><a class="btn btn-secondary btn-compact" href="peminjaman.php">Semua Riwayat</a><?php endif; ?></div>
                    <div class="table-wrapper history-table-wrap"><table class="table-data history-table"><thead><tr><th>Anggota</th><th>Buku</th><th>Tanggal Pinjam</th><th>Batas Kembali</th><th>Dikembalikan</th><th class="text-center">Status</th></tr></thead><tbody><?php if (!$history): ?><tr><td colspan="6" class="table-message">Belum ada riwayat pengembalian.</td></tr><?php else: foreach ($history as $loan): ?><tr><td><div class="member-cell compact"><span class="member-avatar"><?= e(strtoupper(substr($loan['nama_anggota'], 0, 1))) ?></span><div><strong><?= e($loan['nama_anggota']) ?></strong><small><?= e($loan['kode_anggota']) ?></small></div></div></td><td><div class="history-book"><strong><?= e($loan['judul']) ?></strong><small><?= e($loan['kode_buku']) ?></small></div></td><td><?= formatTanggal($loan['tanggal_pinjam']) ?></td><td><?= formatTanggal($loan['batas_pengembalian']) ?></td><td><?= formatTanggal($loan['tanggal_kembali']) ?></td><td class="text-center"><span class="status-pill status-dikembalikan">Selesai</span></td></tr><?php endforeach; endif; ?></tbody></table></div>
                </section>
            </main>
        </div>
    </div>
<script>
(()=>{const r=document.documentElement,b=document.querySelector("[data-theme-toggle]"),k="perpustakaan-theme",g=()=>{try{return localStorage.getItem(k)}catch(e){}},s=t=>{try{localStorage.setItem(k,t)}catch(e){}},a=(t,x)=>{if(x){document.body.classList.add("theme-transition");setTimeout(()=>document.body.classList.remove("theme-transition"),520)}r.classList.toggle("theme-dark",t==="dark");if(b){b.textContent=t==="dark"?"Mode terang":"Mode gelap";b.setAttribute("aria-pressed",t==="dark"?"true":"false")}s(t)};a(g()||"light",false);if(b)b.addEventListener("click",()=>a(r.classList.contains("theme-dark")?"light":"dark",true))})()
</script>
</body>
</html>