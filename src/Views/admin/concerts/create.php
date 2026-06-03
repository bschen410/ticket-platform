<?php
/** @var array $errors */ /** @var array $old */ /** @var array $statuses */
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin/concerts">演唱會管理</a></li>
        <li class="breadcrumb-item active" aria-current="page">新增演唱會</li>
    </ol>
</nav>

<h1 class="h3 mb-4">新增演唱會</h1>

<div class="card">
    <div class="card-body">
        <?php
            $action = '/admin/concerts';
            $submitLabel = '建立';
            require __DIR__ . '/_form.php';
        ?>
    </div>
</div>
