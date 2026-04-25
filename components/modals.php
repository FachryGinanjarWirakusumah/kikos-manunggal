<!-- LOGIN / REGISTER MODAL -->
<div class="login-modal" id="loginModal">
    <div class="login-box">
        <span class="close-btn" id="closeLogin">&times;</span>

        <!-- Form Login -->
        <div id="loginFormContainer">
            <h3>Selamat Datang</h3>
            <p class="subtitle">Masuk untuk mulai mencari hunian impianmu.</p>

            <form action="login_process.php" method="POST">
                <div class="form-group">
                    <label>Email atau Nomor HP</label>
                    <input type="text" name="username" class="form-input" placeholder="contoh: email@anda.com" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-input" placeholder="Masukkan password" required>
                </div>
                <button type="submit" class="btn-submit">Masuk</button>
            </form>
            <p class="signup-text">
                Belum punya akun? <a href="javascript:void(0)" id="toRegister">Daftar Sekarang</a>
            </p>
        </div>

        <!-- Form Register -->
        <div id="registerFormContainer" style="display: none;">
            <h3>Daftar Akun Baru</h3>
            <p class="subtitle">Lengkapi data diri untuk bergabung dengan Kinara.</p>

            <form action="register_process.php" method="POST">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" class="form-input" placeholder="Nama sesuai KTP" required>
                </div>
                <div class="form-group">
                    <label>Email / No. HP</label>
                    <input type="text" name="kontak" class="form-input" placeholder="Email atau WhatsApp aktif" required>
                </div>
                <div class="form-group">
                    <label>Buat Password</label>
                    <input type="password" name="password" class="form-input" placeholder="Minimal 8 karakter" required>
                </div>
                <button type="submit" class="btn-submit">Daftar User</button>
            </form>
            <p class="signup-text">
                Sudah punya akun? <a href="javascript:void(0)" id="toLogin">Masuk di sini</a>
            </p>
        </div>
    </div>
</div>

<!-- IMAGE ZOOM MODAL -->
<div id="imageModal" class="img-zoom-modal">
    <span class="img-close">&times;</span>
    <img class="img-modal-content" id="imgFull">
    <div id="imgCaption"></div>
</div>