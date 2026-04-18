# いいね機能 設計

## 概要

任意の投稿にいいねを付けたり取り消したりする。操作は冪等（二重いいねしても1件）。

---

## エンティティ

### Like（`app/Domain/Like/Entities/Like.php`）

```php
class Like
{
    public function __construct(
        public readonly string $id,
        public readonly string $userId,
        public readonly string $postId,
        public readonly \DateTimeImmutable $createdAt,
    ) {}
}
```

---

## リポジトリインターフェース

`app/Domain/Like/Repositories/LikeRepositoryInterface.php`

```php
interface LikeRepositoryInterface
{
    public function exists(string $userId, string $postId): bool;
    public function save(string $userId, string $postId): void;
    public function delete(string $userId, string $postId): void;

    /** @return Post[] */
    public function getLikedPostsByUserId(
        string $userId,
        ?string $authUserId = null,
        int $limit = 20,
    ): array;
}
```

---

## ユースケース

### LikePostUseCase（`app/Application/Like/LikePostUseCase.php`）

- `LikeRepositoryInterface::exists()` を確認し、まだいいねしていなければ `save()`
- 二重いいねは冪等に処理（例外を投げない）

### UnlikePostUseCase（`app/Application/Like/UnlikePostUseCase.php`）

- `LikeRepositoryInterface::delete()` を呼ぶ

---

## Presentation層

### LikeController

| メソッド | パス | 処理 |
|---------|------|------|
| `store(request, post)` | POST `/posts/{post}/like` | `LikePostUseCase` を実行し `back()` |
| `destroy(request, post)` | DELETE `/posts/{post}/like` | `UnlikePostUseCase` を実行し `back()` |

---

## フロントエンド

`PostCard` のいいねボタンから Inertia `router.post()` / `router.delete()` で操作する。

- `post.likedByAuthUser` フラグでボタンの状態（filled / outline）を切り替え
- `post.likesCount` をボタン横に表示
- いいね操作後は `router.reload()` でページ部分更新（スクロール位置を保持）

---

## ユーザープロフィールでのいいね一覧

`GetUserProfileUseCase` が `LikeRepository::getLikedPostsByUserId()` を呼び、プロフィールページの「いいね」タブに投稿一覧を表示する。
