# 投稿機能 設計

## 概要

投稿の作成・削除・タイムライン表示・全体一覧・投稿詳細を提供する。

---

## エンティティ

### Post（`app/Domain/Post/Entities/Post.php`）

```php
class Post implements JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $userId,
        public readonly string $userName,
        public readonly string $userHandle,
        public readonly string $content,
        public readonly \DateTimeImmutable $createdAt,
        // 集計
        public readonly int $likesCount = 0,
        public readonly bool $likedByAuthUser = false,
        public readonly int $repliesCount = 0,
        public readonly int $retweetsCount = 0,
        public readonly bool $retweetedByAuthUser = false,
        // リツイート情報（リツイートとして表示する場合に使用）
        public readonly ?string $retweetId = null,
        public readonly ?string $retweetedByUserName = null,
        public readonly ?string $retweetedByUserHandle = null,
        public readonly ?\DateTimeImmutable $retweetedAt = null,
        // その他
        public readonly array $hashtags = [],
        public readonly ?string $userProfileImageUrl = null,
        public readonly array $images = [],
    ) {}
}
```

`jsonSerialize()` で `createdAt` は `Y/m/d H:i` 形式に変換して返す。

### PostImage（`app/Domain/Post/Entities/PostImage.php`）

```php
class PostImage
{
    public function __construct(
        public readonly string $id,
        public readonly string $postId,
        public readonly string $path,
        public readonly int $order,
    ) {}
}
```

---

## リポジトリインターフェース

`app/Domain/Post/Repositories/PostRepositoryInterface.php`

```php
interface PostRepositoryInterface
{
    public function findById(string $id, ?string $authUserId = null): ?Post;
    public function getTimeline(string $userId, int $limit, ?string $cursor = null): array;
    public function getAll(?string $authUserId = null, int $limit = 20, ?string $cursor = null): array;
    public function getByHashtag(string $hashtagName, ?string $authUserId = null, int $limit = 20, ?string $cursor = null): array;
    public function getByUserId(string $userId, ?string $authUserId = null, int $limit = 20, ?string $cursor = null): array;
    public function save(Post $post): void;
    public function delete(string $id): void;
}
```

---

## ユースケース

### CreatePostUseCase（`app/Application/Post/CreatePostUseCase.php`）

```php
public function execute(
    string $postId,
    string $userId,
    string $userName,
    string $userHandle,
    string $content,
    array $imagePaths = [],
): Post
```

- 正規表現 `/#([\w\p{L}]+)/u` でハッシュタグを抽出し `syncToPost()` で記録
- 画像パスを `PostImageRepository::saveForPost()` で保存
- 投稿後に `PostObserver::created()` → `UpdateTrendingHashtagsJob` がキューへ

### DeletePostUseCase（`app/Application/Post/DeletePostUseCase.php`）

- 投稿者以外が削除しようとした場合は `AuthorizationException`
- 削除後に `PostObserver::deleted()` → `UpdateTrendingHashtagsJob` がキューへ
- `PostObserver::deleting()` で添付画像ファイルをストレージから削除

### GetPostUseCase（`app/Application/Post/GetPostUseCase.php`）

```php
public function execute(string $postId, ?string $authUserId = null): array
// returns: ['post' => Post, 'replies' => Reply[]]
// PostがなければDomainException
```

### GetTimelineUseCase（`app/Application/Timeline/GetTimelineUseCase.php`）

```php
LIMIT = 20

public function execute(string $userId, ?string $cursor = null): array
// returns: ['posts' => Post[], 'nextCursor' => string|null, 'hasMore' => bool]
```

- フォロー中ユーザーおよび自分の投稿・リツイートをマージして日時降順
- カーソルベースのページネーション（`cursor` は前回最後の投稿の `createdAt` ISO 8601形式）
- LIMIT+1 件取得して `hasMore` を判定

### GetExploreUseCase（`app/Application/Explore/GetExploreUseCase.php`）

```php
LIMIT = 20

public function execute(?string $authUserId = null, ?string $cursor = null): array
// returns: ['posts' => Post[], 'nextCursor' => string|null, 'hasMore' => bool]
```

- 全ユーザーの投稿・リツイートをマージして新着順

---

## Infrastructure層

### EloquentPostRepository（`app/Infrastructure/Eloquent/Repositories/EloquentPostRepository.php`）

- `findById()`: `likes_count`, `replies_count`, `retweets_count` をロード
- `getTimeline()`: フォロー関係を JOIN し投稿・リツイートをマージして日時降順
- `getAll()`: 全投稿・リツイートをマージ
- プライベートメソッド `toEntity()` / `toEntityFromRetweet()` / `toImageEntities()` でエンティティ変換

### PostImageStorage（`app/Infrastructure/Storage/PostImageStorage.php`）

- `PostImageStorageInterface` を実装
- `storeAll(UploadedFile[] $files): string[]`
- `storage/local/post_images/` に `UUID.拡張子` で保存

### PostPresenter（`app/Http/Presenters/PostPresenter.php`）

- 画像パスを署名付き一時URL（1時間有効）に変換してからフロントに渡す

---

## Presentation層

### PostController

| メソッド | 処理 |
|---------|------|
| `show(request, postId)` | `GetPostUseCase` を呼び Inertia で `posts/show` を返す |
| `store(StorePostRequest)` | `CreatePostUseCase` を呼び `back()` でリダイレクト |
| `destroy(request, postId)` | `DeletePostUseCase` を呼び `back()` でリダイレクト |

### StorePostRequest

| フィールド | ルール |
|-----------|-------|
| `content` | nullable, max:140 |
| `images` | nullable, array, max:8件 |
| `images.*` | image, mimes:jpeg/png/webp |
| withValidator | テキストまたは画像の少なくとも一方が必須 |

---

## フロントエンド

### PostCard（`resources/js/components/post-card.tsx`）

- 投稿1件を表示（テキスト・画像・ハッシュタグリンク）
- いいね / リツイート / 返信数 のカウント表示と操作
- リツイートの場合「{userName} がリツイート」を上部に表示
- 自分の投稿のみ削除ボタンを表示

### PostForm（`resources/js/components/post-form.tsx`）

- テキスト入力（最大140文字、リアルタイム文字数カウント）
- 画像アップロード（最大8枚、プレビュー・個別削除）
- `useForm` で投稿作成リクエストを送信

### useInfiniteScroll（`resources/js/hooks/use-infinite-scroll.ts`）

- Intersection Observer でスクロール到達時に次ページ取得コールバックを実行
- `timeline.tsx` / `explore.tsx` / `hashtags/show.tsx` で使用
