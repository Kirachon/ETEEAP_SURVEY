<?php
/**
 * Web Installer - /install
 */

$locked = $locked ?? false;
$storageWritable = $storageWritable ?? false;
$phpVersion = $phpVersion ?? PHP_VERSION;
$errors = $errors ?? [];
$messages = $messages ?? [];
$old = is_array($old ?? null) ? $old : [];

function oldValue(array $old, string $key, string $default = ''): string
{
    $v = $old[$key] ?? $default;
    return is_string($v) ? $v : $default;
}
?>

<div class="min-h-screen bg-slate-50 pt-8 pb-16">
    <div class="max-w-md md:max-w-2xl lg:max-w-4xl mx-auto px-6 w-full animate-fade-in">
        <div class="mb-10 text-center sm:text-left">
            <h1 class="text-3xl lg:text-4xl font-extrabold tracking-tight text-dswd-dark leading-tight">
                Installer Wizard
            </h1>
            <p class="mt-3 text-base lg:text-lg text-slate-500 max-w-2xl text-justify">
                This setup wizard will help you configure the app on Windows (XAMPP / PHP + MySQL) without Docker.
                It will create <code class="px-2 py-1 bg-slate-100 rounded">storage/config.php</code>, initialize the database,
                create the admin user, and then lock itself.
            </p>
            <p class="mt-3 text-sm text-slate-500 max-w-2xl">
                If you see an error about admin password, scroll to <a class="font-bold text-dswd-blue hover:underline" href="#admin-account">Admin Account</a>.
            </p>
        </div>

        <?php if ($locked): ?>
            <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 p-8">
                <h2 class="text-xl font-black text-dswd-dark">Installer is locked</h2>
                <p class="mt-3 text-slate-600">
                    This app is already installed. To re-run the installer, delete:
                    <code class="px-2 py-1 bg-slate-100 rounded">storage/install.lock</code>
                </p>
            </div>
            <?php return; ?>
        <?php endif; ?>

        <?php if (!empty($messages)): ?>
            <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 p-8 mb-8">
                <h2 class="text-lg font-black text-dswd-dark uppercase tracking-wider">Progress</h2>
                <ul class="mt-4 space-y-2 text-sm text-slate-700">
                    <?php foreach ($messages as $m): ?>
                        <li class="flex items-start gap-2">
                            <span class="mt-0.5 text-green-600 font-black">✓</span>
                            <span><?= htmlspecialchars((string) $m) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="bg-white rounded-[2rem] shadow-premium border border-red-200/60 p-8 mb-8">
                <h2 class="text-lg font-black text-red-700 uppercase tracking-wider">Fix these issues</h2>
                <ul class="mt-4 space-y-2 text-sm text-red-700">
                    <?php foreach ($errors as $field => $msgs): ?>
                        <?php foreach ((array) $msgs as $msg): ?>
                            <li class="flex items-start gap-2">
                                <span class="mt-0.5 font-black">•</span>
                                <span><?= htmlspecialchars((string) $msg) ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
            <div class="p-8 md:p-10">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <h2 class="text-lg font-black text-dswd-dark uppercase tracking-wider">System Check</h2>
                    <span class="text-[11px] font-black text-slate-400 bg-slate-100 px-3 py-1 rounded-full uppercase tracking-widest">
                        PHP <?= htmlspecialchars($phpVersion) ?>
                    </span>
                </div>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/50">
                        <div class="font-black text-slate-700">Storage writable</div>
                        <div class="mt-1 text-sm font-semibold <?= $storageWritable ? 'text-green-700' : 'text-red-700' ?>">
                            <?= $storageWritable ? 'OK' : 'Not writable (fix folder permissions for /storage)' ?>
                        </div>
                    </div>
                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/50">
                        <div class="font-black text-slate-700">PHP extensions</div>
                        <div class="mt-1 text-sm font-semibold text-slate-600">
                            Needs <code class="px-2 py-1 bg-slate-100 rounded">mysqli</code>, <code class="px-2 py-1 bg-slate-100 rounded">pdo_mysql</code>, <code class="px-2 py-1 bg-slate-100 rounded">mbstring</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="<?= appUrl('/install') ?>" class="space-y-8 mt-10">
            <?= csrfInputField() ?>

            <div id="app-settings" class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black text-dswd-dark uppercase tracking-wider mb-6">App Settings</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="app_url" class="block text-xs font-black text-slate-600 uppercase tracking-widest">App URL (optional)</label>
                            <input type="text" name="app_url" id="app_url" value="<?= htmlspecialchars(oldValue($old, 'app_url', '')) ?>"
                                   class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none"
                                   placeholder="e.g. http://eteeap.local">
                            <p class="text-[11px] text-slate-500">Leave blank to use the current browser host.</p>
                        </div>

                        <div class="space-y-2">
                            <label for="app_env" class="block text-xs font-black text-slate-600 uppercase tracking-widest">Environment</label>
                            <select name="app_env" id="app_env"
                                    class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none">
                                <?php $env = oldValue($old, 'app_env', 'development'); ?>
                                <option value="development" <?= $env === 'development' ? 'selected' : '' ?>>Development</option>
                                <option value="production" <?= $env === 'production' ? 'selected' : '' ?>>Production</option>
                            </select>
                            <label class="mt-3 inline-flex items-center gap-2 text-sm font-bold text-slate-600">
                                <input type="checkbox" name="app_debug" value="1" class="w-5 h-5" <?= isset($old['app_debug']) ? 'checked' : '' ?>>
                                Enable debug (recommended OFF for production)
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div id="db-settings" class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black text-dswd-dark uppercase tracking-wider mb-6">Database Settings</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-xs font-black text-slate-600 uppercase tracking-widest" for="db_host">DB Host</label>
                            <input type="text" name="db_host" id="db_host" value="<?= htmlspecialchars(oldValue($old, 'db_host', 'localhost')) ?>"
                                   class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none"
                                   placeholder="localhost">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-xs font-black text-slate-600 uppercase tracking-widest" for="db_port">DB Port</label>
                            <input type="text" name="db_port" id="db_port" value="<?= htmlspecialchars(oldValue($old, 'db_port', '3306')) ?>"
                                   class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none"
                                   placeholder="3306">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-xs font-black text-slate-600 uppercase tracking-widest" for="db_name">DB Name</label>
                            <input type="text" name="db_name" id="db_name" value="<?= htmlspecialchars(oldValue($old, 'db_name', 'eteeap_survey')) ?>"
                                   class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none"
                                   placeholder="eteeap_survey">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-xs font-black text-slate-600 uppercase tracking-widest" for="db_user">DB User</label>
                            <input type="text" name="db_user" id="db_user" value="<?= htmlspecialchars(oldValue($old, 'db_user', 'eteeap_user')) ?>"
                                   class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none"
                                   placeholder="eteeap_user">
                        </div>
                        <div class="space-y-2 md:col-span-2">
                            <label class="block text-xs font-black text-slate-600 uppercase tracking-widest" for="db_pass">DB Password</label>
                            <input type="password" name="db_pass" id="db_pass" value="<?= htmlspecialchars(oldValue($old, 'db_pass', '')) ?>"
                                   class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none"
                                   placeholder="(your DB password)">
                        </div>

                        <div class="md:col-span-2 p-5 rounded-2xl border border-slate-200 bg-slate-50/50">
                            <label class="inline-flex items-center gap-2 text-sm font-black text-slate-700">
                                <input type="checkbox" name="create_db" value="1" class="w-5 h-5" <?= isset($old['create_db']) ? 'checked' : '' ?>>
                                Create database + user (requires MySQL admin credentials)
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                                <div class="space-y-2">
                                    <label class="block text-xs font-black text-slate-600 uppercase tracking-widest" for="admin_db_user">MySQL Admin User</label>
                                    <input type="text" name="admin_db_user" id="admin_db_user" value="<?= htmlspecialchars(oldValue($old, 'admin_db_user', 'root')) ?>"
                                           class="w-full px-5 py-4 bg-white border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none"
                                           placeholder="root">
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-xs font-black text-slate-600 uppercase tracking-widest" for="admin_db_pass">MySQL Admin Password</label>
                                    <input type="password" name="admin_db_pass" id="admin_db_pass" value="<?= htmlspecialchars(oldValue($old, 'admin_db_pass', '')) ?>"
                                           class="w-full px-5 py-4 bg-white border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none"
                                           placeholder="(admin password)">
                                </div>
                            </div>
                            <label class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-slate-600">
                                <input type="checkbox" name="install_sample_data" value="1" class="w-5 h-5" <?= isset($old['install_sample_data']) ? 'checked' : '' ?>>
                                Install sample data (seed.sql) (not recommended for production)
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div id="admin-account" class="bg-white rounded-[2rem] shadow-premium border border-slate-200/60 overflow-hidden">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black text-dswd-dark uppercase tracking-wider mb-6">Admin Account</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-xs font-black text-slate-600 uppercase tracking-widest" for="admin_username">Username</label>
                            <input type="text" name="admin_username" id="admin_username" value="<?= htmlspecialchars(oldValue($old, 'admin_username', 'admin')) ?>"
                                   class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none"
                                   placeholder="admin">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-xs font-black text-slate-600 uppercase tracking-widest" for="admin_email">Email</label>
                            <input type="email" name="admin_email" id="admin_email" value="<?= htmlspecialchars(oldValue($old, 'admin_email', 'admin@dswd.gov.ph')) ?>"
                                   class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none"
                                   placeholder="admin@dswd.gov.ph">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-xs font-black text-slate-600 uppercase tracking-widest" for="admin_password">Password</label>
                            <input type="password" name="admin_password" id="admin_password"
                                   class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none"
                                   placeholder="Choose a strong password">
                            <?php if (isset($errors['admin_password'])): ?>
                                <p class="mt-1.5 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['admin_password'][0]) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-xs font-black text-slate-600 uppercase tracking-widest" for="admin_password_confirm">Confirm Password</label>
                            <input type="password" name="admin_password_confirm" id="admin_password_confirm"
                                   class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 font-semibold focus:ring-4 focus:ring-blue-500/10 focus:border-dswd-blue transition-all outline-none"
                                   placeholder="Repeat password">
                            <?php if (isset($errors['admin_password_confirm'])): ?>
                                <p class="mt-1.5 text-xs font-bold text-red-500"><?= htmlspecialchars($errors['admin_password_confirm'][0]) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sticky bottom-0 z-40 glass border-t border-slate-200/50 shadow-[0_-10px_40px_rgba(0,0,0,0.05)] mt-10">
                <div class="max-w-md md:max-w-2xl lg:max-w-4xl mx-auto p-6 md:p-8 flex items-center justify-end gap-4">
                    <button type="submit"
                            class="flex-1 sm:flex-none group relative flex items-center justify-center gap-3 bg-dswd-blue hover:bg-dswd-dark text-white font-black py-4 px-10 rounded-2xl shadow-xl shadow-blue-900/20 transition-all active:scale-[0.98]">
                        <span>Install Now</span>
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
