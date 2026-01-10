<?php
/**
 * ETEEAP Survey Application - InfinityFree Entry Point
 * 
 * Drop this into your htdocs root folder.
 */

// Define application root as the current folder (where src, storage, etc live)
define('APP_ROOT', __DIR__);

// Load configuration
require_once APP_ROOT . '/src/config/app.php';
require_once APP_ROOT . '/src/config/database.php';

// Load helpers
require_once APP_ROOT . '/src/helpers/csrf.php';
require_once APP_ROOT . '/src/helpers/session.php';
require_once APP_ROOT . '/src/helpers/validation.php';
require_once APP_ROOT . '/src/helpers/export.php';
require_once APP_ROOT . '/src/helpers/security.php';

// ... rest of the index.php logic continues here ...
// (I will provide the full block in a moment)
