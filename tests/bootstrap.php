<?php

declare(strict_types=1);

/*
 * PocketMine-MP's CoreConstants.php (loaded by the Composer autoloader, which PHPUnit includes before this file)
 * expects its data packages under its own vendor/ directory, as when the server is the root project.
 * Installed as a dev dependency they live in our vendor/, so point the server's vendor/ there.
 */
$vendor = dirname(__DIR__) . '/vendor';
$serverVendor = $vendor . '/axolotl-pm/pocketmine-mp/vendor';
if(!file_exists($serverVendor) && !@symlink('../..', $serverVendor) && !is_dir($serverVendor)){
    throw new \RuntimeException("Could not link $serverVendor to $vendor");
}

require_once $vendor . '/autoload.php';

if(!extension_loaded('encoding')){
    // Test-only stand-in for pmmp/ext-encoding, see the file for its limits
    require __DIR__ . '/polyfill/encoding.php';
}
if(!extension_loaded('morton')){
    require __DIR__ . '/polyfill/morton.php';
}
