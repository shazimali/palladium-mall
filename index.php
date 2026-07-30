<?php

/*
|--------------------------------------------------------------------------
| Laravel Shared Hosting Entry Point
|--------------------------------------------------------------------------
| This file exists so shared hosting with document root pointing to the
| project root (not /public) still boots Laravel correctly.
|
| This file simply delegates to public/index.php.
*/

define('LARAVEL_START', microtime(true));

require __DIR__.'/public/index.php';
