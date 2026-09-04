# Modern PHP Crash Course (For Rust, C#, Go, Java, Python, TS Developers)

Rust, C#, Go, Java, Python, TypeScript などの言語を習得済みのエンジニアが、**最短でモダン PHP (PHP 8.1+ / 8.2+ / 8.3+) をマスターするための実践リファレンス**です。

---

## 🚀 クイックスタート (実行方法)

### 1. PHP のインストール (Windows)
もし PHP が未インストールの場合は、PowerShell で以下を実行してインストールできます：
```powershell
winget install PHP.PHP
# または https://windows.php.net/download/ から zip を解凍して PATH を通す
```

### 2. サンプルコードの実行
```powershell
# 全モジュールを一括実行
php main.php

# または付属スクリプトで実行
.\run.ps1

# 各モジュールを単体実行
php src/01_types_and_classes.php
php src/02_pattern_matching_and_arrays.php
php src/03_exceptions_and_traits.php
php src/04_generators_and_fibers.php
```

---

## 🗺️ 言語対比マッピング早見表 (PHP vs Rust vs C# vs Go vs Java vs Python)

| 概念・機能 | Modern PHP (8.1+) | Rust | C# | Go | Java | Python |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **厳格型付け** | `declare(strict_types=1);` | 静的型 | 静的型 | 静的型 | 静的型 | 型ヒント |
| **不変データ構造** | `readonly class` | `struct` | `record` | `struct` | `record` | `@dataclass(frozen=True)` |
| **コンストラクタ簡略化**| **Property Promotion** | `Self { .. }` | プライマリコンストラクタ | 初期化構文 | 通常代入 | `@dataclass` |
| **パターンマッチング** | **`match ($val)`** 式 | `match val` | `switch` 式 | `switch` | `switch` | `match ... case` |
| **列挙型 (Enum)** | **Backed Enum** (メソッド可)| `enum` | `enum` | `const iota` | `enum` | `Enum` |
| **水平的コード共有** | **Trait** (Mix-in) | Trait | interface デフォルト実装| 埋め込み | interface デフォルト | 多重継承 / Mixin |
| **オプショナル** | `?string` / `string\|null` | `Option<String>` | `string?` | `*string` | `Optional<String>` | `str \| None` |
| **Null安全アクセス** | `?->` (Nullsafe) | `?` | `?.` | `if != nil` | `Optional.map` | `if obj:` |
| **軽量コルーチン** | **Fiber** (PHP 8.1+) | `async/await` | `async/await` | `goroutine` | Virtual Threads | `asyncio` |

---

## ⚠️ 他言語経験者が最もハマる PHP の「罠」と作法

### 1. 先頭の `declare(strict_types=1);` は必須
- PHP は歴史的経緯から、デフォルトでは「文字列 `"10"` を渡しても数値 `10` に自動キャストする」緩い型変換を行います。
- 静的型付け言語の感覚で厳格な型安全性を得るために、**すべての PHP ファイルの 1 行目に `declare(strict_types=1);` を書くのが現代の標準ルール**です。

### 2. `switch` ではなく必ず `match` 式を使う
- 古い `switch` 文は緩い比較（`==`）で評価されるため、`switch ("0") { case 0: ... }` が一致してしまう致命的なバグの温床でした。
- PHP 8.0+ の **`match` 式は厳格比較（`===`）** で値を返すため、Rust や C# の switch 式と完全に同じ挙動になります。

### 3. 配列（Array）は「リスト」と「連想配列（Map）」のハイブリッド
- PHP の `array` は、C# の `List<T>` と `Dictionary<K, V>`、Java の `ArrayList` と `HashMap` の両方の性質を持っています。
- 連想配列を扱う際はキーの存在チェック（`array_key_exists` または `isset` / `??`）を活用します。

### 4. `==` (緩い比較) は禁止、常に `===` (厳格比較) を使う
- JS と同様に、`0 == ""` や `false == 0` が `true` になってしまいます。常に `===` および `!==` を使用してください。

---

## 📁 提供サンプルコードの解説

| ファイル | テーマ | 主な学習内容 |
| :--- | :--- | :--- |
| [`01_types_and_classes.php`](./src/01_types_and_classes.php) | **型システム & Enum** | `strict_types=1`, Constructor Property Promotion, `readonly class`, Backed Enum, Union型 (`int\|string`) |
| [`02_pattern_matching_and_arrays.php`](./src/02_pattern_matching_and_arrays.php) | **match式 & 配列パイプライン** | `match` 式, 名前付き引数 (`name: "Alice"`), `?->` (Nullsafe), `array_map/filter/reduce`, アロー関数 (`fn() =>`) |
| [`03_exceptions_and_traits.php`](./src/03_exceptions_and_traits.php) | **例外 & Trait (Mix-in)** | 独自ドメイン例外, Multi-catch (`catch (A \| B $e)`), `trait` による水平コード合成, 匿名クラス |
| [`04_generators_and_fibers.php`](./src/04_generators_and_fibers.php) | **遅延ストリーム & Fiber** | `yield` によるメモリ効率の高い Generator, PHP 8.1+ `Fiber` によるコルーチン（一時停止・再開） |
| [`05_closures_and_callables.php`](./src/05_closures_and_callables.php) | **クロージャ & Callable** | 無名関数 (`use ($var)` vs `use (&$var)`), アロー関数 (`fn() =>`), PHP 8.1+ First-Class Callable (`strlen(...)`), `Closure::call` |
| [`main.php`](./main.php) | **統合エントリーポイント** | 全モジュールを一括実行するランナー |

> 📖 **PHP クロージャ・アロー関数・First-Class Callable 完全理解ガイド**:
> 無名関数とアロー関数の違い、`use` による値キャプチャと参照キャプチャのメモリモデル、`strlen(...)` 構文の裏側、スコープ動的束縛（`Closure::bind` / `call`）まで完全網羅した解説は [**`LAMBDA.md`**](./LAMBDA.md) を参照してください。

> 🛠️ **Modern Composer & composer.json 完全理解ガイド**:
> PSR-4 オートロードの仕組み、`require` vs `require-dev`、本番最適化（`dump-autoload -o`）まで完全網羅した解説は [**`COMPOSER_GUIDE.md`**](./COMPOSER_GUIDE.md) を参照してください。

---

## ⚙️ VS Code での PHP 開発設定ガイド (`launch.json` & `settings.json`)

### 1. `launch.json` の書き方 (デバッグ起動設定)

```json
{
    "version": "0.2.0",
    "configurations": [
        {
            // ① 【デフォルト】現在アクティブに開いているタブの PHP ファイルを単体実行
            "name": "▶ PHP: Current File",
            "type": "php",
            "request": "launch",
            "program": "${file}",
            "cwd": "${fileDirname}",
            "runtimeExecutable": "php"
        },
        {
            // ② 統合ランナー (main.php) を実行して全モジュールを一括検証
            "name": "▶ PHP: Run main.php (All Modules)",
            "type": "php",
            "request": "launch",
            "program": "${workspaceFolder}/main.php",
            "cwd": "${workspaceFolder}",
            "runtimeExecutable": "php"
        }
    ]
}
```

### 2. `settings.json` の書き方 (ワークスペース設定)

```json
{
    "files.encoding": "utf8",
    "php.validate.enable": true,
    "files.trimTrailingWhitespace": true,
    "files.insertFinalNewline": true
}
```
