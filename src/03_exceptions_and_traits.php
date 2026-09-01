<?php
declare(strict_types=1);

/**
 * ============================================================================
 * PHP 03: 例外処理・Trait (Mix-in)・インターフェース (Exceptions & Traits)
 * ============================================================================
 * 
 * 【他言語経験者（Rust, C#, Go, Java, Python）向け要点】
 * 1. Trait (トレイト - PHP 5.4+):
 *    - PHP は単一継承モデルですが、Trait を使うことで **複数のクラスへメソッド実装を水平的に注入 (Mix-in)** できます。
 *    - Rustの Trait、Rubyの Module (include)、Scalaの Trait に近い概念です。
 * 
 * 2. 複数例外キャッチ (Multi-catch - PHP 7.1+):
 *    - `catch (InvalidArgumentException | DomainException $e)` のように 1 箇所の catch 節で複数例外を束ねられます (Java 7+ と同等)。
 * 
 * 3. 匿名クラス (Anonymous Classes - PHP 7.0+):
 *    - Java の無名内部クラスと同様に、使い捨てのインターフェース実装オブジェクトを即座に生成可能。
 */

namespace Sample\OOP;

// ============================================================================
// 1. 自作例外クラスの定義
// ============================================================================
class InsufficientFundsException extends \RuntimeException {
    public function __construct(public readonly float $currentBalance, public readonly float $requestedAmount) {
        parent::__construct(
            sprintf("Cannot withdraw %.2f JPY: Current balance is only %.2f JPY", $requestedAmount, $currentBalance)
        );
    }
}

// ============================================================================
// 2. Trait (ロギング機能を注入する Mix-in)
// ============================================================================
trait LoggerTrait {
    public function log(string $message): void {
        echo sprintf("  [LOG %s] %s\n", date("H:i:s"), $message);
    }
}

trait TimestampsTrait {
    public readonly \DateTimeImmutable $createdAt;

    public function initializeTimestamp(): void {
        // オブジェクト生成時のタイムスタンプを不変オブジェクトで保持
        /** @phpstan-ignore-next-line */
        $this->createdAt = new \DateTimeImmutable();
    }
}

// ============================================================================
// 3. インターフェースと Trait を活用したサービスクラス
// ============================================================================
interface AccountService {
    public function deposit(float $amount): void;
    public function withdraw(float $amount): void;
    public function getBalance(): float;
}

class BankAccount implements AccountService {
    use LoggerTrait; // Trait のメソッドを合成

    private float $balance;

    public function __construct(public readonly string $accountNumber, float $initialBalance = 0.0) {
        $this->balance = max(0.0, $initialBalance);
        $this->log("Account {$this->accountNumber} opened with balance JPY " . number_format($this->balance));
    }

    public function deposit(float $amount): void {
        if ($amount <= 0) {
            throw new \InvalidArgumentException("Deposit amount must be positive");
        }
        $this->balance += $amount;
        $this->log("Deposited JPY " . number_format($amount) . " -> New Balance: JPY " . number_format($this->balance));
    }

    public function withdraw(float $amount): void {
        if ($amount <= 0) {
            throw new \InvalidArgumentException("Withdrawal amount must be positive");
        }
        if ($this->balance < $amount) {
            throw new InsufficientFundsException($this->balance, $amount);
        }
        $this->balance -= $amount;
        $this->log("Withdrew JPY " . number_format($amount) . " -> Remaining Balance: JPY " . number_format($this->balance));
    }

    public function getBalance(): float {
        return $this->balance;
    }
}

// ============================================================================
// 実行関数
// ============================================================================
function run(): void {
    echo "--- 1. Traits and Object-Oriented Composition ---\n";
    $account = new BankAccount("AC-12345", 50000.0);
    $account->deposit(30000.0);

    echo "\n--- 2. Exception Handling with Custom Domain Exceptions ---\n";
    try {
        // 残高不足の出金を試行
        $account->withdraw(100000.0);
    } catch (InsufficientFundsException $e) {
        echo "  [Caught Domain Exception] " . $e->getMessage() . "\n";
    } catch (\InvalidArgumentException $e) {
        echo "  [Caught Argument Error] " . $e->getMessage() . "\n";
    }

    echo "\n--- 3. Anonymous Classes (Java-style Interface Mocks) ---\n";
    // テストや使い捨てモックに最適な匿名クラス
    $mockAccount = new class implements AccountService {
        public function deposit(float $amount): void {}
        public function withdraw(float $amount): void {}
        public function getBalance(): float { return 999999.0; }
    };
    echo "  Mock Account Balance: JPY " . number_format($mockAccount->getBalance()) . "\n";
}

// 直接実行された場合
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    run();
}
