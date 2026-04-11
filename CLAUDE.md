# プロジェクトルール

このファイルはClaude Codeに自動で読み込まれます。
実装時は以下のルールを必ず順守してください。

## ドキュメント参照

- 要件定義: `requirements.md`
- 設計書: `design.md`

---

## アーキテクチャルール（Clean Architecture）

### 層の依存方向
```
Presentation → Application → Domain ← Infrastructure
```

依存は必ず内側（Domain）に向けること。外側の層が内側に依存するのは禁止。

### 各層の制約

| 層 | ディレクトリ | 禁止事項 |
|----|-------------|----------|
| Domain | `app/Domain/` | Eloquent・Laravelのクラスをimportしない |
| Application | `app/Application/` | EloquentModelを直接使わない。Interfaceのみに依存 |
| Infrastructure | `app/Infrastructure/` | ドメインロジックを書かない |
| Presentation | `app/Http/` | ビジネスロジックをControllerに書かない |

### UseCaseのルール
- 1クラス1メソッド（`execute()`）
- Controllerから直接Eloquentを呼ばない。必ずUseCaseを経由する

---

## UUID ルール

- 全テーブルの主キーはUUID（文字列型）
- IDの生成はEloquentモデルの `boot()` で行う
- `Str::uuid()` を使用する
- マイグレーションでは `$table->uuid('id')->primary()` を使用する

---

## 命名規則

### PHP（バックエンド）
| 対象 | 規則 | 例 |
|------|------|----|
| クラス名 | PascalCase | `CreatePostUseCase` |
| メソッド名 | camelCase | `execute()` |
| DBカラム名 | snake_case | `user_id`, `created_at` |
| Repositoryの実装クラス | `Eloquent` + Interface名 | `EloquentPostRepository` |

### TypeScript（フロントエンド）
| 対象 | 規則 | 例 |
|------|------|----|
| コンポーネント | PascalCase | `PostCard` |
| ファイル名 | kebab-case | `post-card.tsx` |
| ページファイル | Inertiaのパスに合わせる | `pages/users/show.tsx` |

---

## Inertia.js のルール

- データはコントローラーから `Inertia::render('PageName', [...])` で渡す
- フロントからのリクエストは `router.post()` / `useForm()` を使う
- APIエンドポイント（`/api/*`）は作成しない

---

## テストのルール

### テストフレームワーク
- **Pest**（導入済み）を使用する

### テストの種類と配置

| 種類 | 対象 | 配置 |
|------|------|------|
| Unit Test | Domain Entity, UseCase | `tests/Unit/` |
| Feature Test | Controller, ルーティング | `tests/Feature/` |

### 作成タイミング
- **実装と同時にテストを作成する**（後回し禁止）
- 実装順序の各ステップでテストも合わせて作成する

### Unit Test のルール
- Domain EntityとUseCaseは必ずUnit Testを作成する
- UseCaseのテストではRepositoryをモック（`mock()`）する
- Eloquent・DBに依存しないテストにする

### Feature Test のルール
- 各Controllerのエンドポイントに対してFeature Testを作成する
- 認証が必要なルートは `actingAs()` を使用する
- テスト用DBはSQLiteインメモリを使用する（高速化のため）

### テストの命名規則
```php
// Pest の it() / test() を使用する
it('投稿を作成できる');
it('140文字を超える投稿は作成できない');
it('他のユーザーの投稿は削除できない');
```

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

## 禁止事項

- Controllerにビジネスロジックを書く
- UseCaseからEloquentModelを直接importする
- Domain層にLaravelのクラスをimportする
- `app/Models/` 配下に新規ファイルを作成する（Infrastructure層を使う）
- フロントエンドからAPIルート（`/api/*`）を叩く
- テストを書かずに実装を完了とする
