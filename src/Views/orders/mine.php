<?php /** @var array $pending */ /** @var array $paid */ ?>
<?php
$tabs = [
    'pending' => ['待付款', $pending, 'pending'],
    'paid'    => ['已付款', $paid,    'paid'],
];
$initialTab = !empty($pending) ? 'pending' : 'paid';
if (isset($_GET['tab']) && array_key_exists($_GET['tab'], $tabs)) {
    $initialTab = $_GET['tab'];
}
?>

<div class="max-w-2xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-slate-900 mb-6" style="font-family: Manrope, sans-serif;">我的訂單</h1>

    <!-- Tab Bar -->
    <div class="flex border-b border-slate-200 mb-6">
        <?php foreach ($tabs as $key => [$label, $list, $type]): ?>
            <button
                id="btn-<?= $key ?>"
                onclick="switchTab('<?= $key ?>')"
                class="tab-btn relative px-5 py-3 text-sm font-semibold transition-colors focus:outline-none"
            >
                <?= e($label) ?>
                <span
                    id="badge-<?= $key ?>"
                    class="ml-1.5 inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-xs font-bold
                           <?= $type === 'pending' ? 'bg-[#FFF0EB] text-[#EA6D4A]' : 'bg-emerald-100 text-emerald-700' ?>"
                ><?= count($list) ?></span>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Tab Panes -->
    <?php foreach ($tabs as $key => [$label, $list, $type]): ?>
        <div id="pane-<?= $key ?>" class="tab-pane">
            <?php if (empty($list)): ?>
                <div class="flex flex-col items-center justify-center py-16 text-slate-400">
                    <svg class="w-12 h-12 mb-4 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                    </svg>
                    <p class="text-sm">目前沒有<?= e($label) ?>的訂單</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($list as $o): ?>
                        <a
                            href="<?= $type === 'paid' ? '/orders/' . (int) $o['id'] . '/ticket' : '/orders/' . (int) $o['id'] ?>"
                            class="block bg-[#F1F1F1] rounded-2xl hover:shadow-md transition-shadow overflow-hidden"
                        >
                            <!-- Main content row -->
                            <div class="flex items-center justify-between px-5 py-4">
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-900 truncate" style="font-family: Manrope, sans-serif;">
                                        <?= e($o['concert_title']) ?>
                                    </p>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        訂單 #<?= (int) $o['id'] ?> · 建立於 <?= e(date('Y-m-d H:i', strtotime($o['created_at']))) ?>
                                    </p>
                                    <?php if ($type === 'pending' && !empty($o['expires_at'])): ?>
                                        <p class="text-xs text-[#EA6D4A] mt-0.5">
                                            請於 <?= e(date('H:i', strtotime($o['expires_at']))) ?> 前完成付款
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="flex flex-col items-end ml-4 shrink-0">
                                    <span class="font-semibold text-slate-900 text-sm">
                                        NT$ <?= number_format((float) $o['total_amount']) ?>
                                    </span>
                                    <span class="mt-1.5 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?= $type === 'pending'
                                            ? 'bg-[#FFF0EB] text-[#EA6D4A]'
                                            : 'bg-emerald-100 text-emerald-700' ?>">
                                        <?= e($label) ?>
                                    </span>
                                </div>
                            </div>

                            <?php if ($type === 'paid'): ?>
                            <!-- E-ticket shortcut -->
                            <div class="flex items-center gap-1.5 px-5 py-2.5 border-t border-slate-200/70 text-[#EA6D4A] text-xs font-medium">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z"/>
                                </svg>
                                查看電子票券 →
                            </div>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<style>
.tab-btn {
    color: #94a3b8;
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
}
.tab-btn.active {
    color: #EA6D4A;
    border-bottom-color: #EA6D4A;
}
.tab-pane.hidden { display: none; }
</style>

<script>
(function () {
    var tabs = ['pending', 'paid'];

    function switchTab(tab) {
        tabs.forEach(function (t) {
            document.getElementById('pane-' + t).classList.toggle('hidden', t !== tab);
            var btn = document.getElementById('btn-' + t);
            btn.classList.toggle('active', t === tab);
        });
    }

    switchTab('<?= e($initialTab) ?>');
    window.switchTab = switchTab;
}());
</script>
