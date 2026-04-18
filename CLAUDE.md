# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## 開発コマンド

```bash
# DB初期化＋シーダー
./vendor/bin/sail artisan migrate:fresh --seed

# PHPコードスタイル修正
./vendor/bin/sail bin pint

# Wayfinder（TypeScriptルート定義）再生成
./vendor/bin/sail artisan wayfinder:generate

# Eloquentモデルの型定義（PHPDoc）を再生成
# LaraStan でモデル関連の型推論エラーが出たときに実行する
./vendor/bin/sail artisan ide-helper:models -RW
```

---

## アーキテクチャ概要

### レイヤー構成（Clean Architecture）

```
Presentation → Application → Domain ← Infrastructure
```

| 層 | ディレクトリ | 禁止事項 |
|----|-------------|----------|
| Domain | `app/Domain/` | Eloquent・Laravelのクラスをimportしない |
| Application | `app/Application/` | EloquentModelを直接使わない。Interfaceのみに依存 |
| Infrastructure | `app/Infrastructure/` | ドメインロジックを書かない |
| Presentation | `app/Http/` | ビジネスロジックをControllerに書かない |

### DI（依存性注入）の登録

`app/Providers/AppServiceProvider.php` でRepositoryのInterfaceと実装クラスをバインドしている。新しいRepositoryを追加したら必ずここに登録する。

### Eloquentモデルの配置

- **実体**: `app/Infrastructure/Eloquent/Models/`（UUID生成 `boot()` あり）
- **薄いラッパー**: `app/Models/`（Laravelが期待するパスのため、Infrastructureクラスを継承するだけ）

`app/Models/` に新規ファイルを作成しない。

### Factoryの名前解決

`AppServiceProvider::configureFactories()` でカスタマイズ済み。`App\Infrastructure\Eloquent\Models\Post` → `Database\Factories\PostFactory` と解決される。

---

## フロントエンド

### ルーティング（Wayfinder）

Ziggyではなく **Wayfinder** を使用。`resources/js/routes/` と `resources/js/actions/` はビルド時に自動生成（`.gitignore` 対象）。

```typescript
// URL文字列が必要な場合（useForm().post() など）
import { store } from '@/routes/posts';
post(store.url());

// Inertia Link の href に渡す場合
import { timeline } from '@/routes';
<Link href={timeline()} />
```

### レイアウト自動割り当て（`resources/js/app.tsx`）

| ページパス | レイアウト |
|---|---|
| `welcome` | なし |
| `auth/*` | AuthLayout |
| `settings/*` | AppLayout + SettingsLayout |
| その他 | AppLayout |

### ドメインエンティティのJSON変換

`DateTimeImmutable` などJSON化できないプロパティを持つEntityには `JsonSerializable` を実装する（`app/Domain/Post/Entities/Post.php` が参考実装）。

---

## テストのルール

- Unit Test: `tests/Unit/` — DBに依存しない。UseCaseはRepositoryを `mock()` する
- Feature Test: `tests/Feature/` — SQLiteインメモリDB使用。認証ルートは `actingAs()` を使用
- **TDD必須** — 実装コードを書く前にテストを書き、Red → Green → Refactor のサイクルで進める

---

## UUID ルール

- 全テーブルの主キーはUUID（文字列型）
- IDの生成はEloquentモデルの `boot()` で `Str::uuid()` を使う
- マイグレーション: `$table->uuid('id')->primary()`
- 外部キー: `$table->foreignUuid('user_id')`

---

## 実装順序

新機能を追加する際は以下の順序で実装する。

> **TDD必須**: 各ステップで実装コードより先にテストを書く（Red → Green → Refactor）。テストが Red になることを確認してから実装に進む。

1. マイグレーション
2. **Unit Test（Domain Entity）** → Domain Entity 実装
3. Repository Interface（Domain層）
4. Eloquent Model（Infrastructure層）
5. Repository 実装（Infrastructure層）
6. **Unit Test（UseCase）** → UseCase 実装（Application層）
7. FormRequest（Presentation層）
8. **Feature Test（Controller）** → Controller 実装（Presentation層）
9. ルーティング（`routes/web.php`）
10. フロントエンド（ページ・コンポーネント）
11. **脆弱性スキャン（security-reviewer サブエージェント）**
    - 実装完了後、`security-reviewer` サブエージェントを起動してスキャンを実施する
    - スキャン対象と確認項目は下記「脆弱性スキャン項目」を参照
12. **アーキテクチャレビュー（laravel-architecture-reviewer サブエージェント）**
    - 実装完了後、`laravel-architecture-reviewer` サブエージェントを起動してレビューを実施する
    - Clean Architecture の層境界違反・Laravel ベストプラクティス違反・アーキテクチャ境界の逸脱がないか確認する

---

## 脆弱性スキャン項目

実装完了後、以下の項目を `security-reviewer` サブエージェントでスキャンする。

### 認証・認可
- **IDOR（Insecure Direct Object Reference）**: 他ユーザーのリソースに直接アクセスできないか（UUID使用でも Policy/Gate チェックが必要）
- **認可漏れ**: Controller / UseCase に `authorize()` または Policy チェックが実装されているか
- **認証バイパス**: 認証必須ルートに `auth` ミドルウェアが漏れなく適用されているか

### 入力バリデーション・インジェクション
- **SQLインジェクション**: `DB::raw()` や `whereRaw()` にユーザー入力が直接渡されていないか
- **マスアサインメント**: Eloquent モデルに `$fillable` または `$guarded` が設定されているか
- **バリデーション漏れ**: FormRequest で全入力フィールドにルールが定義されているか

### XSS・出力エスケープ
- **XSS**: Blade テンプレートで `{!! !!}` を使用している箇所（意図的な場合はコメントで明記）
- **React での dangerouslySetInnerHTML**: 使用箇所がないか、または適切にサニタイズされているか

### CSRF・リクエスト保護
- **CSRF保護**: POST/PUT/PATCH/DELETE ルートが `VerifyCsrfToken` ミドルウェアの対象外になっていないか
- **レート制限**: 認証エンドポイントや公開投稿 API に `throttle` が設定されているか

### データ露出
- **センシティブデータの露出**: API レスポンス（`toArray()` / Inertia props）にパスワードハッシュや内部IDが含まれていないか
- **エラーメッセージ**: 本番環境でスタックトレースや詳細なエラーが露出しない設定になっているか

### ファイル・依存関係
- **ファイルアップロード**: ファイル種別・サイズのバリデーション、保存先がWebルートの外であること（該当機能がある場合）
- **依存パッケージの既知脆弱性**: `composer audit` / `npm audit` で既知のCVEがないか

---

## 命名規則

| 対象 | 規則 | 例 |
|------|------|----|
| Repository実装クラス | `Eloquent` + Interface名 | `EloquentPostRepository` |
| TSコンポーネント | PascalCase | `PostCard` |
| TSファイル名 | kebab-case | `post-card.tsx` |
| TSページファイル | Inertiaのパスに合わせる | `pages/users/show.tsx` |

---

## 禁止事項

- Controllerにビジネスロジックを書く
- UseCaseからEloquentModelを直接importする
- Domain層にLaravelのクラスをimportする
- `app/Models/` 配下に新規ファイルを作成する（Infrastructure層を使う）
- フロントエンドからAPIルート（`/api/*`）を叩く
- テストより先に実装コードを書く（TDD違反）

---

## PHPDocコメント規約

プロジェクト内のすべてのPHPクラス（Laravelデフォルト・ライブラリファイルを除く）は、以下のPHPDocを必須とする。

### 対象ファイル

`app/` 配下の以下ディレクトリ（`app/Models/` はInfrastructureの薄いラッパーのため除外）:

- `app/Domain/`
- `app/Application/`
- `app/Infrastructure/`
- `app/Http/`
- `app/Jobs/`
- `app/Policies/`
- `app/Providers/`
- `app/Rules/`
- `app/Support/`
- `app/Actions/`
- `app/Concerns/`
- `app/Enums/`
- `app/Notifications/`

### 必須コメントの種類

#### クラスレベル

```php
/**
 * クラスの責務を1〜2行で説明する。
 */
class FooUseCase
```

#### メソッド

```php
/**
 * メソッドが何をするかを1行で説明する。
 *
 * @param SomeType $param 説明
 * @return ReturnType 説明
 * @throws SomeException 例外が発生する条件
 */
public function handle(SomeType $param): ReturnType
```

#### プロパティ（コンストラクタインジェクション以外）

```php
/** プロパティの説明 */
private string $foo;
```

### ルール

- コンストラクタの引数にPHPDocは不要（型宣言で十分）
- `@param` / `@return` は型が自明でも省略しない
- 説明文は日本語で書く
- インターフェースのメソッドにもPHPDocを付ける（実装クラスは `{@inheritdoc}` 可）

---

## 参照ドキュメント

- 要件定義: `requirements.md`
- 設計書: `design.md`
