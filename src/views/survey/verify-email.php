<?php
/**
 * Survey: Verify Email (OTP)
 */

$email = (string) ($email ?? '');
$masked = $email;
if (strpos($email, '@') !== false) {
    [$local, $domain] = explode('@', $email, 2);
    $local = (string) $local;
    $domain = (string) $domain;
    $maskedLocal = mb_substr($local, 0, 2, 'UTF-8') . str_repeat('*', max(0, mb_strlen($local, 'UTF-8') - 2));
    $masked = $maskedLocal . '@' . $domain;
}
?>

<div class="min-h-screen bg-slate-50 pt-8 pb-40">
    <div class="max-w-md md:max-w-2xl lg:max-w-3xl mx-auto px-6 w-full animate-fade-in">

        <div class="mb-10 text-center sm:text-left">
            <h1 class="text-3xl lg:text-4xl font-extrabold tracking-tight text-dswd-dark leading-tight">
                VERIFY EMAIL
            </h1>
            <p class="mt-3 text-base lg:text-lg text-slate-500 max-w-2xl">
                We sent a one-time password (OTP) to <span class="font-bold text-slate-700"><?= htmlspecialchars($masked, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></span>.
            </p>
        </div>

        <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
            <div class="p-8 md:p-10 space-y-6">

                <form method="POST" action="<?= appUrl('/survey/verify-email') ?>" class="space-y-6">
                    <?= csrfInputField() ?>

                    <div class="space-y-2">
                        <label for="otp" class="block text-sm font-bold text-slate-700 uppercase tracking-widest">OTP Code <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            id="otp"
                            name="otp"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            maxlength="10"
                            class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-black tracking-[0.3em] text-center focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none"
                            placeholder="000000"
                            required
                            autofocus
                        >
                        <p class="text-xs text-slate-500">Tip: check your inbox and spam/junk folder.</p>
                        <p class="text-xs text-slate-500">You can request a new OTP after about 1 minute. Each OTP expires in about 10 minutes.</p>
                    </div>

                    <button
                        type="submit"
                        class="w-full h-14 bg-dswd-blue hover:bg-blue-700 text-white font-black rounded-2xl shadow-premium hover:shadow-xl transition-all flex items-center justify-center gap-3"
                    >
                        Verify & Continue
                    </button>
                </form>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <form method="POST" action="<?= appUrl('/survey/verify-email/resend') ?>">
                        <?= csrfInputField() ?>
                        <button type="submit" class="w-full sm:w-auto px-6 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold transition-all">
                            Resend OTP
                        </button>
                    </form>

                    <a href="<?= appUrl('/survey/step/2') ?>" class="w-full sm:w-auto px-6 py-3 rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold transition-all text-center">
                        Change Email
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
