<?php
/** @var array $errors */ /** @var array $old */ /** @var array $statuses */
?>

<nav class="mb-4 text-sm text-slate-500">
    <a href="/admin/concerts" class="hover:text-slate-900">演唱會管理</a>
    <span class="mx-2">/</span>
    <span class="text-slate-900">新增演唱會</span>
</nav>

<h1 class="mb-4 text-2xl font-bold tracking-tight text-slate-900">新增演唱會</h1>

<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <?php
            $action = '/admin/concerts';
            $submitLabel = '建立';
            require __DIR__ . '/_form.php';
        ?>
</div>
