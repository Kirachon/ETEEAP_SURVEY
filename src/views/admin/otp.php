<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'OTP') ?> | <?= APP_NAME ?></title>
    <?= csrfMetaTag() ?>

    <link rel="stylesheet" href="<?= assetUrl('app.css') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="min-h-screen font-sans bg-gradient-to-br from-blue-900 via-blue-800 to-blue-900 flex items-center justify-center p-4">

    <?php
    $pendingEmail = (string) ($pendingEmail ?? '');
    $masked = $pendingEmail;
    if (strpos($pendingEmail, '@') !== false) {
        [$local, $domain] = explode('@', $pendingEmail, 2);
        $local = (string) $local;
        $domain = (string) $domain;
        $maskedLocal = mb_substr($local, 0, 2, 'UTF-8') . str_repeat('*', max(0, mb_strlen($local, 'UTF-8') - 2));
        $masked = $maskedLocal . '@' . $domain;
    }
    ?>

    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full shadow-lg mb-4">
                <span class="text-3xl font-bold text-blue-900">E</span>
            </div>
            <h1 class="text-2xl font-bold text-white">ETEEAP Survey</h1>
            <p class="text-blue-200 text-sm mt-1">Admin Portal</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-2">Enter OTP</h2>
            <p class="text-sm text-gray-500 mb-6">We sent a one-time code to <span class="font-semibold text-gray-700"><?= htmlspecialchars($masked) ?></span>.</p>
            <p class="text-xs text-gray-400 mb-6">You can request a new OTP after about 1 minute. Each OTP expires in about 10 minutes.</p>

            <?php if (flashHas('error')): ?>
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg flex items-center gap-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm text-red-700"><?= htmlspecialchars(flashGet('error'), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></span>
            </div>
            <?php endif; ?>

            <?php if (flashHas('success')): ?>
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3">
                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-sm text-green-700"><?= htmlspecialchars(flashGet('success'), ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></span>
            </div>
            <?php endif; ?>

            <form method="POST" action="<?= appUrl('/admin/otp') ?>" class="space-y-4">
                <?= csrfInputField() ?>

                <div>
                    <label for="otp" class="block text-sm font-medium text-gray-700 mb-1">One-Time Password</label>
                    <input
                        type="text"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        id="otp"
                        name="otp"
                        maxlength="10"
                        class="w-full h-12 px-4 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors tracking-widest text-center text-lg font-semibold"
                        placeholder="000000"
                        required
                        autofocus
                    >
                </div>

                <button type="submit" class="w-full h-12 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all">
                    Verify & Continue
                </button>
            </form>

            <div class="mt-4 flex items-center justify-between gap-3">
                <form method="POST" action="<?= appUrl('/admin/otp/resend') ?>">
                    <?= csrfInputField() ?>
                    <button type="submit" class="text-sm font-semibold text-blue-700 hover:text-blue-900">
                        Resend OTP
                    </button>
                </form>

                <form method="POST" action="<?= appUrl('/admin/otp/cancel') ?>">
                    <?= csrfInputField() ?>
                    <button type="submit" class="text-sm font-semibold text-gray-600 hover:text-gray-900">
                        Cancel
                    </button>
                </form>
            </div>
        </div>

        <div class="text-center mt-6">
            <p class="text-blue-200 text-xs">
                &copy; <?= date('Y') ?> DSWD ETEEAP Survey System
            </p>
        </div>
    </div>

</body>
</html>
