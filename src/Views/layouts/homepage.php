<?php /** @var string $content */ ?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>JIOJIAN - 票務平台</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'manrope': ['Manrope', 'sans-serif'],
                        'inter':   ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body class="bg-[#1c1c1c] font-inter">

<!-- ── Navbar ────────────────────────────────────────────── -->
<header id="main-nav"
        class="fixed top-0 left-0 right-0 z-50 h-[102px] transition-all duration-300"
        style="background: linear-gradient(to bottom, rgba(0,0,0,0.65), transparent);">
    <div class="relative flex items-center justify-between h-full max-w-[1440px] mx-auto">

        <!-- Logo -->
        <a href="/" class="flex items-center gap-3">
            <div class="w-10 h-10 bg-[#ea6d4a] rounded-[10px] flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                </svg>
            </div>
            <span class="font-manrope font-semibold text-white text-base tracking-[0.8px]">JIOJIAN</span>
        </a>

        <!-- Nav links -->
        <nav class="absolute left-1/2 -translate-x-1/2 flex gap-8 items-center">
            <a href="/"  class="font-manrope text-white/90 text-base hover:text-white transition-colors">首頁</a>
            <a href="/"  class="font-manrope text-white/90 text-base hover:text-white transition-colors">演唱會</a>
            <a href="#"  class="font-manrope text-white/90 text-base hover:text-white transition-colors">音樂節</a>
            <a href="#"  class="font-manrope text-white/90 text-base hover:text-white transition-colors">即將開售</a>
        </nav>

        <!-- Right actions -->
        <div class="flex items-center gap-4">
            <button aria-label="搜尋" class="text-white hover:text-white/70 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/>
                </svg>
            </button>
            <button aria-label="購物車" class="text-white hover:text-white/70 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 7H4l1-7z"/>
                </svg>
            </button>
            <button aria-label="帳號" class="text-white hover:text-white/70 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </button>

            <?php $u = current_user(); ?>
            <?php if ($u): ?>
                <span class="text-white/80 text-sm">Hi, <?= e($u['name']) ?></span>
                <?php if ($u['role'] === 'admin'): ?>
                    <a href="/admin/concerts"
                       class="text-amber-300 text-sm font-manrope hover:text-amber-200 transition-colors">後台</a>
                <?php endif; ?>
                <form method="post" action="/logout" class="inline">
                    <?= csrf_field() ?>
                    <button class="bg-[#ea6d4a] text-white rounded-full w-[80px] h-10 font-manrope font-medium text-base hover:bg-[#d4603d] transition-colors">
                        登出
                    </button>
                </form>
            <?php else: ?>
                <a href="/login"
                   class="bg-[#ea6d4a] text-white rounded-full w-[80px] h-10 flex items-center justify-center font-manrope font-medium text-base hover:bg-[#d4603d] transition-colors">
                    登入
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Flash messages -->
<?php $flashes = flash_all_pull(); ?>
<?php if (!empty($flashes)): ?>
    <div id="flash-container" class="fixed bottom-8 right-8 z-[60] flex flex-col gap-2">
        <?php foreach ($flashes as $key => $message): ?>
            <?php $cls = $key === 'error' ? 'bg-red-600' : ($key === 'success' ? 'bg-emerald-600' : 'bg-amber-500'); ?>
            <div class="flash-msg <?= $cls ?> text-white rounded-lg px-5 py-3 text-sm shadow-lg
                        transition-all duration-500 opacity-100 translate-y-0">
                <?= e($message) ?>
            </div>
        <?php endforeach; ?>
    </div>
    <script>
        (function () {
            var msgs = document.querySelectorAll('.flash-msg');
            msgs.forEach(function (el) {
                setTimeout(function () {
                    el.style.opacity   = '0';
                    el.style.transform = 'translateY(8px)';
                    setTimeout(function () { el.remove(); }, 500);
                }, 5000);
            });
        })();
    </script>
<?php endif; ?>

<!-- Page content (views inject their own sections, including the hero) -->
<main><?= $content ?></main>

<!-- ── Footer ────────────────────────────────────────────── -->
<footer class="bg-[#2b2b2b] border-t border-white/10">
    <div class="max-w-[1440px] mx-auto px-8 py-16">
        <div class="grid grid-cols-4 gap-12">

            <!-- Brand + description + social -->
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-[#ea6d4a] rounded-[10px] flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                        </svg>
                    </div>
                    <span class="font-manrope text-white text-xl tracking-[1px]">JIOJIAN</span>
                </div>
                <p class="text-white/60 text-sm font-inter leading-5 max-w-[384px]">
                    台灣最大的線上售票平台，提供演唱會、音樂節、戲劇、體育賽事等各類活動票券。
                </p>
                <div class="flex gap-4 mt-6">
                    <a href="#" aria-label="Facebook" class="text-white/60 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="GitHub" class="text-white/60 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                        </svg>
                    </a>
                    <a href="#" aria-label="Instagram" class="text-white/60 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- 分類 -->
            <div>
                <h3 class="font-inter font-medium text-white text-lg mb-4">分類</h3>
                <ul class="space-y-3">
                    <li><a href="#" class="text-white/60 text-sm font-inter hover:text-white/80 transition-colors">演唱會</a></li>
                    <li><a href="#" class="text-white/60 text-sm font-inter hover:text-white/80 transition-colors">音樂節</a></li>
                    <li><a href="#" class="text-white/60 text-sm font-inter hover:text-white/80 transition-colors">戲劇</a></li>
                    <li><a href="#" class="text-white/60 text-sm font-inter hover:text-white/80 transition-colors">體育</a></li>
                </ul>
            </div>

            <!-- 服務 -->
            <div>
                <h3 class="font-inter font-medium text-white text-lg mb-4">服務</h3>
                <ul class="space-y-3">
                    <li><a href="#" class="text-white/60 text-sm font-inter hover:text-white/80 transition-colors">幫助中心</a></li>
                    <li><a href="#" class="text-white/60 text-sm font-inter hover:text-white/80 transition-colors">聯絡我們</a></li>
                    <li><a href="#" class="text-white/60 text-sm font-inter hover:text-white/80 transition-colors">退票政策</a></li>
                    <li><a href="#" class="text-white/60 text-sm font-inter hover:text-white/80 transition-colors">常見問題</a></li>
                </ul>
            </div>

            <!-- 公司 -->
            <div>
                <h3 class="font-inter font-medium text-white text-lg mb-4">公司</h3>
                <ul class="space-y-3">
                    <li><a href="#" class="text-white/60 text-sm font-inter hover:text-white/80 transition-colors">關於我們</a></li>
                    <li><a href="#" class="text-white/60 text-sm font-inter hover:text-white/80 transition-colors">加入我們</a></li>
                    <li><a href="#" class="text-white/60 text-sm font-inter hover:text-white/80 transition-colors">隱私權政策</a></li>
                    <li><a href="#" class="text-white/60 text-sm font-inter hover:text-white/80 transition-colors">服務條款</a></li>
                </ul>
            </div>
        </div>

        <!-- Copyright bar -->
        <div class="border-t border-white/10 mt-12 pt-8 flex items-center justify-between">
            <p class="text-white/40 text-sm font-inter">© <?= date('Y') ?> LIVE STAGE. All rights reserved.</p>
            <div class="flex gap-6">
                <a href="#" class="text-white/40 text-sm font-inter hover:text-white/60 transition-colors">隱私權</a>
                <a href="#" class="text-white/40 text-sm font-inter hover:text-white/60 transition-colors">條款</a>
                <a href="#" class="text-white/40 text-sm font-inter hover:text-white/60 transition-colors">Cookie</a>
            </div>
        </div>
    </div>
</footer>

<script>
    (function () {
        var nav = document.getElementById('main-nav');
        function updateNav() {
            if (window.scrollY > 525) {
                nav.style.background = '#1c1c1c';
            } else {
                nav.style.background = 'linear-gradient(to bottom, rgba(0,0,0,0.65), transparent)';
            }
        }
        window.addEventListener('scroll', updateNav, { passive: true });
        updateNav();
    })();
</script>

</body>
</html>
