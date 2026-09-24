<?php
require_once "cek_login.php";
require_once "koneksi.php";

// =====================================================
// STATISTIK DARI DATABASE
// =====================================================

$q1 = $koneksi->query("SELECT COUNT(*) AS total FROM books");
$total_buku = $q1->fetch_assoc()['total'];

$q2 = $koneksi->query("SELECT COUNT(DISTINCT kategori) AS total FROM books");
$total_kategori = $q2->fetch_assoc()['total'];

$q3 = $koneksi->query("SELECT COUNT(DISTINCT penulis) AS total FROM books");
$total_penulis = $q3->fetch_assoc()['total'];

$q4 = $koneksi->query("SELECT MAX(tahun_terbit) AS terbaru FROM books");
$tahun_terbaru = $q4->fetch_assoc()['terbaru'];

$q5 = $koneksi->query("SELECT COUNT(*) AS total FROM members WHERE status = 'aktif'");
$total_anggota = $q5->fetch_assoc()['total'];

$q6 = $koneksi->query("SELECT COUNT(*) AS total FROM loans WHERE status = 'dipinjam'");
$peminjaman_aktif = $q6->fetch_assoc()['total'];

$q7 = $koneksi->query("SELECT COUNT(*) AS total FROM loans WHERE status = 'dipinjam' AND batas_pengembalian < CURDATE()");
$peminjaman_terlambat = $q7->fetch_assoc()['total'];

$admin_name = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
$admin_initial = strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Perpustakaan</title>
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
                <a href="dashboard.php" class="sidebar-link active" aria-current="page">
                    <span class="nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="2"></rect><rect x="14" y="3" width="7" height="7" rx="2"></rect><rect x="3" y="14" width="7" height="7" rx="2"></rect><rect x="14" y="14" width="7" height="7" rx="2"></rect></svg>
                    </span>
                    Dashboard
                </a>
                <a href="data_buku.php" class="sidebar-link">
                    <span class="nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"></path><path d="M8 7h8"></path></svg>
                    </span>
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
                    <span class="nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 8v8M8 12h8"></path></svg>
                    </span>
                    Tambah Buku
                </a>

                <span class="nav-section-label">Data & Laporan</span>
                <details class="nav-group">
                    <summary class="sidebar-link nav-group-summary">
                        <span class="nav-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M4 19V9M10 19V5M16 19v-7M22 19V3"></path></svg>
                        </span>
                        Laporan
                        <svg class="nav-group-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg>
                    </summary>
                    <div class="nav-submenu">
                        <a href="export_excel.php" class="sidebar-sublink">Export Excel</a>
                        <a href="export_pdf.php" target="_blank" rel="noopener" class="sidebar-sublink">Export PDF</a>
                    </div>
                </details>
                <a href="data_json.php" target="_blank" rel="noopener" class="sidebar-link">
                    <span class="nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="m8 9-4 3 4 3M16 9l4 3-4 3M14 5l-4 14"></path></svg>
                    </span>
                    JSON API
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <span class="user-avatar"><?= htmlspecialchars($admin_initial, ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="user-copy">
                        <span class="user-label">Administrator</span>
                        <span class="user-name"><?= $admin_name ?></span>
                    </span>
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
                    <label for="sidebar-toggle" class="menu-toggle" aria-label="Buka menu navigasi">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </label>
                    <div class="topbar-context">
                        <span class="topbar-title">Dashboard</span>
                        <span class="topbar-date">Ringkasan aktivitas perpustakaan</span>
                    </div>
                </div>
                <button class="theme-toggle" type="button" data-theme-toggle aria-label="Aktifkan mode gelap" aria-pressed="false"><span class="theme-toggle-track" aria-hidden="true"><span class="theme-toggle-thumb"></span></span><span class="theme-toggle-text">Mode gelap</span></button>
                <div class="topbar-user" title="<?= $admin_name ?>">
                    <span class="topbar-avatar"><?= htmlspecialchars($admin_initial, ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="topbar-user-copy"><?= $admin_name ?></span>
                </div>
            </header>

            <main class="page-content">
                <section class="hero-card" aria-labelledby="dashboard-title">
                    <div class="hero-content">
                        <span class="hero-eyebrow">Portal Administrator</span>
                        <h1 class="hero-title" id="dashboard-title">Selamat datang, <span><?= $admin_name ?></span></h1>
                        <p class="hero-description">Kelola koleksi buku, pantau jumlah data, dan akses seluruh laporan perpustakaan dari satu tempat.</p>
                    </div>
                    <div class="hero-badge" aria-hidden="true">
                        <svg viewBox="0 0 64 64">
                            <path d="M10 15A7 7 0 0 1 17 8h15v44H17a7 7 0 0 0-7 7V15Z"></path>
                            <path d="M54 15A7 7 0 0 0 47 8H32v44h15a7 7 0 0 1 7 7V15Z"></path>
                            <path d="M39 20h8M39 27h8"></path>
                        </svg>
                    </div>
                </section>

                <section class="stats-grid" aria-label="Statistik Perpustakaan">
                    <article class="stat-card blue">
                        <span class="stat-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"></path></svg>
                        </span>
                        <div class="stat-info">
                            <h3><?= htmlspecialchars((string) $total_buku, ENT_QUOTES, 'UTF-8') ?></h3>
                            <p>Total Buku</p>
                        </div>
                    </article>

                    <article class="stat-card green">
                        <span class="stat-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="m20.6 13.6-7.1 7.1a2 2 0 0 1-2.8 0l-7.1-7.1A2 2 0 0 1 3 12.2V5a2 2 0 0 1 2-2h7.2a2 2 0 0 1 1.4.6l7.1 7.1a2 2 0 0 1 0 2.8Z"></path><circle cx="7.5" cy="7.5" r="1.5"></circle></svg>
                        </span>
                        <div class="stat-info">
                            <h3><?= htmlspecialchars((string) $total_kategori, ENT_QUOTES, 'UTF-8') ?></h3>
                            <p>Kategori Buku</p>
                        </div>
                    </article>

                    <article class="stat-card orange">
                        <span class="stat-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"></circle><path d="M4 21a8 8 0 0 1 16 0"></path></svg>
                        </span>
                        <div class="stat-info">
                            <h3><?= htmlspecialchars((string) $total_penulis, ENT_QUOTES, 'UTF-8') ?></h3>
                            <p>Penulis Terdaftar</p>
                        </div>
                    </article>

                    <article class="stat-card purple">
                        <span class="stat-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4M8 3v4M3 11h18"></path><path d="M8 15h.01M12 15h.01M16 15h.01"></path></svg>
                        </span>
                        <div class="stat-info">
                            <h3><?= $tahun_terbaru !== null ? htmlspecialchars((string) $tahun_terbaru, ENT_QUOTES, 'UTF-8') : '—' ?></h3>
                            <p>Tahun Terbit Terbaru</p>
                        </div>
                    </article>

                    <article class="stat-card blue">
                        <span class="stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"></circle><path d="M3 20a6 6 0 0 1 12 0M16 11a3 3 0 1 0 0-6M17 14a5 5 0 0 1 4 6"></path></svg></span>
                        <div class="stat-info"><h3><?= htmlspecialchars((string) $total_anggota, ENT_QUOTES, 'UTF-8') ?></h3><p>Anggota Aktif</p></div>
                    </article>

                    <article class="stat-card orange">
                        <span class="stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5h16v14H4z"></path><path d="M8 9h8M8 13h5"></path><path d="M7 3v4M17 3v4"></path></svg></span>
                        <div class="stat-info"><h3><?= htmlspecialchars((string) $peminjaman_aktif, ENT_QUOTES, 'UTF-8') ?></h3><p>Peminjaman Aktif</p></div>
                    </article>

                    <article class="stat-card red">
                        <span class="stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3 2.5 20h19L12 3Z"></path><path d="M12 9v5M12 17h.01"></path></svg></span>
                        <div class="stat-info"><h3><?= htmlspecialchars((string) $peminjaman_terlambat, ENT_QUOTES, 'UTF-8') ?></h3><p>Terlambat</p></div>
                    </article>
                </section>

                <section class="dashboard-grid">
                    <article class="surface-card panel">
                        <div class="panel-header">
                            <div>
                                <h2 class="panel-title">Akses Cepat</h2>
                                <p class="panel-description">Pilih tindakan yang ingin Anda lakukan.</p>
                            </div>
                        </div>
                        <div class="action-grid">
                            <a href="data_buku.php" class="action-card">
                                <span class="action-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"></path><path d="M8 7h8"></path></svg>
                                </span>
                                <span>
                                    <span class="action-name">Kelola Data Buku</span>
                                    <span class="action-link">Lihat koleksi <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"></path></svg></span>
                                </span>
                            </a>

                            <a href="tambah.php" class="action-card">
                                <span class="action-icon green" aria-hidden="true">
                                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 8v8M8 12h8"></path></svg>
                                </span>
                                <span>
                                    <span class="action-name">Tambah Buku Baru</span>
                                    <span class="action-link">Tambah data <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"></path></svg></span>
                                </span>
                            </a>

                            <a href="peminjaman.php" class="action-card">
                                <span class="action-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5h16v14H4z"></path><path d="M8 9h8M8 13h5"></path><path d="M7 3v4M17 3v4"></path></svg></span>
                                <span><span class="action-name">Kelola Peminjaman</span><span class="action-link">Catat transaksi <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"></path></svg></span></span>
                            </a>

                            <a href="data_json.php" target="_blank" rel="noopener" class="action-card">
                                <span class="action-icon purple" aria-hidden="true">
                                    <svg viewBox="0 0 24 24"><path d="m8 9-4 3 4 3M16 9l4 3-4 3M14 5l-4 14"></path></svg>
                                </span>
                                <span>
                                    <span class="action-name">JSON API</span>
                                    <span class="action-link">Buka endpoint <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"></path></svg></span>
                                </span>
                            </a>
                        </div>
                    </article>

                    <aside class="surface-card panel tip-card">
                        <div class="panel-header">
                            <div>
                                <h2 class="panel-title">Panduan Singkat</h2>
                                <p class="panel-description">Alur pengelolaan koleksi.</p>
                            </div>
                        </div>
                        <div class="tip-list">
                            <div class="tip-item">
                                <span class="tip-check" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"></path></svg></span>
                                Tambah buku dengan kode unik.
                            </div>
                            <div class="tip-item">
                                <span class="tip-check" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"></path></svg></span>
                                Gunakan pencarian dan kategori.
                            </div>
                            <div class="tip-item">
                                <span class="tip-check" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"></path></svg></span>
                                Unduh laporan Excel atau PDF.
                            </div>
                        </div>
                    </aside>
                </section>
            </main>
        </div>
    </div>
<script>
(function () {
    const root = document.documentElement;
    const button = document.querySelector("[data-theme-toggle]");
    const storageKey = "perpustakaan-theme";
    const getSavedTheme = () => {
        try { return localStorage.getItem(storageKey); } catch (error) { return null; }
    };
    const saveTheme = theme => {
        try { localStorage.setItem(storageKey, theme); } catch (error) {}
    };
    const prefersDark = window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches;
    const initialTheme = getSavedTheme() || (prefersDark ? "dark" : "light");
    const applyTheme = (theme, animate) => {
        if (animate) {
            document.body.classList.add("theme-transition");
            window.setTimeout(() => document.body.classList.remove("theme-transition"), 520);
        }
        root.classList.toggle("theme-dark", theme === "dark");
        if (button) {
            button.setAttribute("aria-pressed", theme === "dark" ? "true" : "false");
            button.setAttribute("aria-label", theme === "dark" ? "Aktifkan mode terang" : "Aktifkan mode gelap");
            const label = button.querySelector(".theme-toggle-text");
            if (label) label.textContent = theme === "dark" ? "Mode terang" : "Mode gelap";
        }
        saveTheme(theme);
    };
    applyTheme(initialTheme, false);
    if (button) {
        button.addEventListener("click", () => {
            applyTheme(root.classList.contains("theme-dark") ? "light" : "dark", true);
        });
    }
})();
</script>
</body>
</html>
