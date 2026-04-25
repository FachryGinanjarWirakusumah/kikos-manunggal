document.addEventListener('DOMContentLoaded', () => {
    const isLogin = window.KINARA_CONFIG.isLogin;
    const loginModal = document.getElementById("loginModal");
    const overlay = document.getElementById('overlay');

    // === 1. LOGIKA DROPDOWN NAVBAR ===
    document.addEventListener('click', (e) => {
        const wrappers = document.querySelectorAll('.dropdown-wrapper');
        let targetWrapper = e.target.closest('.dropdown-wrapper');

        if (targetWrapper) {
            const trigger = e.target.closest('.nav-item, .login-btn');
            if (trigger) {
                e.preventDefault();
                e.stopPropagation();
                
                const isActive = targetWrapper.classList.contains('active');
                wrappers.forEach(w => w.classList.remove('active'));
                if(overlay) overlay.classList.remove('show');

                if (!isActive) {
                    targetWrapper.classList.add('active');
                    if(overlay) overlay.classList.add('show');
                }
            }
        } else {
            wrappers.forEach(w => w.classList.remove('active'));
            if(overlay) overlay.classList.remove('show');
        }
    });

    // === 2. LOGIKA MODAL LOGIN & REGISTER ===
    const openLogin = document.getElementById("openLogin");
    const closeLogin = document.getElementById("closeLogin");
    const loginFormContainer = document.getElementById("loginFormContainer");
    const registerFormContainer = document.getElementById("registerFormContainer");
    const toRegister = document.getElementById("toRegister");
    const toLogin = document.getElementById("toLogin");

    if(openLogin) openLogin.onclick = () => loginModal.style.display = "block";
    
    if(closeLogin) {
        closeLogin.onclick = () => {
            loginModal.style.display = "none";
            setTimeout(() => {
                loginFormContainer.style.display = "block";
                registerFormContainer.style.display = "none";
            }, 400);
        };
    }

    if(toRegister) toRegister.onclick = (e) => {
        e.preventDefault();
        loginFormContainer.style.display = "none";
        registerFormContainer.style.display = "block";
    };

    if(toLogin) toLogin.onclick = (e) => {
        e.preventDefault();
        registerFormContainer.style.display = "none";
        loginFormContainer.style.display = "block";
    };

    window.addEventListener("click", (e) => {
        if (e.target == loginModal) {
            loginModal.style.display = "none";
            loginFormContainer.style.display = "block";
            registerFormContainer.style.display = "none";
        }
    });

    // === 3. LOGIKA ZOOM GAMBAR PROMO ===
    const imgModal = document.getElementById("imageModal");
    const fullImg = document.getElementById("imgFull");
    const captionText = document.getElementById("imgCaption");
    const closeImg = document.querySelector(".img-close");

    document.querySelectorAll('.promo-card').forEach(card => {
        card.addEventListener('click', function() {
            const img = this.querySelector('img');
            imgModal.classList.add('show'); 
            fullImg.src = img.src;
            captionText.innerHTML = img.alt;
        });
    });

    if(closeImg) closeImg.onclick = () => imgModal.classList.remove('show');
    if(imgModal) imgModal.onclick = (e) => { if(e.target === imgModal) imgModal.classList.remove('show'); };

    // === 4. LOGIKA SLIDER ===
    function initSlider(sliderId, prevBtnId, nextBtnId) {
        const slider = document.getElementById(sliderId);
        const prev = document.getElementById(prevBtnId);
        const next = document.getElementById(nextBtnId);
        if (slider && prev && next) {
            next.onclick = () => slider.scrollLeft += slider.offsetWidth;
            prev.onclick = () => slider.scrollLeft -= slider.offsetWidth;
        }
    }
    initSlider('propertySlider', 'prevBtn', 'nextBtn');
    initSlider('promoSlider', 'promoPrev', 'promoNext');

    // === 5. FILTER TAB (IKHWAN / AKHWAT) ===
    const tabBtns = document.querySelectorAll('.tab-btn');
    const cards = document.querySelectorAll('.property-card');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-target');
            tabBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            cards.forEach(card => {
                card.style.display = (card.getAttribute('data-type') === target) ? 'block' : 'none';
            });
        });
    });
});