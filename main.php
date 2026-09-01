<?php
declare(strict_types=1);

/**
 * ============================================================================
 * Modern PHP (PHP 8.1+) Crash Course - Main Runner
 * For Rust / C# / Go / Java / Python Developers
 * ============================================================================
 */

require_once __DIR__ . '/src/01_types_and_classes.php';
require_once __DIR__ . '/src/02_pattern_matching_and_arrays.php';
require_once __DIR__ . '/src/03_exceptions_and_traits.php';
require_once __DIR__ . '/src/04_generators_and_fibers.php';

function printBanner(string $title): void {
    echo "\n" . str_repeat("=", 64) . "\n";
    echo "  {$title}\n";
    echo str_repeat("=", 64) . "\n\n";
}

function printSection(string $title): void {
    echo "\n" . str_repeat("#", 64) . "\n";
    echo "# {$title}\n";
    echo str_repeat("#", 64) . "\n\n";
}

function main(): void {
    printBanner("MODERN PHP CRASH COURSE (Running on PHP " . PHP_VERSION . ")");

    printSection("01: Type System, Readonly Classes, and Backed Enum");
    \Sample\Types\run();

    printSection("02: Pattern Matching (match), Nullsafe (?->), and Arrays");
    \Sample\Control\run();

    printSection("03: Exception Handling, Traits (Mix-in), and Interfaces");
    \Sample\OOP\run();

    printSection("04: Memory Optimization (yield) and Coroutines (Fiber)");
    \Sample\Async\run();

    printBanner("ALL PHP TUTORIAL MODULES COMPLETED SUCCESSFULLY!");
}

main();
