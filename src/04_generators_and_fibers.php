<?php
declare(strict_types=1);

/**
 * ============================================================================
 * PHP 04: ジェネレータ・メモリ最適化・Fiber (Generators & Fibers)
 * ============================================================================
 * 
 * 【他言語経験者（Rust, C#, Go, Java, Python）向け要点】
 * 1. ジェネレータ (yield):
 *    - C#の `yield return`、Pythonの `yield`、Rustの `Iterator` と同様の遅延評価機構。
 *    - 何万件・何百万件のデータでもメモリ（RAM）をほぼ消費せずに 1 件ずつストリーム処理。
 * 
 * 2. Fiber (ファイバー - PHP 8.1+):
 *    - 完全なスタックを保持する **コルーチン（協調的軽量スレッド）**。
 *    - `Fiber::suspend()` で処理を一時停止して値を呼び出し元へ返し、
 *      `$fiber->resume()` で停止箇所から再開可能。
 *    - モダンな非同期フレームワーク（Revolt, Amp, ReactPHP）の低レベル基盤。
 */

namespace Sample\Async;

// ============================================================================
// 1. ジェネレータによる遅延評価 (メモリ効率の高い数列生成)
// ============================================================================
/**
 * @return \Generator<int, int>
 */
function fibonacci(int $limit): \Generator {
    $a = 0;
    $b = 1;
    $count = 0;
    while ($count < $limit) {
        yield $a;
        $temp = $a;
        $a = $b;
        $b = $temp + $b;
        $count++;
    }
}

// ============================================================================
// 2. Fiber による協調的コルーチン
// ============================================================================
function demonstrateFibers(): void {
    $fiber = new \Fiber(function(): void {
        echo "  [Fiber] Coroutine started\n";
        
        // 外部からデータを受け取りつつ一時停止
        $input = \Fiber::suspend("Yielded from Fiber step 1");
        echo "  [Fiber] Resumed with input: '{$input}'\n";

        // もう一度一時停止
        \Fiber::suspend("Yielded from Fiber step 2");
        echo "  [Fiber] Coroutine finished\n";
    });

    // 1. Fiber を起動
    $state1 = $fiber->start();
    echo "  [Main] Fiber paused, received: '{$state1}'\n";

    // 2. データを注入して Fiber を再開
    $state2 = $fiber->resume("Data from Main Thread");
    echo "  [Main] Fiber paused again, received: '{$state2}'\n";

    // 3. 最後の完了まで再開
    $fiber->resume();
    echo "  [Main] Fiber lifecycle completed (isTerminated: " . ($fiber->isTerminated() ? "true" : "false") . ")\n";
}

// ============================================================================
// 実行関数
// ============================================================================
function run(): void {
    echo "--- 1. Memory-Efficient Stream via Generators (yield) ---\n";
    $fib = fibonacci(8);
    $numbers = [];
    foreach ($fib as $n) {
        $numbers[] = $n;
    }
    echo "Fibonacci (first 8 numbers): [ " . implode(", ", $numbers) . " ]\n";

    echo "\n--- 2. Coroutines and Cooperative Multitasking with Fiber (PHP 8.1+) ---\n";
    demonstrateFibers();
}

// 直接実行された場合
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    run();
}
