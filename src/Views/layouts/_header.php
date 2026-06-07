<?php
/** @var bool $navTransparent */
$navTransparent = $navTransparent ?? false;
$u = current_user();

$navBarHeight = $navTransparent ? 'h-[102px]' : 'h-[64px]';
$navInitBg  = $navTransparent
    ? 'background: linear-gradient(to bottom, rgba(0,0,0,0.65), transparent);'
    : 'background: #E3E3E3; border-bottom: 1px solid rgba(103,99,99,0.25);';
$lightClass = $navTransparent ? '' : 'nav-light';
?>
<style>
    #main-nav .nav-link   { color: rgba(255,255,255,0.9); }
    #main-nav .nav-link:hover { color: #fff; }
    #main-nav .nav-logo   { color: #fff; }
    #main-nav .nav-icon   { color: rgba(255,255,255,0.9); }
    #main-nav .nav-icon:hover { color: rgba(255,255,255,0.7); }
    #main-nav .nav-user   { color: rgba(255,255,255,0.8); }
    #main-nav .nav-admin  { color: #fcd34d; }

    #main-nav.nav-light .nav-link  { color: #334155; }
    #main-nav.nav-light .nav-link:hover { color: #0f172a; }
    #main-nav.nav-light .nav-logo  { color: #0f172a; }
    #main-nav.nav-light .nav-icon  { color: #475569; }
    #main-nav.nav-light .nav-icon:hover { color: #0f172a; }
    #main-nav.nav-light .nav-user  { color: #334155; }
    #main-nav.nav-light .nav-admin { color: #d97706; }

    /* Mobile menu */
    #mobile-menu { display: none; }
    #mobile-menu.open { display: block; }
    @media (min-width: 768px) { #mobile-menu { display: none !important; } }

    #main-nav .mobile-link { color: rgba(255,255,255,0.9); border-color: rgba(255,255,255,0.1); }
    #main-nav .mobile-link:hover { color: #fff; }
    #main-nav.nav-light .mobile-link { color: #334155; border-color: rgba(103,99,99,0.15); }
    #main-nav.nav-light .mobile-link:hover { color: #0f172a; }

    #main-nav .mobile-user  { color: rgba(255,255,255,0.8); }
    #main-nav .mobile-admin { color: #fcd34d; }
    #main-nav.nav-light .mobile-user  { color: #334155; }
    #main-nav.nav-light .mobile-admin { color: #d97706; }

    /* Inline search input */
    #nav-search-input { background: rgba(255,255,255,0.15); color: #fff; }
    #nav-search-input::placeholder { color: rgba(255,255,255,0.5); }
    #main-nav.nav-light #nav-search-input { background: rgba(0,0,0,0.07); color: #1a1a1a; }
    #main-nav.nav-light #nav-search-input::placeholder { color: #94a3b8; }
</style>

<header id="main-nav"
        class="fixed top-0 left-0 right-0 z-50 <?= $lightClass ?>"
        style="transition: background 300ms ease; <?= $navInitBg ?>">

    <!-- Main bar -->
    <div class="relative flex items-center justify-between <?= $navBarHeight ?> max-w-[1440px] mx-auto px-4 md:px-8 gap-4">

        <a href="/" class="flex items-center gap-3 shrink-0">
            <div class="w-10 h-10 bg-[#ea6d4a] rounded-[10px] flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                </svg>
            </div>
            <span class="nav-logo font-manrope font-semibold text-base tracking-[0.8px]">JIOJIAN</span>
        </a>

        <!-- Desktop nav -->
        <nav id="nav-links" class="hidden md:flex absolute left-1/2 -translate-x-1/2 gap-8 items-center">
            <a href="/"         class="nav-link font-manrope text-base transition-colors">首頁</a>
            <a href="/concerts" class="nav-link font-manrope text-base transition-colors">演唱會</a>
            <a href="/upcoming" class="nav-link font-manrope text-base transition-colors">即將開賣</a>
        </nav>

        <!-- Inline search form (hidden by default) -->
        <form id="nav-search-form" action="/concerts" method="get"
              style="display:none; flex:1;" class="items-center">
            <div class="relative w-full">
                <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                    <svg class="w-4 h-4 text-[#ea6d4a]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/>
                    </svg>
                </div>
                <input id="nav-search-input"
                       type="search" name="q"
                       placeholder="搜尋演唱會、場館、藝人..."
                       autocomplete="off"
                       class="w-full h-9 rounded-full pl-9 pr-4 text-sm font-inter focus:outline-none transition-colors"
                       style="border: 1px solid rgba(255,255,255,0.2);">
            </div>
        </form>

        <!-- Desktop right actions -->
        <div id="nav-actions-desktop" class="hidden md:flex items-center gap-4">
            <button aria-label="搜尋" class="nav-search-btn nav-icon transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/>
                </svg>
            </button>

            <?php if ($u): ?>
                <span class="nav-user text-sm">Hi, <?= e($u['name']) ?></span>
                <?php if ($u['role'] === 'admin'): ?>
                    <a href="/admin/concerts" class="nav-admin text-sm font-manrope transition-colors">後台</a>
                <?php endif; ?>
                <a href="/my/orders" class="nav-user text-sm font-manrope transition-colors">我的訂單</a>
                <form method="post" action="/logout" class="inline">
                    <?= csrf_field() ?>
                    <button class="bg-[#ea6d4a] text-white rounded-full w-[80px] h-10 font-manrope font-medium text-base hover:bg-[#d4603d] transition-colors">登出</button>
                </form>
            <?php else: ?>
                <a href="/login" class="bg-[#ea6d4a] text-white rounded-full w-[80px] h-10 flex items-center justify-center font-manrope font-medium text-base hover:bg-[#d4603d] transition-colors">登入</a>
            <?php endif; ?>
        </div>

        <!-- Mobile: search + hamburger -->
        <div id="nav-actions-mobile" class="flex md:hidden items-center gap-3">
            <button aria-label="搜尋" class="nav-search-btn nav-icon transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/>
                </svg>
            </button>
            <button id="hamburger-btn" aria-label="開關選單" class="nav-icon transition-colors p-1">
                <svg id="hamburger-icon" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg id="close-icon" class="w-6 h-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Search close button (hidden by default) -->
        <button id="nav-search-close" aria-label="關閉搜尋"
                class="nav-icon shrink-0 transition-colors"
                style="display:none; background:none; border:none; cursor:pointer; padding:4px;">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Mobile menu panel -->
    <div id="mobile-menu" class="px-4 pb-5">
        <nav class="flex flex-col">
            <a href="/"         class="mobile-link font-manrope text-base py-3 border-b transition-colors">首頁</a>
            <a href="/concerts" class="mobile-link font-manrope text-base py-3 border-b transition-colors">演唱會</a>
            <a href="/upcoming" class="mobile-link font-manrope text-base py-3 border-b transition-colors">即將開賣</a>
        </nav>
        <div class="flex flex-col gap-3 pt-4">
            <?php if ($u): ?>
                <span class="mobile-user text-sm font-manrope">Hi, <?= e($u['name']) ?></span>
                <?php if ($u['role'] === 'admin'): ?>
                    <a href="/admin/concerts" class="mobile-admin text-sm font-manrope">後台管理</a>
                <?php endif; ?>
                <a href="/my/orders" class="mobile-user text-sm font-manrope">我的訂單</a>
                <form method="post" action="/logout">
                    <?= csrf_field() ?>
                    <button class="bg-[#ea6d4a] text-white rounded-full w-full h-10 font-manrope font-medium text-base hover:bg-[#d4603d] transition-colors">登出</button>
                </form>
            <?php else: ?>
                <a href="/login" class="bg-[#ea6d4a] text-white rounded-full h-10 flex items-center justify-center font-manrope font-medium text-base hover:bg-[#d4603d] transition-colors">登入</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<script>
(function () {
    var hamburgerBtn  = document.getElementById('hamburger-btn');
    var menu          = document.getElementById('mobile-menu');
    var nav           = document.getElementById('main-nav');
    var hamburgerIcon = document.getElementById('hamburger-icon');
    var closeIcon     = document.getElementById('close-icon');

    hamburgerBtn.addEventListener('click', function () {
        var isOpen = menu.classList.toggle('open');
        hamburgerIcon.classList.toggle('hidden', isOpen);
        closeIcon.classList.toggle('hidden', !isOpen);
        <?php if ($navTransparent): ?>
        if (window.scrollY <= 525) {
            nav.style.background = isOpen
                ? 'rgba(0,0,0,0.92)'
                : 'linear-gradient(to bottom, rgba(0,0,0,0.65), transparent)';
            nav.style.borderBottom = '';
        }
        <?php endif; ?>
    });

    // Inline search toggle
    var navLinks          = document.getElementById('nav-links');
    var navActionsDesktop = document.getElementById('nav-actions-desktop');
    var navActionsMobile  = document.getElementById('nav-actions-mobile');
    var searchForm        = document.getElementById('nav-search-form');
    var searchInput       = document.getElementById('nav-search-input');
    var searchClose       = document.getElementById('nav-search-close');

    function openSearch() {
        navLinks.style.display          = 'none';
        navActionsDesktop.style.display = 'none';
        navActionsMobile.style.display  = 'none';
        searchForm.style.display        = 'flex';
        searchClose.style.display       = 'flex';
        setTimeout(function () { searchInput.focus(); }, 30);
    }

    function closeSearch() {
        navLinks.style.display          = '';
        navActionsDesktop.style.display = '';
        navActionsMobile.style.display  = '';
        searchForm.style.display        = 'none';
        searchClose.style.display       = 'none';
        searchInput.value               = '';
    }

    document.querySelectorAll('.nav-search-btn').forEach(function (btn) {
        btn.addEventListener('click', openSearch);
    });

    searchClose.addEventListener('click', closeSearch);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeSearch();
    });
})();
</script>

<?php if ($navTransparent): ?>
<script>
    (function () {
        var nav  = document.getElementById('main-nav');
        var menu = document.getElementById('mobile-menu');
        function updateNav() {
            if (window.scrollY > 525) {
                nav.style.background   = '#E3E3E3';
                nav.style.borderBottom = '1px solid rgba(103,99,99,0.25)';
                nav.classList.add('nav-light');
            } else if (!menu.classList.contains('open')) {
                nav.style.background   = 'linear-gradient(to bottom, rgba(0,0,0,0.65), transparent)';
                nav.style.borderBottom = '';
                nav.classList.remove('nav-light');
            }
        }
        window.addEventListener('scroll', updateNav, { passive: true });
    })();
</script>
<?php endif; ?>
