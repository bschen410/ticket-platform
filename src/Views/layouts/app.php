<?php
/** @var string $content */
/** @var bool   $navTransparent */
$bodyBg = ($navTransparent ?? false) ? 'bg-[#1c1c1c]' : 'bg-[#E3E3E3]';
?>
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
<body class="<?= $bodyBg ?> font-inter min-h-screen flex flex-col">

<?php require __DIR__ . '/_header.php'; ?>
<?php require __DIR__ . '/_flash.php'; ?>

<main <?= ($navTransparent ?? false) ? 'class="flex-1"' : 'class="flex-1 w-full pt-[88px] pb-16 px-8 max-w-[1440px] mx-auto"' ?>><?= $content ?></main>

<?php require __DIR__ . '/_footer.php'; ?>

</body>
</html>
