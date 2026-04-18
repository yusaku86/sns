# 返信（リプライ）機能 設計

## 概要

任意の投稿に対してリプライを作成する。返信の返信（ネスト）は非対応。

---

## テーブル設計

```sql
CREATE TABLE replies (
    id         CHAR(36)     PRIMARY KEY,
    post_id    CHAR(36)     NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
    user_id    CHAR(36)     NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    content    VARCHAR(140) NOT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

- `post_id` の CASCADE DELETE で、投稿削除時に返信も自動削除

---

## エンティティ

### Reply（`app/Domain/Reply/Entities/Reply.php`）

```php
class Reply implements JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $postId,
        public readonly string $userId,
        public readonly string $userName,
        public readonly string $userHandle,
        public readonly string $content,
        public readonly \DateTimeImmutable $createdAt,
        // 元投稿の文脈（プロフィールページの返信タブで使用）
        public readonly ?string $originalPostContent = null,
        public readonly ?string $originalPostUserName = null,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'id'         => $this->id,
            'postId'     => $this->postId,
            'userId'     => $this->userId,
            'userName'   => $this->userName,
            'userHandle' => $this->userHandle,
            'content'    => $this->content,
            'createdAt'  => $this->createdAt->format('Y/m/d H:i'),
        ];
    }
}
```

---

## Post エンティティへの変更

`Post` エンティティに `repliesCount` フィールドを追加し、投稿カードで返信数を表示する。

```php
public readonly int $repliesCount = 0,
```

---

## リポジトリインターフェース

`app/Domain/Reply/Repositories/ReplyRepositoryInterface.php`

```php
interface ReplyRepositoryInterface
{
    /** @return Reply[] */
    public function getByPostId(string $postId): array;

    /** @return Reply[] */
    public function getByUserId(string $userId, int $limit = 20): array;

    public function save(Reply $reply): void;
}
```

---

## ユースケース

### CreateReplyUseCase（`app/Application/Reply/CreateReplyUseCase.php`）

```php
public function execute(
    string $postId,
    string $userId,
    string $userName,
    string $userHandle,
    string $content,
): Reply
```

- `PostRepository::findById()` で投稿の存在を確認（存在しなければ `DomainException`）
- `Reply` エンティティを生成して `ReplyRepository::save()` を呼ぶ

### GetPostUseCase（`app/Application/Post/GetPostUseCase.php`）

```php
public function execute(string $postId, ?string $authUserId = null): array
// returns: ['post' => Post, 'replies' => Reply[]]
```

- 投稿詳細と返信一覧をまとめて返す
- `ReplyRepository::getByPostId()` で投稿に紐づく返信を取得（古い順）

---

## Presentation層

### ReplyController（`app/Http/Controllers/ReplyController.php`）

| メソッド | パス | 処理 |
|---------|------|------|
| `store(StoreReplyRequest, post)` | POST `/posts/{post}/replies` | `CreateReplyUseCase` を実行し `back()` |

### StoreReplyRequest

| フィールド | ルール |
|-----------|-------|
| `content` | required, max:140 |

---

## フロントエンド

### 投稿詳細ページ（`resources/js/pages/posts/show.tsx`）

- 元の投稿を `PostCard` で表示
- 認証済みユーザーには返信フォームを表示
- 返信一覧を古い順でタイムライン形式に表示

### PostCard での返信数表示

- `post.repliesCount` を `MessageCircle` アイコン横に表示
- クリックで投稿詳細ページ（`/posts/{id}`）へ遷移

### ユーザープロフィール

- `GetUserProfileUseCase` が `ReplyRepository::getByUserId()` を呼び、プロフィールページの「返信」タブに表示
- 返信と元投稿の文脈情報（`originalPostContent`, `originalPostUserName`）を一緒に表示
