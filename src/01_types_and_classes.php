<?php
declare(strict_types=1);

/**
 * ============================================================================
 * PHP 01: 型システム・クラス・不変性・Enum (Types, Classes, Immutability, Enum)
 * ============================================================================
 * 
 * 【他言語経験者（Rust, C#, Go, Java, Python）向け要点】
 * 1. declare(strict_types=1); (厳格な型チェック):
 *    - PHPのデフォルトは暗黙の型変換が働きますが、ファイルの先頭にこれを宣言すると、
 *      Java/C#/Rust と同様に厳密な型検査が強制されます (モダンPHPの必須作法)。
 * 
 * 2. Constructor Property Promotion (コンストラクタ引数プロパティ宣言 - PHP 8.0+):
 *    - C#のプライマリコンストラクタや TypeScript と同様に、引数でアクセス修飾子をつけると
 *      プロパティの宣言と代入を 1 行で自動処理します。
 * 
 * 3. readonly プロパティ & readonly クラス (PHP 8.1+ / 8.2+):
 *    - 不変データ構造 (C# record, Java record, Rust struct 相当)。
 *    - 初期化後の変更をコンパイラレベルで完全に禁止します。
 * 
 * 4. Backed Enum (PHP 8.1+):
 *    - Rust / C# / Java と同様に、文字列や整数の値（Backing value）やメソッドを持てる安全な列挙型。
 * 
 * 5. Union型 (A|B) & Intersection型 (A&B) (PHP 8.0+ / 8.1+):
 *    - `string|int` や `Countable&Iterator` のような高度な合成型をネイティブサポート。
 */

namespace Sample\Types;

// ============================================================================
// 1. Backed Enum の定義 (メソッド・値付き)
// ============================================================================
enum UserRole: string {
    case Admin = "admin";
    case Developer = "developer";
    case Viewer = "viewer";

    public function canEdit(): bool {
        return match ($this) {
            self::Admin, self::Developer => true,
            self::Viewer => false,
        };
    }
}

// ============================================================================
// 2. 不変データキャリア (readonly クラス - C# record / Java record 相当)
// ============================================================================
readonly class UserProfile {
    /**
     * Constructor Property Promotion: プロパティ宣言と代入を同時に行う
     * @param string[] $tags
     */
    public function __construct(
        public string $id,
        public string $name,
        public int $age,
        public UserRole $role,
        public array $tags = [],
    ) {}

    public function isAdult(): bool {
        return $this->age >= 18;
    }
}

// ============================================================================
// 3. Union型 と Intersection型の関数
// ============================================================================
function formatIdentifier(string|int $id): string {
    if (is_int($id)) {
        return sprintf("ID_NUM_%06d", $id);
    }
    return "ID_STR_" . strtoupper($id);
}

// ============================================================================
// 実行関数
// ============================================================================
function run(): void {
    echo "--- 1. Strict Typing and Union Types ---\n";
    echo "Formatted ID (int):    " . formatIdentifier(42) . "\n";
    echo "Formatted ID (string): " . formatIdentifier("usr_alpha") . "\n";

    echo "\n--- 2. Readonly Class & Constructor Promotion ---\n";
    $user = new UserProfile(
        id: "u-101",
        name: "Alice",
        age: 25,
        role: UserRole::Developer,
        tags: ["backend", "php", "rust"]
    );

    echo "User Name:    " . $user->name . "\n";
    echo "User Role:    " . $user->role->value . "\n";
    echo "Can Edit?:    " . ($user->role->canEdit() ? "Yes" : "No") . "\n";
    echo "Is Adult?:    " . ($user->isAdult() ? "Yes" : "No") . "\n";
    echo "User Tags:    [" . implode(", ", $user->tags) . "]\n";

    echo "\n--- 3. Backed Enum Matching ---\n";
    $roleFromRaw = UserRole::tryFrom("admin");
    if ($roleFromRaw !== null) {
        echo "Successfully parsed enum from 'admin': " . $roleFromRaw->name . "\n";
    }
}

// 直接実行された場合
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    run();
}
