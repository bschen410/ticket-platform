<?php /** @var array $concerts */ /** @var string $pageTitle */ ?>
<?php $pageTitle = $pageTitle ?? '演唱會'; ?>

<!-- ── Page header ─────────────────────────────────────────── -->
<div class="flex flex-col gap-6 mb-10">

    <h1 class="font-manrope font-bold text-[#1a1a1a] text-[32px] leading-tight"><?= e($pageTitle) ?></h1>

    <div class="flex flex-wrap items-center justify-between gap-3">

        <!-- Search bar -->
        <div class="relative w-full sm:w-[480px]">
            <div class="absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none">
                <svg class="w-5 h-5 text-[#ea6d4a]" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2.5">
                    <circle cx="11" cy="11" r="8"/>
                    <path stroke-linecap="round" d="m21 21-4.35-4.35"/>
                </svg>
            </div>
            <div class="absolute left-[46px] top-1/2 -translate-y-1/2 h-5 w-px bg-slate-300 pointer-events-none"></div>
            <input id="concert-search" type="search"
                   placeholder="搜尋演唱會、場館、藝人..."
                   value="<?= e($_GET['q'] ?? '') ?>"
                   class="w-full h-12 bg-[#f6f6f6] rounded-full pl-[60px] pr-5 text-[#1a1a1a] text-sm font-inter
                          placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#ea6d4a]/40 shadow-sm">
        </div>

        <!-- View toggle -->
        <div class="flex items-center gap-2">
            <button id="btn-grid" aria-label="格狀檢視"
                    class="view-btn w-10 h-10 flex items-center justify-center rounded-lg transition-colors
                           bg-[#ea6d4a] text-white">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3"  y="3"  width="7" height="7" rx="1"/>
                    <rect x="14" y="3"  width="7" height="7" rx="1"/>
                    <rect x="3"  y="14" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>
            </button>
            <button id="btn-list" aria-label="列表檢視"
                    class="view-btn w-10 h-10 flex items-center justify-center rounded-lg transition-colors
                           bg-[#f6f6f6] text-[#4b4b4b] hover:bg-[#e8e8e8]">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="6"  x2="21" y2="6"/>
                    <line x1="3" y1="12" x2="21" y2="12"/>
                    <line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<?php if (empty($concerts)): ?>
    <div class="rounded-lg bg-white/70 px-5 py-4 text-sm text-[#4b4b4b] font-inter">
        目前沒有<?= e($pageTitle) ?>的演唱會，敬請期待。
    </div>
<?php else: ?>

    <p id="no-results" class="hidden rounded-lg bg-white/70 px-5 py-4 text-sm text-[#4b4b4b] font-inter mb-4">
        找不到符合的演唱會。
    </p>

    <!-- ── GRID VIEW ─────────────────────────────────────────── -->
    <div id="view-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-[14px]">
        <?php foreach ($concerts as $c): ?>
            <a href="/concerts/<?= (int) $c['id'] ?>"
               data-title="<?= mb_strtolower(e($c['title'])) ?>"
               data-venue="<?= mb_strtolower(e($c['venue'])) ?>"
               class="concert-card hover:bg-[#F1F1F1] rounded-[5px] overflow-hidden flex flex-col
                      hover:shadow-lg transition-all duration-200">

                <div class="shrink-0 overflow-hidden" style="aspect-ratio: 850/370;">
                    <?php if (!empty($c['poster_url'])): ?>
                        <img src="<?= e($c['poster_url']) ?>"
                             class="w-full h-full object-cover"
                             alt="<?= e($c['title']) ?>">
                    <?php else: ?>
                        <div class="w-full h-full bg-slate-800 flex items-center justify-center">
                            <svg class="w-12 h-12 text-white/20" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                            </svg>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="flex flex-col justify-between flex-1 pl-[15px] pr-4 pt-3 pb-[24px]">
                    <div>
                        <h3 class="font-manrope font-bold text-[#a63f21] text-2xl leading-tight line-clamp-1">
                            <?= e($c['title']) ?>
                        </h3>
                    </div>
                    <div class="flex items-center gap-0 mt-2">
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

    <!-- ── LIST VIEW ─────────────────────────────────────────── -->
    <style>
        @media (max-width: 767px) {
            #list-header-row { display: none !important; }
            #view-list .concert-card { display: flex !important; align-items: center; gap: 12px; padding: 10px 0; }
            #view-list .list-col-thumb { width: 64px; flex-shrink: 0; }
            #view-list .list-col-title { flex: 1; min-width: 0; }
            #view-list .list-col-date, #view-list .list-col-venue { display: none !important; }
            #view-list .list-col-cta { padding-right: 0; }
        }
    </style>
    <div id="view-list" class="hidden flex-col">
        <!-- Header row -->
        <div id="list-header-row"
             class="grid gap-4 px-4 pb-2 border-b border-[#d0d0d0] text-xs font-manrope font-semibold text-[#4b4b4b] uppercase tracking-wider"
             style="grid-template-columns: 80px 1fr 180px 160px 100px;">
            <span></span>
            <span>演唱會</span>
            <span>演出時間</span>
            <span>地點</span>
            <span></span>
        </div>

        <div class="flex flex-col divide-y divide-[#d0d0d0]">
        <?php foreach ($concerts as $c): ?>
            <a href="/concerts/<?= (int) $c['id'] ?>"
               data-title="<?= mb_strtolower(e($c['title'])) ?>"
               data-venue="<?= mb_strtolower(e($c['venue'])) ?>"
               class="concert-card grid gap-4 items-center py-3
                      hover:bg-[#F1F1F1] transition-colors duration-200"
               style="grid-template-columns: 80px 1fr 180px 160px 100px;">

                <!-- Thumbnail -->
                <div class="list-col-thumb shrink-0 w-[80px]">
                    <?php if (!empty($c['poster_url'])): ?>
                        <img src="<?= e($c['poster_url']) ?>"
                             class="w-full h-auto block"
                             alt="<?= e($c['title']) ?>">
                    <?php else: ?>
                        <div class="w-full h-[52px] bg-slate-800 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white/20" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                            </svg>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Title -->
                <span class="list-col-title font-manrope font-bold text-[#1a1a1a] text-base leading-tight line-clamp-1 pr-4">
                    <?= e($c['title']) ?>
                </span>

                <!-- Date -->
                <div class="list-col-date flex items-center gap-2 text-[#4b4b4b] text-sm font-inter">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8"  y1="2" x2="8"  y2="6"/>
                        <line x1="3"  y1="10" x2="21" y2="10"/>
                    </svg>
                    <span><?= e(date('Y.m.d H:i', strtotime($c['performed_at']))) ?></span>
                </div>

                <!-- Venue -->
                <div class="list-col-venue flex items-center gap-2 text-[#4b4b4b] text-sm font-inter">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="truncate"><?= e($c['venue']) ?></span>
                </div>

                <!-- CTA -->
                <div class="list-col-cta flex justify-end pr-4">
                    <span class="inline-flex items-center justify-center
                                 bg-[#ea6d4a] text-white text-sm font-manrope font-semibold
                                 rounded-full px-4 py-1.5 hover:bg-[#d4603d] transition-colors whitespace-nowrap">
                        查看詳情
                    </span>
                </div>
            </a>
        <?php endforeach; ?>
        </div>
    </div>

<?php endif; ?>

<script>
(function () {
    var btnGrid  = document.getElementById('btn-grid');
    var btnList  = document.getElementById('btn-list');
    var viewGrid = document.getElementById('view-grid');
    var viewList = document.getElementById('view-list');
    if (!btnGrid || !viewGrid) return;

    var ACTIVE   = ['bg-[#ea6d4a]', 'text-white'];
    var INACTIVE = ['bg-[#f6f6f6]', 'text-[#4b4b4b]'];

    function setActive(on, off) {
        viewGrid.style.display = (on === btnGrid) ? '' : 'none';
        viewList.style.display = (on === btnList) ? 'flex' : 'none';
        ACTIVE.forEach(function(c)   { on.classList.add(c);     off.classList.remove(c); });
        INACTIVE.forEach(function(c) { off.classList.add(c);    on.classList.remove(c);  });
    }

    btnGrid.addEventListener('click', function () {
        setActive(btnGrid, btnList);
        localStorage.setItem('concerts-view', 'grid');
    });
    btnList.addEventListener('click', function () {
        setActive(btnList, btnGrid);
        localStorage.setItem('concerts-view', 'list');
    });

    if (localStorage.getItem('concerts-view') === 'list') {
        setActive(btnList, btnGrid);
    }

    // Search
    var input    = document.getElementById('concert-search');
    var cards    = document.querySelectorAll('.concert-card');
    var noResult = document.getElementById('no-results');
    if (!input) return;

    if (input.value) input.dispatchEvent(new Event('input'));

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
