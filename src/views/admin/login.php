<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Login') ?> | <?= APP_NAME ?></title>
    <?= csrfMetaTag() ?>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-900 via-blue-800 to-blue-900 flex items-center justify-center p-4">
    
    <div class="w-full max-w-md">
        
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full shadow-lg mb-4">
                <span class="text-3xl font-bold text-blue-900">E</span>
            </div>
            <h1 class="text-2xl font-bold text-white">ETEEAP Survey</h1>
            <p class="text-blue-200 text-sm mt-1">Admin Portal</p>
        </div>
        
        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">Sign in to your account</h2>
            
            <?php if (flashHas('error')): ?>
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg flex items-center gap-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm text-red-700"><?= htmlspecialchars(flashGet('error')) ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (flashHas('success')): ?>
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3">
                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-sm text-green-700"><?= htmlspecialchars(flashGet('success')) ?></span>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="<?= appUrl('/admin/login') ?>">
                <?= csrfInputField() ?>
                
                <div class="space-y-4">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?= htmlspecialchars($email ?? '') ?>"
                            class="w-full h-12 px-4 rounded-lg border <?= isset($errors['email']) ? 'border-red-500' : 'border-gray-300' ?> focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            placeholder="admin@dswd.gov.ph"
                            required
                            autofocus
                        >
                        <?php if (isset($errors['email'])): ?>
                            <p class="text-xs text-red-500 mt-1"><?= htmlspecialchars($errors['email'][0]) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password"
                            class="w-full h-12 px-4 rounded-lg border <?= isset($errors['password']) ? 'border-red-500' : 'border-gray-300' ?> focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            placeholder="••••••••"
                            required
                        >
                        <?php if (isset($errors['password'])): ?>
                            <p class="text-xs text-red-500 mt-1"><?= htmlspecialchars($errors['password'][0]) ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Submit -->
                    <button 
                        type="submit" 
                        class="w-full h-12 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2"
                    >
                        <span>Sign In</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-6">
            <p class="text-blue-200 text-xs">
                &copy; <?= date('Y') ?> DSWD ETEEAP Survey System
            </p>
        </div>
    </div>
    
</body>
</html>
