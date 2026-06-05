<?php /** @var array $concerts */ ?>

<!-- ── Hero Carousel ──────────────────────────────────────── -->
<?php
$slides = array_values($concerts);
$hasSlides = !empty($slides);
?>
<section class="relative overflow-hidden bg-[#1c1c1c]" style="min-height:300px;" id="hero-section"
         data-slides='<?= json_encode(array_map(fn($c) => [
             'poster_url'   => $c['poster_url'] ?? '',
             'title'        => $c['title'],
             'venue'        => $c['venue'],
             'performed_at' => date('Y.m.d', strtotime($c['performed_at'])),
             'id'           => (int)$c['id'],
         ], $slides), JSON_HEX_TAG | JSON_HEX_QUOT) ?>'>

    <!-- Slide backgrounds -->
    <div id="hero-bg" class="absolute inset-0">
        <?php foreach ($slides as $i => $c): ?>
            <div class="hero-slide absolute inset-0 transition-opacity duration-700 <?= $i === 0 ? 'opacity-100' : 'opacity-0' ?>">
                <?php if (!empty($c['poster_url'])): ?>
                    <img src="<?= e($c['poster_url']) ?>"
                         class="w-full h-full object-contain opacity-30"
                         alt="">
                <?php else: ?>
                    <div class="w-full h-full bg-gradient-to-br from-[#2a1a0e] via-[#1c1c1c] to-[#0e0e1e]"></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <?php if (!$hasSlides): ?>
            <div class="absolute inset-0 bg-gradient-to-br from-[#2a1a0e] via-[#1c1c1c] to-[#0e0e1e]"></div>
        <?php endif; ?>
    </div>

    <!-- Bottom-to-top dark gradient for text readability -->
    <div class="absolute inset-0 pointer-events-none"
         style="background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.4) 50%, transparent 100%);"></div>

    <!-- Prev arrow -->
    <?php if (count($slides) > 1): ?>
    <button id="hero-prev" aria-label="上一個"
            class="absolute left-8 top-1/2 -translate-y-1/2 text-white/50 hover:text-white transition-colors z-10">
        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>

    <!-- Next arrow -->
    <button id="hero-next" aria-label="下一個"
            class="absolute right-8 top-1/2 -translate-y-1/2 text-white/50 hover:text-white transition-colors z-10">
        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
    </button>
    <?php endif; ?>

    <!-- Concert info overlay -->
    <?php if ($hasSlides): ?>
        <div class="absolute bottom-[88px] left-[100px] max-w-[900px]">
            <div id="hero-meta" class="flex items-center gap-8 text-white/80 text-base font-inter mb-4 transition-opacity duration-300">
                <span class="flex items-center gap-3">
                    <svg class="w-6 h-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span id="hero-venue"><?= e($slides[0]['venue']) ?></span>
                </span>
                <span class="flex items-center gap-3">
                    <svg class="w-6 h-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8"  y1="2" x2="8"  y2="6"/>
                        <line x1="3"  y1="10" x2="21" y2="10"/>
                    </svg>
                    <span id="hero-date"><?= e(date('Y.m.d', strtotime($slides[0]['performed_at']))) ?></span>
                </span>
            </div>
            <a id="hero-link" href="/concerts/<?= (int)$slides[0]['id'] ?>">
                <h1 id="hero-title"
                    class="font-manrope font-bold text-[#a63f21] leading-none tracking-[-2.4px] transition-opacity duration-300 hover:opacity-80"
                    style="font-size: clamp(48px, 5.5vw, 96px);">
                    <?= e($slides[0]['title']) ?>
                </h1>
            </a>
        </div>
    <?php else: ?>
        <div class="absolute bottom-[88px] left-[100px]">
            <p class="font-manrope font-bold text-white/30 text-5xl">目前尚無演唱會</p>
        </div>
    <?php endif; ?>

    <!-- Pagination dots -->
    <?php if (count($slides) > 1): ?>
        <div id="hero-dots" class="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-2">
            <?php foreach ($slides as $i => $c): ?>
                <button aria-label="第<?= $i + 1 ?>場" data-index="<?= $i ?>"
                        class="hero-dot h-2.5 rounded-full transition-all duration-300 <?= $i === 0 ? 'w-6 bg-[#ea6d4a]' : 'w-2.5 bg-white/30 hover:bg-white/50' ?>">
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<script>
(function () {
    var section   = document.getElementById('hero-section');
    if (!section) return;

    var slideData = JSON.parse(section.dataset.slides || '[]');
    var bgSlides  = section.querySelectorAll('.hero-slide');
    var allImgs   = section.querySelectorAll('.hero-slide img');
    var dots      = section.querySelectorAll('.hero-dot');
    var titleEl   = document.getElementById('hero-title');
    var venueEl   = document.getElementById('hero-venue');
    var dateEl    = document.getElementById('hero-date');
    var linkEl    = document.getElementById('hero-link');
    var metaEl    = document.getElementById('hero-meta');
    var current   = 0;
    var timer;
    var ratios    = {};

    // ── Height auto-sizing ────────────────────────────────────
    function applyHeight() {
        var r = ratios[current];
        if (r) section.style.height = Math.round(section.offsetWidth / r) + 'px';
    }

    allImgs.forEach(function (img, idx) {
        function measure() {
            if (img.naturalWidth && img.naturalHeight) {
                ratios[idx] = img.naturalWidth / img.naturalHeight;
                if (idx === current) applyHeight();
            }
        }
        img.complete ? measure() : img.addEventListener('load', measure);
    });

    window.addEventListener('resize', applyHeight, { passive: true });

    // ── Carousel (only when > 1 slide) ───────────────────────
    if (slideData.length <= 1) return;

    function goTo(idx) {
        bgSlides[current].classList.replace('opacity-100', 'opacity-0');
        dots[current].classList.replace('w-6', 'w-2.5');
        dots[current].classList.replace('bg-[#ea6d4a]', 'bg-white/30');

        current = (idx + slideData.length) % slideData.length;
        applyHeight();

        bgSlides[current].classList.replace('opacity-0', 'opacity-100');
        dots[current].classList.replace('w-2.5', 'w-6');
        dots[current].classList.replace('bg-white/30', 'bg-[#ea6d4a]');

        var s = slideData[current];
        titleEl.style.opacity = '0';
        metaEl.style.opacity  = '0';
        setTimeout(function () {
            titleEl.textContent   = s.title;
            venueEl.textContent   = s.venue;
            dateEl.textContent    = s.performed_at;
            linkEl.href           = '/concerts/' + s.id;
            titleEl.style.opacity = '1';
            metaEl.style.opacity  = '1';
        }, 150);
    }

    function startTimer() {
        clearInterval(timer);
        timer = setInterval(function () { goTo(current + 1); }, 5000);
    }

    document.getElementById('hero-prev').addEventListener('click', function () {
        goTo(current - 1); startTimer();
    });
    document.getElementById('hero-next').addEventListener('click', function () {
        goTo(current + 1); startTimer();
    });
    dots.forEach(function (dot) {
        dot.addEventListener('click', function () {
            goTo(parseInt(this.dataset.index)); startTimer();
        });
    });

    section.addEventListener('mouseenter', function () { clearInterval(timer); });
    section.addEventListener('mouseleave', startTimer);

    startTimer();
})();
</script>

<!-- ── Light section: search + events ────────────────────── -->
<section class="bg-[#e3e3e3] pb-20">

    <!-- Search bar -->
    <div class="flex justify-center pt-[51px] px-[180px]">
        <div class="relative w-[509px]">
            <div class="absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none">
                <svg class="w-5 h-5 text-[#ea6d4a]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/>
                </svg>
            </div>
            <!-- vertical separator -->
            <div class="absolute left-[46px] top-1/2 -translate-y-1/2 h-5 w-px bg-slate-300 pointer-events-none"></div>
            <input id="concert-search" type="search"
                   placeholder="搜尋演唱會、場館、藝人..."
                   class="w-full h-12 bg-[#f6f6f6] rounded-full pl-[60px] pr-5 text-[#1a1a1a] text-sm font-inter placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#ea6d4a]/40 shadow-sm">
        </div>
    </div>

    <!-- Events grid -->
    <div class="max-w-[1440px] mx-auto px-[180px] mt-12">

        <!-- Section header -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="font-manrope font-semibold text-[#1a1a1a] text-2xl">節目資訊</h2>
            <a href="/"
               class="bg-[#ea6d4a] text-white text-sm font-manrope font-semibold rounded-full px-5 py-2 hover:bg-[#d4603d] transition-colors">
                更多 &nbsp;→
            </a>
        </div>

        <?php if (empty($concerts)): ?>
            <div class="rounded-lg bg-white/70 px-5 py-4 text-sm text-[#4b4b4b] font-inter">
                目前沒有販售中的演唱會，敬請期待。
            </div>
        <?php else: ?>
            <p id="no-results" class="hidden rounded-lg bg-white/70 px-5 py-4 text-sm text-[#4b4b4b] font-inter">
                找不到符合的演唱會。
            </p>
            <div id="concerts-grid" class="grid grid-cols-3 gap-[14px]">
                <?php foreach ($concerts as $c): ?>
                    <a href="/concerts/<?= (int) $c['id'] ?>"
                       data-title="<?= mb_strtolower(e($c['title'])) ?>"
                       data-venue="<?= mb_strtolower(e($c['venue'])) ?>"
                       class="concert-card hover:bg-white rounded-[5px] overflow-hidden flex flex-col
                              hover:shadow-lg transition-all duration-200">

                        <!-- Poster image -->
                        <div class="shrink-0 overflow-hidden" style="aspect-ratio: 850/370;">
                            <?php if (!empty($c['poster_url'])): ?>
                                <img src="<?= e($c['poster_url']) ?>"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                     alt="<?= e($c['title']) ?>">
                            <?php else: ?>
                                <div class="w-full h-full bg-slate-800 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-white/20" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Info block -->
                        <div class="flex flex-col justify-between flex-1 pl-[15px] pr-4 pt-3 pb-[24px]">
                            <div>
                                <h3 class="font-manrope font-bold text-[#a63f21] text-2xl leading-tight line-clamp-1">
                                    <?= e($c['title']) ?>
                                </h3>
                                <?php if (!empty($c['description'])): ?>
                                    <p class="text-[#4b4b4b] text-sm font-inter leading-5 mt-0.5 line-clamp-1">
                                        <?= e($c['description']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="flex items-center gap-0 mt-2">
                                <!-- Venue -->
                                <div class="flex flex-1 items-center gap-2 text-[#4b4b4b] text-sm font-inter">
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="whitespace-nowrap truncate max-w-[100px]"><?= e($c['venue']) ?></span>
                                </div>
                                <!-- Date -->
                                <div class="flex flex-1 items-center gap-2 text-[#4b4b4b] text-sm font-inter">
                                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                        <line x1="16" y1="2" x2="16" y2="6"/>
                                        <line x1="8"  y1="2" x2="8"  y2="6"/>
                                        <line x1="3"  y1="10" x2="21" y2="10"/>
                                    </svg>
                                    <span><?= e(date('Y.m.d', strtotime($c['performed_at']))) ?></span>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
    (function () {
        var input    = document.getElementById('concert-search');
        var cards    = document.querySelectorAll('.concert-card');
        var noResult = document.getElementById('no-results');
        if (!input) return;

        input.addEventListener('input', function () {
            var q = this.value.trim().toLowerCase();
            var visible = 0;
            cards.forEach(function (card) {
                var match = !q || card.dataset.title.includes(q) || card.dataset.venue.includes(q);
                card.style.display = match ? '' : 'none';
                if (match) visible++;
            });
            if (noResult) noResult.classList.toggle('hidden', visible > 0);
        });
    })();
    </script>
</section>
