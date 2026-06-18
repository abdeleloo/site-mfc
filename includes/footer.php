    <footer>
        <div class="footer-inner">
            <div class="footer-top">
                <div class="footer-brand">
                    <strong>MFC — Maison de la Formation Continue</strong>
                    <p>Votre partenaire formation professionnelle depuis 20 ans.<br>Qualité, expertise, proximité.</p>
                </div>
                <div class="footer-nav">
                    <h4>Navigation</h4>
                    <ul>
                        <li><a href="<?= $prefix ?>index.php">Accueil</a></li>
                        <li><a href="<?= $prefix ?>pages/formations.php">Formations</a></li>
                        <li><a href="<?= $prefix ?>pages/formateurs.php">Formateurs</a></li>
                        <li><a href="<?= $prefix ?>pages/inscription.php">Inscription</a></li>
                    </ul>
                </div>
                <div class="footer-nav">
                    <h4>Infos</h4>
                    <ul>
                        <li><a href="<?= $prefix ?>pages/qui-sommes-nous.php">Qui sommes-nous ?</a></li>
                        <li><a href="<?= $prefix ?>pages/contact.php">Contact</a></li>
                        <li><a href="<?= $prefix ?>pages/satisfaction.php">Satisfaction</a></li>
                    </ul>
                </div>
                <div class="footer-nav">
                    <h4>Centres</h4>
                    <ul>
                        <li>OVH SAS — Roubaix</li>
                        <li>AZUR — Lille</li>
                        <li>Domaine : mfc.fr</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <span>© <?= date('Y') ?> MFC — Tous droits réservés</span>
                <div class="footer-links">
                    <a href="<?= $prefix ?>pages/mentions-legales.php">Mentions légales</a>
                    <a href="<?= $prefix ?>pages/confidentialite.php">Confidentialité</a>
                </div>
            </div>
        </div>
    </footer>
    <script>
        /* ── Header scroll effect ── */
        const header = document.getElementById('main-header');
        if (header) {
            const onScroll = () => header.classList.toggle('header-scrolled', window.scrollY > 10);
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        }

        /* ── Active nav link ── */
        const currentPath = window.location.pathname.split('/').pop();
        document.querySelectorAll('nav a').forEach(link => {
            const linkPath = (link.getAttribute('href') ?? '').split('/').pop();
            if (linkPath && linkPath === currentPath) link.classList.add('active');
        });

        /* ── Hamburger menu ── */
        const hamburger = document.getElementById('hamburger');
        const mainNav   = document.getElementById('main-nav');
        if (hamburger && mainNav) {
            hamburger.addEventListener('click', () => {
                const open = mainNav.classList.toggle('nav-open');
                hamburger.classList.toggle('open', open);
                hamburger.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
            /* Close on outside click */
            document.addEventListener('click', e => {
                if (!header.contains(e.target)) {
                    mainNav.classList.remove('nav-open');
                    hamburger.classList.remove('open');
                    hamburger.setAttribute('aria-expanded', 'false');
                }
            });
        }

        /* 