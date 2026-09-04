# PHP クロージャ・アロー関数・First-Class Callable 完全理解ガイド (PHP Lambdas & Closures Deep Dive)

PHP 5.3 の無名関数から、PHP 7.4 のアロー関数、そして PHP 8.1 の First-Class Callable 構文に至るまで、**PHP の関数型プログラミング機能「クロージャ（Closures）」** の完全解説書です。

Python や JavaScript、Java などのエンジニアが必ず疑問に思う「`use ($var)` を書かないと外側変数を参照できない理由」「参照キャプチャ `use (&$var)` の正体」「アロー関数と無名関数の決定的な違い」「`strlen(...)` の裏側」まで徹底的に解説します。

---

## 📑 目次

1. [PHP のクロージャ進化史 (PHP 5.3 〜 PHP 8.1)](#1-php-のクロージャ進化史-php-53--php-81)
2. [無名関数と `use` キーワードのメモリモデル](#2-無名関数と-use-キーワードのメモリモデル)
3. [アロー関数 (`fn($x) => ...`) の特徴と制約](#3-アロー関数-fnx---の特徴と制約)
4. [PHP 8.1+ First-Class Callable 構文 (`strlen(...)`)](#4-php-81-first-class-callable-構文-strlen)
5. [スコープの動的再束縛 (`Closure::bind` / `Closure::call`)](#5-スコープの動的再束縛-closurebind--closurecall)
6. [実務高階関数パイプライン (`array_map`, `array_filter`, `array_reduce`)](#6-実務高階関数パイプライン-array_map-array_filter-array_reduce)
7. [他言語エンジニア向け比較表 (PHP vs JavaScript vs Python vs Java vs Rust)](#7-他言語エンジニア向け比較表-php-vs-javascript-vs-python-vs-java-vs-rust)
8. [理解度チェッククイズ & よくある落とし穴](#8-理解度チェッククイズ--よくある落とし穴)

---

## 1. PHP のクロージャ進化史 (PHP 5.3 〜 PHP 8.1)

### 歴史年表
1. **PHP 4 / 5.0**: `create_function('$a', 'return $a*2;')` という文字列 `eval` ベースの危険な関数生成しかなかった。
2. **PHP 5.3 (2009)**: **無名関数（Anonymous Functions）** と `use` キーワードの導入。組み込みクラス `Closure` の誕生。
3. **PHP 7.4 (2019)**: **アロー関数（Arrow Functions: `fn()`）** の導入。外側変数の自動キャプチャと短縮構文。
4. **PHP 8.1 (2021)**: **First-Class Callable 構文 (`callable(...)`)** の導入。文字列や配列によるリフレクションから型安全な Closure 化へ。

---

## 2. 無名関数と `use` キーワードのメモリモデル

PHP の無名関数は、JavaScript や Python と異なり、**外側のローカル変数を自動的にキャプチャしません**。

### 2-1. なぜ明示的な `use` が必要なのか？
PHP はレキシカルスコープ（静的スコープ）が完全に閉じており、関数の内部から外側のスコープの変数を勝手に見に行くことは許されていません。明示的に `use` で許可した変数のみが `Closure` インスタンス内部のプロパティとして保存されます。

### 2-2. 値キャプチャ (`use ($var)`) vs 参照キャプチャ (`use (&$var)`)

```php
$base = 100;
$counter = 0;

// 1. 値キャプチャ: 宣言時点の値がコピーされる (外側は変更されない)
$add = function (int $x) use ($base): int {
    return $x + $base;
};

// 2. 参照キャプチャ: 外側の変数へのポインタ参照を保持する (状態変更可能)
$increment = function () use (&$counter): void {
    $counter++;
};

$increment();
$increment();
echo $counter; // 2
```

---

## 3. アロー関数 (`fn($x) => ...`) の特徴と制約

PHP 7.4 で導入されたアロー関数は、無名関数をより簡潔に書くための糖衣構文です。

```php
$multiplier = 3;

// アロー関数: 外側の $multiplier を自動的に値キャプチャする
$triple = fn(int $x): int => $x * $multiplier;
```

### 💡 無名関数 (`function`) vs アロー関数 (`fn`)
| 比較項目 | 無名関数 (`function () {}`) | アロー関数 (`fn() =>`) |
| :--- | :--- | :--- |
| **キャプチャ方法** | **`use` による明示的指定** | **自動キャプチャ** |
| **キャプチャモード** | 値渡しも参照渡しも可能 (`&$var`) | **値渡し（By-Value）のみ** |
| **構文** | 複数行のブロック `{}` 可能 | **単一の式（Expression）のみ** |
| **`return` 文** | 必須 | **自動で式の評価値を返す** |

---

## 4. PHP 8.1+ First-Class Callable 構文 (`strlen(...)`)

PHP 8.1 より前は、関数やメソッドをコールバックとして渡す際に `'strlen'` という文字列や `[$this, 'myMethod']` という配列を使っていました。これらは静的解析やリファクタリングが効かず、スペルミスの温床でした。

```php
// ❌ 従来の古い記法 (型安全ではない)
$lengths = array_map('strlen', $words);
$filtered = array_filter($items, [$this, 'isValid']);

// ⭕ PHP 8.1+ First-Class Callable 構文 (型安全・IDE補完対応)
$lengths = array_map(strlen(...), $words);
$filtered = array_filter($items, $this->isValid(...));
```

- 末尾に `(...)` を書くだけで、PHP ランタイムがそのメソッドを参照する `Closure` オブジェクトを即座に生成します。
- プライベートメソッドに対しても、そのクラス内部の文脈であれば安全に callable 化できます。

---

## 5. スコープの動的再束縛 (`Closure::bind` / `Closure::call`)

PHP のクロージャはオブジェクトであり、**実行時のスコープ（`$this`）を別のオブジェクトに差し替える**ことができます。

```php
class UserSecret {
    private string $apiKey = "sk-live-999";
}

$user = new UserSecret();

// 通常は private プロパティに外部からアクセスできないが...
$reader = function (): string {
    return $this->apiKey; // $this を UserSecret に束縛してアクセス
};

// Closure::call により、$user のコンテキストで即座に実行！
$key = $reader->call($user);
echo $key; // sk-live-999
```

> **Framework での応用**:  
> Laravel や Symfony などのフレームワークの DI コンテナや ORM は、この動的バインドを活用してプライベートプロパティへのアクセスや遅延ロードを実現しています。

---

## 6. 実務高階関数パイプライン (`array_map`, `array_filter`, `array_reduce`)

```php
$products = [
    ['name' => 'Keyboard', 'price' => 18000, 'in_stock' => true],
    ['name' => 'Mouse',    'price' => 5000,  'in_stock' => false],
    ['name' => 'Monitor',  'price' => 45000, 'in_stock' => true],
];

// 1. 在庫あり商品を抽出
$inStock = array_filter($products, fn(array $p): bool => $p['in_stock']);

// 2. 価格リストに変換
$prices = array_map(fn(array $p): int => $p['price'], $inStock);

// 3. 合計金額を算出
$total = array_reduce($prices, fn(int $carry, int $price): int => $carry + $price, 0);

echo "Total in-stock value: JPY {$total}";
```

---

## 7. 他言語エンジニア向け比較表 (PHP vs JavaScript vs Python vs Java vs Rust)

| 項目 | PHP (8.1+) | JavaScript (ES6) | Python | Java (21+) | Rust |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **短縮記法** | `fn($x) => $x * 2` | `x => x * 2` | `lambda x: x*2` | `x -> x * 2` | `\|x\| x * 2` |
| **外部スコープのキャプチャ** | **明示的 (`use`) または `fn`** | 自動 | 自動 (遅延評価) | 自動 (effectively final) | 自動 (借用/move) |
| **外部変数の書き換え** | ⭕ `use (&$var)` で可能 | ⭕ 自由に変更可能 | ⚠️ `nonlocal` が必要 | ❌ 不可 | ⭕ `FnMut` |
| **First-Class Callable** | ⭕ `strlen(...)` (8.1+) | ⭕ 直接関数名 | ⭕ 直接関数名 | ⭕ `String::length` | ⭕ 直接関数名 |
| **スコープ差し替え** | ⭕ `Closure::bind` | ⭕ `.bind()` / `.call()` | ❌ なし | ❌ なし | ❌ なし |

---

## 8. 理解度チェッククイズ & よくある落とし穴

### Q1. 次のコードの出力は何ですか？
```php
$factor = 2;
$calc = function (int $x) use ($factor): int {
    return $x * $factor;
};
$factor = 10;
echo $calc(5);
```
<details>
<summary>▶ 解答と解説</summary>

**正解: 10 (10 × 5 = 50 ではない！)**  
`use ($factor)` は宣言時の値（`2`）を**値渡し（コピー）**でキャプチャします。その後に外側の `$factor` を `10` に書き換えても、クロージャ内部のキャプチャ値は `2` のまま変わりません。もし `50` にしたい場合は、参照渡し `use (&$factor)` と明記する必要があります。
</details>

---

## まとめ

1. **「使い捨ての変換ならアロー関数 `fn()`」**: 外側の変数を自動キャプチャし、式として返却。
2. **「複数行や状態変更なら無名関数 `function () use (&$var)`」**: 参照渡しで外部変数を安全に変更。
3. **「組み込み関数やメソッドの受け渡しなら `First-Class Callable (...)`」**: 文字列を使わず型安全に Closure 化。
