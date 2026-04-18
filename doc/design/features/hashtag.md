# ハッシュタグ機能 設計

## 概要

投稿本文から `#タグ` を自動抽出し、タグ別の投稿一覧ページを提供する。トレンド表示は [trending.md](trending.md) を参照。

---

## テーブル設計

```sql
-- ハッシュタグマスター
CREATE TABLE hashtags (
    id         CHAR(36)     PRIMARY KEY,
    name       VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- 投稿とハッシュタグの多対多
CREATE TABLE hashtag_post (
    hashtag_id CHAR(36) NOT NULL REFERENCES hashtags(id),
    post_id    CHAR(36) NOT NULL REFERENCES posts(id),
    PRIMARY KEY (hashtag_id, post_id)
);
```

---

## エンティティ

### Hashtag（`app/Domain/Hashtag/Entities/Hashtag.php`）

```php
class Hashtag implements JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly int $postsCount = 0,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'postsCount' => $this->postsCount,
        ];
    }
}
```

---

## リポジトリインターフェース

`app/Domain/Hashtag/Repositories/HashtagRepositoryInterface.php`

```php
interface HashtagRepositoryInterface
{
    public function findOrCreateByName(string $name): Hashtag;

    /** @param string[] $names */
    public function syncToPost(array $names, string $postId): void;

    /**
     * 投稿数の多いハッシュタグを上位 $limit 件返す
     * @return Hashtag[]
     */
    public function getTrending(int $limit = 5): array;
}
```

---

## ハッシュタグの抽出

`CreatePostUseCase` で正規表現を使って本文から抽出する。

```php
preg_match_all('/#([\w\p{L}]+)/u', $content, $matches);
$names = $matches[1]; // ['タグ名', ...]
```

抽出後:
1. `HashtagRepository::findOrCreateByName()` で各タグを取得 or 作成
2. `HashtagRepository::syncToPost()` で `hashtag_post` 中間テーブルを更新

---

## ユースケース

### GetHashtagPostsUseCase（`app/Application/Hashtag/GetHashtagPostsUseCase.php`）

```php
LIMIT = 20

public function execute(
    string $hashtagName,
    ?string $authUserId = null,
    ?string $cursor = null,
): array
// returns: ['posts' => Post[], 'nextCursor' => string|null, 'hasMore' => bool]
```

- `PostRepository::getByHashtag()` を呼びカーソルページネーションで投稿を取得

---

## Infrastructure層

### EloquentHashtagRepository

```php
public function findOrCreateByName(string $name): Hashtag
// firstOrCreate() で存在しなければ UUID を生成して作成

public function syncToPost(array $names, string $postId): void
// Hashtag を取得し posts()->sync() で中間テーブルを更新

public function getTrending(int $limit = 5): array
// withCount('posts')->orderByDesc('posts_count')->limit($limit)
```

### EloquentPostRepository での絞り込み

```php
public function getByHashtag(string $hashtagName, ...): array
// hashtag_post 中間テーブルを経由して投稿を取得
// カーソルページネーション対応
```

---

## Presentation層

### HashtagController（`app/Http/Controllers/HashtagController.php`）

| メソッド | パス | 処理 |
|---------|------|------|
| `show(request, hashtag)` | GET `/hashtags/{hashtag}` | `GetHashtagPostsUseCase` を呼び Inertia で `hashtags/show` を返す |

---

## フロントエンド

### PostCard でのハッシュタグリンク化

`PostCard` で本文中の `#タグ` を `<Link href="/hashtags/{name}">` に変換して表示する。

### ハッシュタグ詳細ページ（`resources/js/pages/hashtags/show.tsx`）

- `#{hashtagName}` の投稿一覧をタイムライン形式で表示
- `useInfiniteScroll` で無限スクロールページネーション
