<?php
require_once "cek_login.php";

$admin_name = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
$admin_initial = strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1));

$notif = [
    'hapus_sukses' => ['success', 'Data buku berhasil dihapus.'],
    'hapus_gagal'  => ['error', 'Gagal menghapus data buku.'],
    'notfound'     => ['error', 'Data buku tidak ditemukan.'],
    'pinjaman_ada' => ['error', 'Buku tidak dapat dihapus karena memiliki riwayat peminjaman.'],
    'gagal'        => ['error', 'Permintaan tidak valid.'],
];
$status = $_GET['status'] ?? '';
$tipe = $notif[$status][0] ?? '';
$pesan = $notif[$status][1] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Buku - Perpustakaan</title>
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
                        <span class="topbar-title">Data Buku</span>
                        <span class="topbar-date">Kelola koleksi perpustakaan</span>
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
                        <h1 class="page-title">Data Buku</h1>
                        <p class="page-description">Cari, filter, edit, dan hapus seluruh data buku dari satu tabel yang rapi.</p>
                    </div>
                    <div class="heading-actions">
                        <a href="export_excel.php" class="btn btn-primary">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12M7 10l5 5 5-5M5 21h14"></path></svg>
                            Excel
                        </a>
                        <a href="export_pdf.php" target="_blank" rel="noopener" class="btn btn-danger">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12M7 10l5 5 5-5M5 21h14"></path></svg>
                            PDF
                        </a>
                        <a href="tambah.php" class="btn btn-success">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 8v8M8 12h8"></path></svg>
                            Tambah Buku
                        </a>
                    </div>
                </header>

                <?php if ($pesan): ?>
                    <div class="alert-<?= htmlspecialchars($tipe, ENT_QUOTES, 'UTF-8') ?>" role="status">
                        <?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <section class="surface-card toolbar-card" aria-label="Pencarian dan filter buku">
                    <div class="filter-bar">
                        <label class="search-control">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg>
                            <input type="text" id="searchInput" placeholder="Cari judul, penulis, kode, atau penerbit..." aria-label="Cari buku">
                        </label>
                        <label class="select-control">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M7 12h10M10 18h4"></path></svg>
                            <select id="filterKategori" aria-label="Filter kategori">
                                <option value="">Semua Kategori</option>
                            </select>
                        </label>
                    </div>
                </section>

                <div class="table-wrapper">
                    <table class="table-data" id="tabelBuku">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">Cover</th>
                                <th>Kode Buku</th>
                                <th>Judul</th>
                                <th>Penulis</th>
                                <th>Kategori</th>
                                <th class="text-center">Tahun</th>
                                <th>Penerbit</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyBuku">
                            <tr><td colspan="9" class="table-message">Memuat data buku...</td></tr>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script>
    let semuaBuku = [];

    const coverPalettes = [
        ["#2563eb", "#7c3aed"],
        ["#0f766e", "#22c55e"],
        ["#c2410c", "#f59e0b"],
        ["#be123c", "#f97316"],
        ["#1d4ed8", "#06b6d4"],
        ["#7e22ce", "#ec4899"],
        ["#4338ca", "#8b5cf6"]
    ];

    function escapeHtml(value) {
        const element = document.createElement("span");
        element.textContent = String(value ?? "");
        return element.innerHTML;
    }

    function getCoverPalette(value) {
        const text = String(value ?? "");
        let hash = 0;
        for (let index = 0; index < text.length; index += 1) {
            hash = ((hash << 5) - hash + text.charCodeAt(index)) | 0;
        }
        return coverPalettes[Math.abs(hash) % coverPalettes.length];
    }

    function coverMarkup(book) {
        const title = String(book.judul ?? "").trim();
        const author = String(book.penulis ?? "").trim();
        const words = title.split(/\s+/).filter(Boolean);
        const mark = words.slice(0, 2).map(word => word.charAt(0)).join("").toUpperCase() || "BK";
        const [start, end] = getCoverPalette(title || book.kode_buku);

        return `
            <div class="book-cover" style="--cover-start: ${start}; --cover-end: ${end};" title="${escapeHtml(title)}">
                <span class="book-cover-mark">${escapeHtml(mark)}</span>
                <span class="book-cover-title">${escapeHtml(title)}</span>
                <span class="book-cover-author">${escapeHtml(author)}</span>
            </div>
        `;
    }

    function tampilkanData(data) {
        const tbody = document.getElementById("tbodyBuku");

        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="table-message">Tidak ada data buku yang sesuai.</td></tr>';
            return;
        }

        let html = "";
        data.forEach((buku, index) => {
            html += `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td class="cover-cell">${coverMarkup(buku)}</td>
                    <td><span class="badge">${buku.kode_buku}</span></td>
                    <td><b>${buku.judul}</b></td>
                    <td>${buku.penulis}</td>
                    <td>${buku.kategori}</td>
                    <td class="text-center">${buku.tahun_terbit}</td>
                    <td>${buku.penerbit}</td>
                    <td class="text-center">
                        <a href="edit.php?id=${buku.id}" class="btn-action edit" title="Edit buku" aria-label="Edit ${buku.judul}">&#9998;</a>
                        <a href="hapus.php?id=${buku.id}" class="btn-action delete" title="Hapus buku" aria-label="Hapus ${buku.judul}" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">&#128465;</a>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }

    function isiFilterKategori(data) {
        const select = document.getElementById("filterKategori");
        const kategoriSet = [...new Set(data.map(buku => buku.kategori))].sort();

        kategoriSet.forEach(kategori => {
            const option = document.createElement("option");
            option.value = kategori;
            option.textContent = kategori;
            select.appendChild(option);
        });
    }

    function loadData() {
        fetch("data_json.php")
            .then(response => {
                if (!response.ok) {
                    throw new Error("Gagal memuat data");
                }
                return response.json();
            })
            .then(data => {
                semuaBuku = data;
                tampilkanData(data);
                isiFilterKategori(data);
            })
            .catch(error => {
                console.error(error);
                document.getElementById("tbodyBuku").innerHTML =
                    `<tr><td colspan="9" class="table-message text-error">Gagal memuat data: ${error.message}</td></tr>`;
            });
    }

    function filterData() {
        const keyword = document.getElementById("searchInput").value.toLowerCase();
        const kategori = document.getElementById("filterKategori").value;

        const filtered = semuaBuku.filter(buku => {
            const cocokKeyword =
                buku.judul.toLowerCase().includes(keyword) ||
                buku.penulis.toLowerCase().includes(keyword) ||
                buku.kode_buku.toLowerCase().includes(keyword) ||
                buku.penerbit.toLowerCase().includes(keyword);

            const cocokKategori = kategori === "" || buku.kategori === kategori;
            return cocokKeyword && cocokKategori;
        });

        tampilkanData(filtered);
    }

    document.addEventListener("DOMContentLoaded", loadData);
    document.getElementById("searchInput").addEventListener("input", filterData);
    document.getElementById("filterKategori").addEventListener("change", filterData);
    </script>
<script>
(()=>{const r=document.documentElement,b=document.querySelector("[data-theme-toggle]"),k="perpustakaan-theme",g=()=>{try{return localStorage.getItem(k)}catch(e){}},s=t=>{try{localStorage.setItem(k,t)}catch(e){}},a=(t,x)=>{if(x){document.body.classList.add("theme-transition");setTimeout(()=>document.body.classList.remove("theme-transition"),520)}r.classList.toggle("theme-dark",t==="dark");if(b){b.textContent=t==="dark"?"Mode terang":"Mode gelap";b.setAttribute("aria-pressed",t==="dark"?"true":"false")}s(t)};a(g()||"light",false);if(b)b.addEventListener("click",()=>a(r.classList.contains("theme-dark")?"light":"dark",true))})()
</script>
</body>
</html>