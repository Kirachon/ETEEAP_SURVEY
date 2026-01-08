<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Logout') ?> | <?= APP_NAME ?></title>
    <?= csrfMetaTag() ?>
    <link rel="stylesheet" href="<?= assetUrl('app.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="min-h-screen bg-slate-50 font-sans antialiased flex items-center justify-center p-6">
    <div class="w-full max-w-md card-premium p-6">
        <h1 class="text-xl font-black text-slate-900">Confirm logout</h1>
        <p class="mt-2 text-sm text-slate-600">You’ll be signed out of the admin dashboard.</p>

        <form method="POST" action="<?= appUrl('/admin/logout') ?>" class="mt-6 flex gap-3">
            <?= csrfInputField() ?>
            <a href="<?= appUrl('/admin/dashboard') ?>" class="flex-1 text-center bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-3 px-4 rounded-xl border border-slate-200 transition">
                Cancel
            </a>
            <button type="submit" class="flex-1 bg-dswd-blue hover:bg-dswd-dark text-white font-black py-3 px-4 rounded-xl shadow transition">
                Logout
            </button>
        </form>
    </div>
</body>
</html>
