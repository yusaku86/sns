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
| `settings/*`, `teams/*` | AppLayout + SettingsLayout |
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

1. マイグレーション
2. Domain Entity + **Unit Test**
3. Repository Interface（Domain層）
4. Eloquent Model（Infrastructure層）
5. Repository 実装（Infrastructure層）
6. UseCase（Application層）+ **Unit Test**
7. FormRequest（Presentation層）
8. Controller（Presentation層）+ **Feature Test**
9. ルーティング（`routes/web.php`）
10. フロントエンド（ページ・コンポーネント）

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

## 参照ドキュメント

- 要件定義: `requirements.md`
- 設計書: `design.md`
