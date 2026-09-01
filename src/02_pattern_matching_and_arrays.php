<?php
declare(strict_types=1);

/**
 * ============================================================================
 * PHP 02: match式・名前付き引数・配列関数 (Pattern Matching & Arrays)
 * ============================================================================
 * 
 * 【他言語経験者（Rust, C#, Go, Java, Python）向け要点】
 * 1. match 式 (PHP 8.0+):
 *    - 従来の `switch` 文の欠点（暗黙の型変換 `==`、`break` 忘れ、フォールスルー）を完全に排除。
 *    - Rustの `match`、C#の `switch` 式と同様に **厳格比較 (===)** を行い、**式として値を返します**。
 * 
 * 2. 名前付き引数 (Named Arguments - PHP 8.0+):
 *    - `createUser(name: "Alice", age: 30, isAdmin: true)` のように引数名を指定可能。
 *    - デフォルト引数が多い関数の可読性が劇的に向上（C# / Python と同等）。
 * 
 * 3. Nullsafe 演算子 (?-> - PHP 8.0+):
 *    - C# / TS / Swift の `?.` に相当。途中で `null` があれば即座に `null` を返します。
 * 
 * 4. アロー関数 (fn() => expr - PHP 7.4+):
 *    - `function() use ($x)` と書かずに、外側のスコープの変数を**値渡しで自動キャプチャ**。
 */

namespace Sample\Control;

// ============================================================================
// 1. match 式による代数的データ型の分岐
// ============================================================================
interface PaymentMethod {}

readonly class CreditCard implements PaymentMethod {
    public function __construct(public string $number, public string $holder) {}
}

readonly class BankTransfer implements PaymentMethod {
    public function __construct(public string $account, public string $bankCode) {}
}

readonly class Crypto implements PaymentMethod {
    public function __construct(public string $address, public string $currency) {}
}

function processPayment(PaymentMethod $payment): string {
    // match 式によるクラス型チェックと安全な値の返却
    return match ($payment::class) {
        CreditCard::class => (function() use ($payment): string {
            /** @var CreditCard $payment */
            $masked = "****-****-****-" . substr($payment->number, -4);
            return "CreditCard [Holder: {$payment->holder}, Number: {$masked}]";
        })(),

        BankTransfer::class => (function() use ($payment): string {
            /** @var BankTransfer $payment */
            return "BankTransfer [Bank: {$payment->bankCode}, Acc: {$payment->account}]";
        })(),

        Crypto::class => (function() use ($payment): string {
            /** @var Crypto $payment */
            return "Crypto [Currency: {$payment->currency}, Addr: {$payment->address}]";
        })(),

        default => throw new \InvalidArgumentException("Unsupported payment type"),
    };
}

// ============================================================================
// 2. オプショナルなプロパティを持つオブジェクト
// ============================================================================
class Order {
    public function __construct(
        public string $orderId,
        public ?Customer $customer = null
    ) {}
}

class Customer {
    public function __construct(
        public string $name,
        public ?Address $address = null
    ) {}
}

class Address {
    public function __construct(public string $city) {}
}

// ============================================================================
// 実行関数
// ============================================================================
function run(): void {
    echo "--- 1. Pattern Matching with match Expression ---\n";
    $payments = [
        new CreditCard("1234-5678-9012-3456", "Alice"),
        new Crypto("0x1234abcd5678", "ETH"),
        new BankTransfer("987654321", "BNK-001"),
    ];

    foreach ($payments as $p) {
        echo "  " . processPayment($p) . "\n";
    }

    echo "\n--- 2. Nullsafe Operator (?->) and Null Coalescing (??) ---\n";
    $order1 = new Order("ord-001", new Customer("Alice", new Address("Tokyo")));
    $order2 = new Order("ord-002", new Customer("Bob", null));
    $order3 = new Order("ord-003", null);

    // Nullsafe 演算子で安全にチェーンアクセス
    $city1 = $order1->customer?->address?->city ?? "Unknown City";
    $city2 = $order2->customer?->address?->city ?? "Unknown City";
    $city3 = $order3->customer?->address?->city ?? "Unknown City";

    echo "Order 1 City: {$city1}\n";
    echo "Order 2 City: {$city2}\n";
    echo "Order 3 City: {$city3}\n";

    echo "\n--- 3. Functional Array Pipelines & Arrow Functions ---\n";
    $products = [
        ["name" => "MacBook Pro", "category" => "Electronics", "price" => 250000, "stock" => 5],
        ["name" => "Mechanical Keyboard", "category" => "Electronics", "price" => 18000, "stock" => 0],
        ["name" => "Rust in Action", "category" => "Books", "price" => 4200, "stock" => 8],
        ["name" => "Clean Code", "category" => "Books", "price" => 3800, "stock" => 12],
    ];

    // 在庫あり Books のタイトルを大文字にして抽出 (LINQ / Stream 相当)
    $inStockBooks = array_filter(
        $products,
        fn(array $p): bool => $p["category"] === "Books" && $p["stock"] > 0
    );

    $bookTitles = array_map(
        fn(array $p): string => strtoupper($p["name"]),
        $inStockBooks
    );
    echo "Available Books: [ " . implode(", ", $bookTitles) . " ]\n";

    // 合計金額の計算 (array_reduce)
    $totalValue = array_reduce(
        $products,
        fn(int $acc, array $p): int => $acc + ($p["price"] * $p["stock"]),
        0
    );
    echo "Total Inventory Value: JPY " . number_format($totalValue) . "\n";
}

// 直接実行された場合
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    run();
}
