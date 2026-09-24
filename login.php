<?php
session_start();
require_once "koneksi.php";

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = "Username dan password wajib diisi!";
    } else {
        $stmt = $koneksi->prepare("SELECT id, username, password FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();

            if ($password === $admin['password']) {
                $_SESSION['admin_id']       = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Password salah!";
            }
        } else {
            $error = "Username tidak ditemukan!";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Perpustakaan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">
    <main class="login-shell">
        <section class="login-showcase">
            <div class="login-brand">
                <span class="login-brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H11v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z"></path>
                        <path d="M20 5.5A2.5 2.5 0 0 0 17.5 3H13v16h4.5a2.5 2.5 0 0 1 2.5 2.5v-16Z"></path>
                    </svg>
                </span>
                <div>
                    <strong>Perpustakaan</strong>
                    <span>Admin Workspace</span>
                </div>
            </div>

            <div class="login-showcase-content">
                <span class="login-kicker">Manajemen Koleksi</span>
                <h1 class="login-title">Kelola Perpustakaan dengan lebih rapi.</h1>
                <p class="login-description">Akses dashboard untuk menambahkan buku, menyaring katalog, dan mengunduh laporan koleksi.</p>
                <div class="login-features" aria-label="Fitur utama">
                    <span class="login-feature"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"></path></svg> Katalog Digital</span>
                    <span class="login-feature"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"></path></svg> Laporan Export</span>
                    <span class="login-feature"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"></path></svg> Pencarian Cepat</span>
                </div>
            </div>

            <div class="login-copyright">© <?= date('Y') ?> Perpustakaan • Administrator Portal</div>
        </section>

        <section class="login-panel">
<button class="theme-toggle theme-toggle-login" type="button" data-theme-toggle aria-label="Aktifkan mode gelap" aria-pressed="false">Mode gelap</button>
            <div class="login-panel-header">
                <h1>Selamat Datang</h1>
                <p>Masuk menggunakan akun administrator Anda.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert-error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form class="login-form" method="POST" action="login.php">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="login-input-wrap">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"></circle><path d="M4 21a8 8 0 0 1 16 0"></path></svg>
                        <input class="form-control" type="text" id="username" name="username" placeholder="Masukkan username" autocomplete="username" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="login-input-wrap">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path></svg>
                        <input class="form-control" type="password" id="password" name="password" placeholder="Masukkan password" autocomplete="current-password" required>
                    </div>
                </div>

                <button type="submit" class="login-submit">
                    Masuk ke Dashboard
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
                </button>
            </form>

            <div class="login-hint">
                Akun demo: <strong>admin</strong> / <strong>admin123</strong>
            </div>
        </section>
    </main>
<script>
(()=>{const r=document.documentElement,b=document.querySelector("[data-theme-toggle]"),k="perpustakaan-theme",g=()=>{try{return localStorage.getItem(k)}catch(e){}},s=t=>{try{localStorage.setItem(k,t)}catch(e){}},a=(t,x)=>{if(x){document.body.classList.add("theme-transition");setTimeout(()=>document.body.classList.remove("theme-transition"),520)}r.classList.toggle("theme-dark",t==="dark");if(b){b.textContent=t==="dark"?"Mode terang":"Mode gelap";b.setAttribute("aria-pressed",t==="dark"?"true":"false")}s(t)};a(g()||"light",false);if(b)b.addEventListener("click",()=>a(r.classList.contains("theme-dark")?"light":"dark",true))})()
</script>
</body>
</html>