# Modern Composer & composer.json 完全理解ガイド (Composer Master Guide)

PHP のデファクトスタンダードである依存関係管理ツール **「Composer」** と設定ファイル **「composer.json」** の完全解説書です。

古い時代の `require_once` 地獄から PHP を解放した **PSR-4 オートロード（Autoloading）の仕組み**、開発環境と本番環境の依存切り分け、スクリプト実行、そして **本番デプロイ時の高速化最適化（`dump-autoload -o` など）** までを網羅しています。

---

## 📑 目次

1. [Composer の基本概念とディレクトリ構成](#1-composer-の基本概念とディレクトリ構成)
2. [最重要基本プロパティ (`composer.json`)](#2-最重要基本プロパティ-composerjson)
3. [PSR-4 オートロード完全攻略（名前空間とフォルダのマッピング）](#3-psr-4-オートロード完全攻略名前空間とフォルダのマッピング)
4. [依存パッケージの管理 (`require` vs `require-dev`)](#4-依存パッケージの管理-require-vs-require-dev)
5. [本番デプロイ時のパフォーマンス最適化](#5-本番デプロイ時のパフォーマンス最適化)
6. [実務テンプレートと CLI コマンド早見表](#6-実務テンプレートと-cli-コマンド早見表)

---

## 1. Composer の基本概念とディレクトリ構成

```text
my_php_project/
├── composer.json       # パッケージ定義と依存関係マニフェスト (Git管理)
├── composer.lock       # 実際にインストールされた確定バージョン (Git管理)
├── vendor/             # Composer がダウンロードした外部ライブラリ (Git除外)
│   └── autoload.php    # 全クラスを一発で自動読み込みするオートローダー
└── src/                # あなたが書く PHP ソースコード
```

### 💡 `require_once 'vendor/autoload.php';` の魔法
Composer を使ったプロジェクトでは、すべてのクラスファイルで個別に `require_once` を書く必要はありません。エントリポイントで `require_once 'vendor/autoload.php';` を 1 回呼ぶだけで、名前空間に基づいて必要なクラスがオンデマンドで自動読み込みされます。

---

## 2. 最重要基本プロパティ (`composer.json`)

```json
{
    "name": "mycompany/awesome-service",
    "description": "High performance modern PHP backend service",
    "type": "project",
    "license": "MIT",
    "authors": [
        {
            "name": "Harun",
            "email": "harun@example.com"
        }
    ],
    "require": {
        "php": "^8.2",
        "guzzlehttp/guzzle": "^7.8",
        "monolog/monolog": "^3.5"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.5",
        "phpstan/phpstan": "^1.10"
    },
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    }
}
```

---

## 3. PSR-4 オートロード完全攻略

PSR-4 は、**「名前空間プレフィックス」を「特定のディレクトリ」にマッピングする標準規約**です。

```json
"autoload": {
    "psr-4": {
        "App\\": "src/"
    }
}
```

### 解決ルール
| 完全修飾クラス名 (FQCN) | 物理ファイルパス |
| :--- | :--- |
| `App\User` | `src/User.php` |
| `App\Services\AuthService` | `src/Services/AuthService.php` |
| `App\Models\Entities\Order` | `src/Models/Entities/Order.php` |

> **⚠️ 注意**: 大文字小文字が完全に一致している必要があります（Linux 本番環境でのファイル名不一致エラーを防止）。

---

## 4. 依存パッケージの管理 (`require` vs `require-dev`)

- **`require`**: 本番環境で必須のパッケージ（例: ORM, HTTP クライアント, ロガー）。
- **`require-dev`**: 開発時・CI/CD のみで使用するツール（例: PHPUnit, PHPStan, PHP_CodeSniffer）。

### 本番インストール時の鉄則
本番サーバーにデプロイする際は、開発ツールを除外し、オートロードを最適化してインストールします：
```bash
composer install --no-dev --optimize-autoloader --no-interaction
```

---

## 5. 本番デプロイ時のパフォーマンス最適化

PHP は通常、未ロードのクラスを呼ぶたびにディスク上をファイル探索します。
以下のコマンドを実行すると、**クラス名とファイルパスの静的マッピング配列（クラスマップ）を生成し、ディスク I/O を劇的に削減**します。

```bash
composer dump-autoload -o
# または
composer dump-autoload --classmap-authoritative
```

---

## 6. 実務テンプレートと CLI コマンド早見表

| コマンド | 説明 |
| :--- | :--- |
| **`composer install`** | `composer.lock` に基づいて依存ライブラリを完全再現インストール |
| **`composer update`** | `composer.json` の範囲内で最新版に更新し、`composer.lock` を書き換え |
| **`composer require vendor/package`** | パッケージを追加してインストール |
| **`composer require --dev vendor/package`** | 開発専用パッケージを追加 |
| **`composer remove vendor/package`** | パッケージを削除 |
| **`composer dump-autoload`** | クラスの追加・移動時にオートロードマップを再生成 |
