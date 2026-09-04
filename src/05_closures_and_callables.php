<?php
declare(strict_types=1);

namespace Sample\Closures;

use Closure;

/**
 * ============================================================================
 * モジュール 05: 無名関数・アロー関数・First-Class Callable (Closures & Callables)
 * ============================================================================
 * 
 * 【他言語経験者向け要点】
 * 1. 無名関数 (function () use ($var) {}):
 *    - PHP 5.3+ で導入。外側の変数は `use ($var)` で明示的にキャプチャしなければならない。
 *    - デフォルトは「値渡し（コピー）」。変更したい場合は `use (&$var)` と参照渡しを指定。
 * 
 * 2. アロー関数 (fn($x) => $x * 2):
 *    - PHP 7.4+ で導入。外側の変数を「自動的に不変キャプチャ（By-Value）」する。
 *    - 式（Expression）のみ記述可能（単一行）。
 * 
 * 3. First-Class Callable (strlen(...)):
 *    - PHP 8.1+ で導入。関数名やメソッドに `(...)` を付けるだけで即座に Closure オブジェクト化。
 *    - 古い文字列形式 `['MyClass', 'myMethod']` や `'strlen'` に代わる型安全な記法。
 * 
 * 4. Closure::bind() / Closure::call():
 *    - クロージャの `$this` スコープを別のオブジェクトに動的束縛するメタプログラミング。
 */

class Counter {
    private int $count = 0;

    public function increment(): void {
        $this->count++;
    }

    public function getCount(): int {
        return $this->count;
    }
}

function run(): void {
    demoAnonymousFunctionsAndCapture();
    demoArrowFunctions();
    demoFirstClassCallables();
    demoClosureScopeBinding();
}

function demoAnonymousFunctionsAndCapture(): void {
    echo "--- 1. Anonymous Functions & Variable Capture (use (\$x) vs use (&\$x)) ---\n";

    $base = 100;
    $mutableCount = 0;

    // 1. 値キャプチャ (コピーされるため外側には影響しない)
    $addBase = function (int $x) use ($base): int {
        return $x + $base;
    };
    echo "addBase(25): " . $addBase(25) . "\n";

    // 2. 参照キャプチャ (use (&$var))
    $increment = function () use (&$mutableCount): void {
        $mutableCount++;
    };
    $increment();
    $increment();
    echo "Mutable counter after 2 calls: {$mutableCount}\n";
}

function demoArrowFunctions(): void {
    echo "\n--- 2. Short Arrow Functions (fn(\$x) => ...) (PHP 7.4+) ---\n";

    $multiplier = 3;

    // アロー関数は外側の $multiplier を自動的に値キャプチャする (use 不要)
    $triple = fn(int $x): int => $x * $multiplier;
    echo "triple(10) with multiplier={$multiplier}: " . $triple(10) . "\n";

    // コレクション処理での簡潔なパイプライン
    $numbers = [1, 2, 3, 4, 5];
    $squared = array_map(fn(int $n): int => $n ** 2, $numbers);
    echo "Squared numbers: [ " . implode(', ', $squared) . " ]\n";
}

function demoFirstClassCallables(): void {
    echo "\n--- 3. First-Class Callable Syntax (...) (PHP 8.1+) ---\n";

    // 組み込み関数を安全に Closure 化
    $stringLength = strlen(...);
    echo "strlen(...) of 'Modern PHP': " . $stringLength('Modern PHP') . "\n";

    // インスタンスメソッドを Closure 化
    $counter = new Counter();
    $inc = $counter->increment(...);
    $inc();
    $inc();
    echo "Counter after First-Class Callable calls: " . $counter->getCount() . "\n";

    // 配列のフィルタリングに組み込み関数を渡す
    $words = ['apple', '', 'banana', '   ', 'cherry'];
    $nonEmpty = array_filter($words, strlen(...));
    echo "Filtered non-empty words: [ " . implode(', ', $nonEmpty) . " ]\n";
}

function demoClosureScopeBinding(): void {
    echo "\n--- 4. Dynamic Scope Binding (\$closure->call(\$obj)) ---\n";

    $counter = new Counter();

    // 通常はアクセスできない private プロパティ $count を Closure::call で動的参照
    $readPrivate = function (): int {
        /** @var Counter $this */
        return $this->count;
    };

    $currentValue = $readPrivate->call($counter);
    echo "Read private property \$count via Closure::call: {$currentValue}\n";
}

// 直接実行時のエントリポイント
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    run();
}
