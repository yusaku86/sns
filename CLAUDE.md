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

## アーキテクチャ

Clean Architecture の4層構成。詳細は `doc/design/architecture.md` を参照。

| 層 | ディレクトリ | 禁止事項 |
|----|-------------|----------|
| Domain | `app/Domain/` | Eloquent・Laravelのクラスをimportしない |
| Application | `app/Application/` | EloquentModelを直接使わない。Interfaceのみに依存 |
| Infrastructure | `app/Infrastructure/` | ドメインロジックを書かない |
| Presentation | `app/Http/` | ビジネスロジックをControllerに書かない |

**重要な規則:**
- 新しいRepositoryを追加したら `AppServiceProvider` に必ずバインドする
- Eloquentモデルの実体は `app/Infrastructure/Eloquent/Models/`。`app/Models/` に新規ファイルを作成しない
- フロントエンドのルーティングは Wayfinder を使用（Ziggy 不使用）

---

## テストのルール

- Unit Test: `tests/Unit/` — DBに依存しない。UseCaseはRepositoryを `mock()` する
- Feature Test: `tests/Feature/` — SQLiteインメモリDB使用。認証ルートは `actingAs()` を使用
- **TDD必須** — 実装コードを書く前にテストを書き、Red → Green → Refactor のサイクルで進める

---

## 実装順序

> **TDD必須**: 各ステップで実装コードより先にテストを書く。

1. **ドキュメント更新**（要件・設計書を先に更新する）
   - 要件変更 → `doc/requirements.md`
   - 新機能 → `doc/design/features/{feature}.md` を作成
   - テーブル変更 → `doc/design/data-model.md`
   - ルート変更 → `doc/design/screens.md`
2. マイグレーション
3. **Unit Test（Domain Entity）** → Domain Entity 実装
4. Repository Interface（Domain層）
5. Eloquent Model（Infrastructure層）
6. Repository 実装（Infrastructure層）
7. **Unit Test（UseCase）** → UseCase 実装（Application層）
8. FormRequest（Presentation層）
9. **Feature Test（Controller）** → Controller 実装（Presentation層）
10. ルーティング（`routes/web.php`）
11. フロントエンド（ページ・コンポーネント）
12. **脆弱性スキャン**（`security-reviewer` サブエージェントで実施）
13. **アーキテクチャレビュー**（`laravel-architecture-reviewer` サブエージェントで実施）

---

## 脆弱性スキャン項目

`security-reviewer` サブエージェントで以下を確認する。

- **IDOR**: 他ユーザーのリソースに直接アクセスできないか
- **認可漏れ**: Controller / UseCase に認可チェックがあるか
- **認証バイパス**: 認証必須ルートに `auth` ミドルウェアが適用されているか
- **SQLインジェクション**: `DB::raw()` / `whereRaw()` にユーザー入力が渡されていないか
- **マスアサインメント**: Eloquentモデルに `$fillable` / `$guarded` が設定されているか
- **XSS**: `{!! !!}` / `dangerouslySetInnerHTML` の使用箇所
- **CSRF保護**: 変更系ルートがミドルウェア対象外になっていないか
- **センシティブデータの露出**: Inertia props にパスワードハッシュ等が含まれていないか
- **ファイルアップロード**: 種別・サイズのバリデーション、保存先がWebルート外であること
- **依存パッケージ**: `composer audit` / `npm audit` で既知CVEがないか

---

## 命名規則

| 対象 | 規則 | 例 |
|------|------|----|
| Repository実装クラス | `Eloquent` + Interface名 | `EloquentPostRepository` |
| TSコンポーネント | PascalCase | `PostCard` |
| TSファイル名 | kebab-case | `post-card.tsx` |
| TSページファイル | Inertiaのパスに合わせる | `pages/users/show.tsx` |

---

## PHPDocコメント規約

`app/` 配下のすべてのクラス（`app/Models/` を除く）に以下を必須とする。

- **クラス**: 責務を1〜2行で説明
- **メソッド**: `@param` / `@return` / `@throws` を型が自明でも省略しない
- **プロパティ**（コンストラクタインジェクション以外）: 1行で説明
- 説明文は日本語で書く。インターフェースのメソッドにも付ける（実装クラスは `{@inheritdoc}` 可）

---

## 参照ドキュメント

- 要件定義: `doc/requirements.md`
- アーキテクチャ設計: `doc/design/architecture.md`
- データモデル: `doc/design/data-model.md`
- 画面・ルーティング: `doc/design/screens.md`
- 機能別設計: `doc/design/features/` （post / like / retweet / reply / follow / hashtag / trending / image / profile / auth）
