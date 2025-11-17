<?php

declare(strict_types=1);

use Hyperf\Di\ClassLoader;
use Hyperf\Engine\Coroutine;

ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');

error_reporting(E_ALL);
date_default_timezone_set('UTC');

! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__));
! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);

// CORREÇÃO: A função espera um inteiro (flags), não um booleano.
// Usamos a constante SWOOLE_HOOK_FLAGS que é definida como SWOOLE_HOOK_ALL.
Swoole\Runtime::enableCoroutine(SWOOLE_HOOK_FLAGS);

require BASE_PATH . '/vendor/autoload.php';

ClassLoader::init();

Coroutine::set([
    'hook_flags' => SWOOLE_HOOK_FLAGS,
]);