# 画面・ルーティング設計

## 画面一覧

| 画面 | パス | 認証 | レイアウト |
|------|------|------|-----------|
| ランディング | `/welcome` | 不要 | なし |
| ログイン | `/login` | 不要 | AuthLayout |
| 新規登録 | `/register` | 不要 | AuthLayout |
| メール認証 | `/email/verify` | 必要 | AuthLayout |
| パスワードリセット | `/forgot-password` 等 | 不要 | AuthLayout |
| 二要素認証 | `/two-factor-challenge` | 必要 | AuthLayout |
| タイムライン | `/` | 必要 | AppLayout |
| 全体投稿一覧 | `/explore` | 不要 | AppLayout |
| 投稿詳細 | `/posts/{post}` | 不要（返信投稿は必要） | AppLayout |
| ハッシュタグ詳細 | `/hashtags/{hashtag}` | 不要 | AppLayout |
| ユーザープロフィール | `/users/{user}` | 不要 | AppLayout |
| 設定（プロフィール） | `/settings/profile` | 必要 | AppLayout + SettingsLayout |
| 設定（セキュリティ） | `/settings/security` | 必要 | AppLayout + SettingsLayout |
| 設定（外観） | `/settings/appearance` | 必要 | AppLayout + SettingsLayout |

---

## ルーティング定義（`routes/web.php`）

### 認証ルート（Laravel Fortify が自動提供）

| メソッド | パス | 概要 |
|----------|------|------|
| GET | `/login` | ログイン画面 |
| POST | `/login` | ログイン処理（レート制限: 5/分） |
| POST | `/logout` | ログアウト処理 |
| GET | `/register` | 新規登録画面 |
| POST | `/register` | 新規登録処理 |
| GET/POST | `/two-factor-challenge` | 2FA 認証（レート制限: 5/分） |
| GET/POST | `/forgot-password` | パスワードリセット要求 |
| GET/POST | `/reset-password` | パスワードリセット |

### 認証不要ルート（Throttle: 60 req/分）

| メソッド | パス | コントローラー | ルート名 |
|----------|------|--------------|---------|
| GET | `/explore` | `ExploreController@index` | `explore` | クエリパラメータ `?q=` で投稿検索（省略時は全体一覧）|
| GET | `/users/{user}` | `UserController@show` | `users.show` |
| GET | `/posts/{post}` | `PostController@show` | `posts.show` |
| GET | `/hashtags/{hashtag}` | `HashtagController@show` | `hashtags.show` |

### 認証必須ルート（Throttle: 60 req/分、タイムラインは 120 req/分）

| メソッド | パス | コントローラー | ルート名 |
|----------|------|--------------|---------|
| GET | `/` | `TimelineController@index` | `timeline` |
| GET | `/dashboard` | リダイレクト → `/` | `dashboard` |
| POST | `/posts` | `PostController@store` | `posts.store` |
| DELETE | `/posts/{post}` | `PostController@destroy` | `posts.destroy` |
| POST | `/posts/{post}/replies` | `ReplyController@store` | `replies.store` |
| POST | `/posts/{post}/like` | `LikeController@store` | `likes.store` |
| DELETE | `/posts/{post}/like` | `LikeController@destroy` | `likes.destroy` |
| POST | `/posts/{post}/retweet` | `RetweetController@store` | `retweets.store` |
| DELETE | `/posts/{post}/retweet` | `RetweetController@destroy` | `retweets.destroy` |
| PUT | `/users/{user}` | `UserController@update` | `users.update` |
| POST | `/users/{user}/follow` | `FollowController@store` | `follows.store` |
| DELETE | `/users/{user}/follow` | `FollowController@destroy` | `follows.destroy` |

---

## レイアウト自動割り当て（`resources/js/app.tsx`）

| ページパス | レイアウト |
|-----------|-----------|
| `welcome` | なし |
| `auth/*` | AuthLayout |
| `settings/*` | AppLayout + SettingsLayout |
| その他 | AppLayout |

---

## フロントエンドのルーティング（Wayfinder）

Ziggy ではなく **Wayfinder** を使用する。`resources/js/routes/` と `resources/js/actions/` はビルド時に自動生成（`.gitignore` 対象）。

```typescript
// URL文字列が必要な場合（useForm().post() など）
import { store } from '@/routes/posts';
post(store.url());

// Inertia Link の href に渡す場合
import { timeline } from '@/routes';
<Link href={timeline()} />

// パスパラメータがある場合
import { show } from '@/routes/users';
<Link href={show.url(userId)} />
```

Wayfinder の再生成コマンド:

```bash
./vendor/bin/sail artisan wayfinder:generate
```
