<?php

declare(strict_types=1);

namespace kim\present\personaskin\tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Autoloads every class of the plugin, so a signature or inheritance mismatch against the server / protocol
 * (which PHP reports as a fatal error when the class is linked) is caught without a running server.
 */
final class ClassLoadTest extends TestCase{

    /** @return iterable<string, array{string}> */
    public static function classProvider() : iterable{
        $root = dirname(__DIR__) . '/src/';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
        foreach($files as $file){
            if(!$file instanceof \SplFileInfo || $file->getExtension() !== 'php'){
                continue;
            }
            $class = 'kim\\present\\personaskin\\' . str_replace('/', '\\', substr($file->getPathname(), strlen($root), -4));
            yield $class => [$class];
        }
    }

    #[DataProvider('classProvider')]
    public function testClassLoads(string $class) : void{
        self::assertTrue(
            class_exists($class) || interface_exists($class) || trait_exists($class) || enum_exists($class),
            "$class could not be loaded"
        );
    }
}
