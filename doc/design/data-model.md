# データモデル設計

## テーブル一覧

### users

| カラム | 型 | 備考 |
|--------|----|------|
| id | uuid | PK |
| name | varchar(255) | 表示名 |
| handle | varchar(255) | unique、@ユーザー名（メールの@前 + UUID短縮で自動生成） |
| email | varchar(255) | unique |
| email_verified_at | timestamp | nullable |
| password | varchar(255) | |
| bio | text | nullable |
| header_image | varchar(255) | nullable、ストレージパス |
| profile_image | varchar(255) | nullable、ストレージパス |
| two_factor_secret | varchar(255) | nullable、2FA TOTP シークレット |
| two_factor_recovery_codes | text | nullable |
| two_factor_confirmed_at | timestamp | nullable |
| remember_token | varchar(100) | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

### posts

| カラム | 型 | 備考 |
|--------|----|------|
| id | uuid | PK |
| user_id | uuid | FK → users |
| content | varchar(140) | |
| created_at | timestamp | |
| updated_at | timestamp | |

### likes

| カラム | 型 | 備考 |
|--------|----|------|
| id | uuid | PK |
| user_id | uuid | FK → users |
| post_id | uuid | FK → posts |
| created_at | timestamp | updated_at なし |

### retweets

| カラム | 型 | 備考 |
|--------|----|------|
| id | uuid | PK |
| user_id | uuid | FK → users |
| post_id | uuid | FK → posts |
| created_at | timestamp | updated_at なし |

### replies

| カラム | 型 | 備考 |
|--------|----|------|
| id | uuid | PK |
| post_id | uuid | FK → posts（CASCADE DELETE） |
| user_id | uuid | FK → users（CASCADE DELETE） |
| content | varchar(140) | |
| created_at | timestamp | |
| updated_at | timestamp | |

### follows

| カラム | 型 | 備考 |
|--------|----|------|
| id | uuid | PK |
| follower_id | uuid | FK → users（フォローする側） |
| following_id | uuid | FK → users（フォローされる側） |
| created_at | timestamp | updated_at なし |

### hashtags

| カラム | 型 | 備考 |
|--------|----|------|
| id | uuid | PK |
| name | varchar(255) | unique |
| created_at | timestamp | |
| updated_at | timestamp | |

### hashtag_post（中間テーブル）

| カラム | 型 | 備考 |
|--------|----|------|
| hashtag_id | uuid | FK → hashtags、複合PK |
| post_id | uuid | FK → posts、複合PK |

### post_images

| カラム | 型 | 備考 |
|--------|----|------|
| id | uuid | PK |
| post_id | uuid | FK → posts（CASCADE DELETE） |
| path | varchar(255) | ストレージパス |
| order | tinyint unsigned | 表示順（デフォルト0） |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## ER図（概略）

```
users ──< posts ──< likes
      |         |
      |         ├──< retweets
      |         ├──< replies
      |         ├──< post_images
      |         └──>< hashtags（hashtag_post）
      |
      └──< follows（follower_id / following_id）
```

---

## UUID 設計方針

- 全テーブルの主キーはUUID（文字列型）
- IDの生成はEloquentモデルの `boot()` で `Str::uuid()` を使う
- マイグレーション: `$table->uuid('id')->primary()`
- 外部キー: `$table->foreignUuid('user_id')`

---

## Eloquentモデルの配置

- **実体**: `app/Infrastructure/Eloquent/Models/`（UUID生成 `boot()` あり）
- **薄いラッパー**: `app/Models/`（Laravelが期待するパスのため、Infrastructureクラスを継承するだけ）

`app/Models/` に新規ファイルを作成しない。

---

## Factory・Seeder

### Factory（`database/factories/`）

| ファイル | 対象モデル |
|---------|-----------|
| `PostFactory.php` | `Infrastructure\Eloquent\Models\Post` |
| `UserFactory.php` | `Infrastructure\Eloquent\Models\User` |
| `LikeFactory.php` | `Infrastructure\Eloquent\Models\Like` |
| `ReplyFactory.php` | `Infrastructure\Eloquent\Models\Reply` |

Factory の名前解決は `AppServiceProvider::configureFactories()` でカスタマイズ済み。`App\Infrastructure\Eloquent\Models\Post` → `Database\Factories\PostFactory` と解決される。

### Seeder（`database/seeders/`）

開発用テストデータを生成する。`migrate:fresh --seed` で初期化とシーディングを一括実行。
