<nav class="navbar">
    <div class="nav-container">
        <div class="logo">KINARA</div>

        <ul class="nav-links">
            <li>
                <a href="index.php" class="<?= ($current_page == 'index.php') ? 'active' : ''; ?>">Home</a>
            </li>

            <li class="dropdown-wrapper">
                <div class="nav-item <?= ($current_page == 'tentang_kami.php') ? 'active' : ''; ?>">
                    Tentang Kinara <i class="fas fa-chevron-down"></i>
                </div>
                
                <div class="dropdown-content menu-tentang">
                    <a href="tentang_kami.php" class="dropdown-link <?= ($current_page == 'tentang_kami.php') ? 'active' : ''; ?>">
                        <div class="dropdown-item">
                            <div class="icon-box"><i class="fas fa-users"></i></div>
                            <div class="text">
                                <strong>Tentang Kami</strong>
                                <p>Membangun solusi hunian yang lebih mudah diakses.</p>
                            </div>
                        </div>
                    </a>
                </div>
            </li>

            <li class="dropdown-wrapper">
                <div class="nav-item <?= ($current_page == 'aturan.php' || $current_page == 'cek_kamar.php') ? 'active' : ''; ?>">
                    Cek Unit Kamar <i class="fas fa-chevron-down"></i>
                </div>
                
                <div class="dropdown-content menu-tentang">
                    <a href="aturan.php" class="dropdown-link <?= ($current_page == 'aturan.php') ? 'active' : ''; ?>">
                        <div class="dropdown-item">
                            <div class="icon-box"><i class="fas fa-clipboard-list"></i></div>
                            <div class="text">
                                <strong>Aturan Kinara Kost</strong>
                                <p>Penting untuk kalian ketahui ya</p>
                            </div>
                        </div>
                    </a>

                    <a href="cek_kamar.php" class="dropdown-link <?= ($current_page == 'cek_kamar.php') ? 'active' : ''; ?>">
                        <div class="dropdown-item">
                            <div class="icon-box"><i class="fas fa-bed"></i></div>
                            <div class="text">
                                <strong>Cek Kamar</strong>
                                <p>Ayo cek kamar yang masih tersedia.</p>
                            </div>
                        </div>
                    </a>
                </div>
            </li>
        </ul>

        <div class="nav-actions">
            <div class="dropdown-wrapper">
                <div class="nav-item">
                    <img src="https://flagcdn.com/w40/id.png" class="flag-circle" alt="ID"> 
                    <span>ID</span> <i class="fas fa-chevron-down"></i>
                </div>

                <div class="dropdown-content lang-dropdown">
                    <h4 class="dropdown-title">Pilih Bahasa</h4>
                    
                    <div class="dropdown-item lang-option active-lang">
                        <img src="https://flagcdn.com/w40/id.png" class="flag-circle">
                        <span class="lang-text">Bahasa Indonesia</span>
                        <i class="fas fa-check check-icon"></i>
                    </div>

                    <div class="dropdown-item lang-option">
                        <img src="https://flagcdn.com/w40/us.png" class="flag-circle">
                        <span class="lang-text">Bahasa Inggris</span>
                    </div>
                </div>
            </div>

            <!-- LOGIN BUTTON -->
            <?php if(isset($_SESSION['login'])): ?>
                <div class="dropdown-wrapper">
                    <button class="login-btn">
                        <i class="far fa-user"></i> Halo, <?= $_SESSION['nama']; ?>
                    </button>
                    <div class="dropdown-content">
                        <a href="logout.php" style="color: red; padding: 10px; display: block; text-decoration: none;">
                            <i class="fas fa-sign-out-alt"></i> Keluar
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <button class="login-btn" id="openLogin">
                    <i class="far fa-user"></i> Masuk / Daftar
                </button>
            <?php endif; ?>
        </div>
    </div>
</nav>