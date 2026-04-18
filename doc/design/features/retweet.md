# リツイート機能 設計

## 概要

任意の投稿をリツイートし、タイムラインや全体一覧に混合表示する。操作は冪等（二重リツイートしても1件）。

---

## エンティティ

### Retweet（`app/Domain/Retweet/Entities/Retweet.php`）

```php
class Retweet
{
    public function __construct(
        public readonly string $id,
        public readonly string $userId,
        public readonly string $postId,
        public readonly \DateTimeImmutable $createdAt,
    ) {}
}
```

### Post エンティティとの関係

リツイートはフィードに「元投稿のコンテンツ」として表示される。`Post` エンティティが以下のリツイート情報フィールドを持つ：

| フィールド | 説明 |
|-----------|------|
| `retweetId` | Retweet テーブルの ID（リツイートとして表示する場合に設定） |
| `retweetedByUserName` | リツイートしたユーザーの表示名 |
| `retweetedByUserHandle` | リツイートしたユーザーの handle |
| `retweetedAt` | リツイートした日時 |

これらが `null` の場合は通常の投稿として表示される。

---

## リポジトリインターフェース

`app/Domain/Retweet/Repositories/RetweetRepositoryInterface.php`

```php
interface RetweetRepositoryInterface
{
    public function exists(string $userId, string $postId): bool;
    public function save(string $userId, string $postId): void;
    public function delete(string $userId, string $postId): void;
}
```

---

## ユースケース

### RetweetPostUseCase（`app/Application/Retweet/RetweetPostUseCase.php`）

- `RetweetRepositoryInterface::exists()` を確認し、まだリツイートしていなければ `save()`
- 二重リツイートは冪等に処理

### UnretweetPostUseCase（`app/Application/Retweet/UnretweetPostUseCase.php`）

- `RetweetRepositoryInterface::delete()` を呼ぶ

---

## Infrastructure層

### EloquentPostRepository でのリツイート混合表示

`getTimeline()` / `getAll()` では、投稿とリツイートを SQL レベルでマージして日時降順に並べる。

- 投稿: `posts` テーブルを `created_at` 降順で取得
- リツイート: `retweets` テーブルを `created_at` 降順で取得し、関連する `posts` の内容を JOIN
- 両者を UNION して日時降順にソート

リツイートは `toEntityFromRetweet()` でエンティティ変換し、`retweetId`, `retweetedByUserName` 等を設定する。

---

## Presentation層

### RetweetController

| メソッド | パス | 処理 |
|---------|------|------|
| `store(request, post)` | POST `/posts/{post}/retweet` | `RetweetPostUseCase` を実行し `back()` |
| `destroy(request, post)` | DELETE `/posts/{post}/retweet` | `UnretweetPostUseCase` を実行し `back()` |

---

## フロントエンド

`PostCard` のリツイートボタンから操作する。

- `post.retweetedByAuthUser` フラグでボタン状態（filled / outline）を切り替え
- `post.retweetsCount` をボタン横に表示
- `post.retweetedByUserName` が設定されている場合、カード上部に「{name} がリツイート」のバッジを表示
- リツイート操作後は `router.reload()` でページ部分更新（スクロール位置を保持）
