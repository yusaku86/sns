# SNSアプリケーション 設計書

## アーキテクチャ方針

### Clean Architecture

依存の方向を内側に向け、ドメインロジックをフレームワークから独立させる。

```
[Presentation] → [Application] → [Domain] ← [Infrastructure]
```

| 層 | ディレクトリ | 役割 |
|----|-------------|------|
| Domain | `app/Domain/` | ビジネスエンティティ・リポジトリインターフェース（純粋PHP） |
| Application | `app/Application/` | ユースケース（業務ロジックの調整） |
| Infrastructure | `app/Infrastructure/` | Eloquentモデル・リポジトリ実装 |
| Presentation | `app/Http/` | コントローラー・フォームリクエスト |

---

## ディレクトリ構成

### バックエンド

```
app/
├── Domain/                                  ← ドメイン層（フレームワーク非依存）
│   ├── User/
│   │   ├── Entities/User.php
│   │   └── Repositories/UserRepositoryInterface.php
│   ├── Post/
│   │   ├── Entities/Post.php
│   │   └── Repositories/PostRepositoryInterface.php
│   ├── Like/
│   │   ├── Entities/Like.php
│   │   └── Repositories/LikeRepositoryInterface.php
│   ├── Follow/
│   │   ├── Entities/Follow.php
│   │   └── Repositories/FollowRepositoryInterface.php
│   └── Reply/                               ← 【新規】返信ドメイン
│       ├── Entities/Reply.php
│       └── Repositories/ReplyRepositoryInterface.php
│
├── Application/                             ← アプリケーション層（ユースケース）
│   ├── Post/
│   │   ├── CreatePostUseCase.php
│   │   ├── DeletePostUseCase.php
│   │   └── GetPostUseCase.php               ← 【新規】投稿詳細取得
│   ├── Like/
│   │   ├── LikePostUseCase.php
│   │   └── UnlikePostUseCase.php
│   ├── Follow/
│   │   ├── FollowUserUseCase.php
│   │   └── UnfollowUserUseCase.php
│   ├── Timeline/
│   │   └── GetTimelineUseCase.php
│   ├── Explore/
│   │   └── GetExploreUseCase.php
│   ├── Reply/                               ← 【新規】
│   │   └── CreateReplyUseCase.php
│   └── User/
│       ├── GetUserProfileUseCase.php
│       └── UpdateUserProfileUseCase.php
│
├── Infrastructure/                          ← インフラ層（DB実装）
│   └── Eloquent/
│       ├── Models/
│       │   ├── User.php                     ← Fortifyが参照するモデル
│       │   ├── Post.php
│       │   ├── Like.php
│       │   ├── Follow.php
│       │   └── Reply.php                    ← 【新規】
│       └── Repositories/
│           ├── EloquentUserRepository.php
│           ├── EloquentPostRepository.php
│           ├── EloquentLikeRepository.php
│           ├── EloquentFollowRepository.php
│           └── EloquentReplyRepository.php  ← 【新規】
│
├── Http/                                    ← プレゼンテーション層
│   ├── Controllers/
│   │   ├── TimelineController.php
│   │   ├── ExploreController.php
│   │   ├── PostController.php               ← show() を追加
│   │   ├── LikeController.php
│   │   ├── FollowController.php
│   │   ├── UserController.php
│   │   └── ReplyController.php              ← 【新規】
│   └── Requests/
│       ├── StorePostRequest.php
│       ├── UpdateProfileRequest.php
│       └── StoreReplyRequest.php            ← 【新規】
│
└── Providers/
    └── AppServiceProvider.php               ← DIバインディング
```

### フロントエンド

```
resources/js/
├── pages/
│   ├── timeline.tsx          ← / （タイムライン）
│   ├── explore.tsx           ← /explore（全体投稿一覧）
│   ├── posts/
│   │   └── show.tsx          ← /posts/{id}（投稿詳細 + 返信）【新規】
│   └── users/
│       └── show.tsx          ← /users/{id}（プロフィール）
└── components/
    ├── post-card.tsx         ← 投稿1件（返信数表示を追加）
    ├── post-form.tsx         ← 投稿作成フォーム
    ├── follow-button.tsx     ← フォロー/アンフォローボタン
    └── reply-form.tsx        ← 返信作成フォーム【新規】
```

---

## UUID 設計

### 方針
全テーブルの主キーおよび外部キーを UUID（文字列）で統一する。

### マイグレーション変更箇所

既存マイグレーションを UUID に合わせて修正する（初回migrate前のため直接修正）。

| ファイル | 変更内容 |
|----------|----------|
| `0001_01_01_000000_create_users_table.php` | `id()` → `uuid('id')->primary()` |
| `2026_01_27_000001_create_teams_table.php` | teams/team_members/team_invitations の id・FK を UUID に変更 |
| `2026_01_27_000002_add_current_team_id_to_users_table.php` | current_team_id の型を UUID に変更 |

### 新規マイグレーション

```
xxxx_add_bio_to_users_table.php
xxxx_create_posts_table.php
xxxx_create_likes_table.php
xxxx_create_follows_table.php
xxxx_create_replies_table.php          ← 【返信機能】
```

### Eloquentモデルの共通設定

```php
use Illuminate\Support\Str;

class Post extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn ($model) => $model->id ??= (string) Str::uuid());
    }
}
```

---

## 各層の設計詳細

### Domain層

フレームワーク非依存の純粋なPHPクラス。Eloquentは使用しない。

```php
// app/Domain/Post/Entities/Post.php
class Post
{
    public function __construct(
        public readonly string $id,
        public readonly string $userId,
        public readonly string $content,
        public readonly \DateTimeImmutable $createdAt,
        public readonly int $likesCount = 0,
        public readonly bool $likedByAuthUser = false,
    ) {}
}
```

```php
// app/Domain/Post/Repositories/PostRepositoryInterface.php
interface PostRepositoryInterface
{
    public function findById(string $id): ?Post;
    public function getTimeline(string $userId, int $limit = 20): array;
    public function getAll(int $limit = 20): array;
    public function save(Post $post): void;
    public function delete(string $id): void;
}
```

### Application層

ユースケースはリポジトリインターフェースに依存し、Eloquentを知らない。

```php
// app/Application/Post/CreatePostUseCase.php
class CreatePostUseCase
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
    ) {}

    public function execute(string $userId, string $content): Post
    {
        $post = new Post(
            id: (string) Str::uuid(),
            userId: $userId,
            content: $content,
            createdAt: new \DateTimeImmutable(),
        );

        $this->postRepository->save($post);
        return $post;
    }
}
```

### Infrastructure層

EloquentモデルとドメインエンティティのマッピングはRepositoryが担う。

```php
// app/Infrastructure/Eloquent/Repositories/EloquentPostRepository.php
class EloquentPostRepository implements PostRepositoryInterface
{
    public function save(Post $post): void
    {
        PostModel::create([
            'id'      => $post->id,
            'user_id' => $post->userId,
            'content' => $post->content,
        ]);
    }

    public function getAll(int $limit = 20): array
    {
        return PostModel::with('user')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($model) => $this->toEntity($model))
            ->toArray();
    }

    private function toEntity(PostModel $model): Post
    {
        return new Post(
            id: $model->id,
            userId: $model->user_id,
            content: $model->content,
            createdAt: new \DateTimeImmutable($model->created_at),
        );
    }
}
```

### Presentation層

コントローラーはユースケースを呼び、Inertiaでレスポンスを返す。

```php
// app/Http/Controllers/PostController.php
class PostController extends Controller
{
    public function __construct(
        private CreatePostUseCase $createPost,
        private DeletePostUseCase $deletePost,
    ) {}

    public function store(StorePostRequest $request): RedirectResponse
    {
        $this->createPost->execute(
            userId: Auth::id(),
            content: $request->validated('content'),
        );

        return back();
    }
}
```

### DIバインディング（AppServiceProvider）

```php
public function register(): void
{
    $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
    $this->app->bind(PostRepositoryInterface::class, EloquentPostRepository::class);
    $this->app->bind(LikeRepositoryInterface::class, EloquentLikeRepository::class);
    $this->app->bind(FollowRepositoryInterface::class, EloquentFollowRepository::class);
    $this->app->bind(ReplyRepositoryInterface::class, EloquentReplyRepository::class); // 【返信機能】
}
```

---

## トレンド機能の設計詳細

### 概要

全ハッシュタグのうち投稿数上位5件を右サイドバーに表示する。投稿が多い場合でも応答性を維持するため、ランキングはキャッシュ（TTL: 5分）から取得する。キャッシュは投稿作成・削除イベント発生時に非同期で更新する。

### キャッシュ戦略

| 項目 | 内容 |
|------|------|
| キャッシュキー | `trending_hashtags` |
| TTL | 5分 |
| ドライバー | Laravelデフォルト（`.env` の `CACHE_STORE`） |
| 更新タイミング | 投稿作成・削除後にジョブをキューへディスパッチ |

```
PostCreated / PostDeleted イベント
    → UpdateTrendingHashtagsJob（キューワーカー）
        → Cache::put('trending_hashtags', [...], 300)
```

### Hashtag エンティティ変更

`postsCount` フィールドを追加し、トレンド表示で投稿数を渡せるようにする。

```php
// app/Domain/Hashtag/Entities/Hashtag.php
class Hashtag implements JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly int $postsCount = 0,   // 追加
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

### HashtagRepositoryInterface 変更

```php
// app/Domain/Hashtag/Repositories/HashtagRepositoryInterface.php
interface HashtagRepositoryInterface
{
    public function findOrCreateByName(string $name): Hashtag;

    /** @param string[] $names */
    public function syncToPost(array $names, string $postId): void;

    /**
     * 投稿数の多いハッシュタグを上位 $limit 件返す
     * @return Hashtag[]
     */
    public function getTrending(int $limit = 5): array;   // 追加
}
```

### EloquentHashtagRepository 変更

```php
public function getTrending(int $limit = 5): array
{
    return HashtagModel::withCount('posts')
        ->orderByDesc('posts_count')
        ->limit($limit)
        ->get()
        ->map(fn ($m) => new HashtagEntity(
            id: $m->id,
            name: $m->name,
            postsCount: $m->posts_count,
        ))
        ->all();
}
```

### GetTrendingHashtagsUseCase（新規）

```php
// app/Application/Hashtag/GetTrendingHashtagsUseCase.php
class GetTrendingHashtagsUseCase
{
    private const CACHE_KEY = 'trending_hashtags';
    private const CACHE_TTL = 300; // 秒

    public function __construct(
        private HashtagRepositoryInterface $hashtagRepository,
    ) {}

    /** @return array<array{name: string, postsCount: int}> */
    public function execute(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return array_map(
                fn ($h) => ['name' => $h->name, 'postsCount' => $h->postsCount],
                $this->hashtagRepository->getTrending(5),
            );
        });
    }
}
```

### UpdateTrendingHashtagsJob（新規）

```php
// app/Jobs/UpdateTrendingHashtagsJob.php
class UpdateTrendingHashtagsJob implements ShouldQueue
{
    public function handle(HashtagRepositoryInterface $hashtagRepository): void
    {
        $trending = array_map(
            fn ($h) => ['name' => $h->name, 'postsCount' => $h->postsCount],
            $hashtagRepository->getTrending(5),
        );
        Cache::put('trending_hashtags', $trending, 300);
    }
}
```

### Inertia shared props（SharedData）

トレンドデータは全ページで使うため、`HandleInertiaRequests` ミドルウェアで shared props として渡す。

```php
// app/Http/Middleware/HandleInertiaRequests.php
public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'trendingHashtags' => fn () => $this->getTrendingHashtags->execute(),
    ];
}
```

### フロントエンド変更

#### `resources/js/components/right-sidebar.tsx`

- ハードコードの `trends` 配列を削除
- `usePage().props.trendingHashtags` から取得
- 各ハッシュタグをクリックで `/hashtags/:name` に遷移する `<Link>` に変更
- 投稿数を `{trend.postsCount}件の投稿` として表示

```tsx
import { Link, usePage } from '@inertiajs/react';

type TrendingHashtag = { name: string; postsCount: number };

export default function RightSidebar() {
    const { trendingHashtags } = usePage<{ trendingHashtags: TrendingHashtag[] }>().props;
    // ...
}
```

### ディレクトリ構成への追記

```
app/
├── Application/Hashtag/
│   ├── GetHashtagPostsUseCase.php
│   └── GetTrendingHashtagsUseCase.php   ← 【新規】
└── Jobs/
    └── UpdateTrendingHashtagsJob.php    ← 【新規】
```

### 実装タスク一覧

| # | 層 | ファイル | 内容 |
|---|---|---------|------|
| 1 | Domain | `app/Domain/Hashtag/Entities/Hashtag.php` | `postsCount` フィールド追加・`JsonSerializable` 実装 |
| 2 | Domain | `app/Domain/Hashtag/Repositories/HashtagRepositoryInterface.php` | `getTrending(int $limit = 5): array` 追加 |
| 3 | Infrastructure | `app/Infrastructure/Eloquent/Repositories/EloquentHashtagRepository.php` | `getTrending()` 実装 |
| 4 | Application | `app/Application/Hashtag/GetTrendingHashtagsUseCase.php` | キャッシュ付きトレンド取得UC（新規）|
| 5 | Job | `app/Jobs/UpdateTrendingHashtagsJob.php` | キャッシュ再構築ジョブ（新規）|
| 6 | Provider | `app/Providers/AppServiceProvider.php` | `GetTrendingHashtagsUseCase` をミドルウェアにDI登録 |
| 7 | Middleware | `app/Http/Middleware/HandleInertiaRequests.php` | `trendingHashtags` を shared props に追加 |
| 8 | Application | `app/Application/Post/CreatePostUseCase.php` | 投稿作成後に `UpdateTrendingHashtagsJob` をディスパッチ |
| 9 | Application | `app/Application/Post/DeletePostUseCase.php` | 投稿削除後に `UpdateTrendingHashtagsJob` をディスパッチ |
| 10 | Frontend | `resources/js/components/right-sidebar.tsx` | ダミーデータを shared props に差し替え・リンク化 |
| 11 | Test | `tests/Unit/Application/Hashtag/GetTrendingHashtagsUseCaseTest.php` | キャッシュヒット・ミス両ケース |
| 12 | Test | `tests/Feature/Http/Controllers/` | shared props に `trendingHashtags` が含まれるか確認 |

---

## 返信機能の設計詳細

### テーブル設計

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
- `replies` テーブルを独立させることで、ネスト返信への将来的な拡張も容易

### Post エンティティへの変更

`Post` エンティティに `repliesCount` を追加し、投稿カードで返信数を表示できるようにする。

```php
// app/Domain/Post/Entities/Post.php（変更箇所）
public readonly int $repliesCount = 0,   // 追加
```

`jsonSerialize()` にも `'repliesCount' => $this->repliesCount` を追加する。

### Reply エンティティ

```php
// app/Domain/Reply/Entities/Reply.php
class Reply implements JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $postId,
        public readonly string $userId,
        public readonly string $userName,
        public readonly string $userHandle,
        public readonly string $content,
        public readonly DateTimeImmutable $createdAt,
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

### ReplyRepositoryInterface

```php
// app/Domain/Reply/Repositories/ReplyRepositoryInterface.php
interface ReplyRepositoryInterface
{
    /** @return Reply[] */
    public function getByPostId(string $postId): array;

    public function save(Reply $reply): void;

    public function countByPostId(string $postId): int;
}
```

### CreateReplyUseCase

```php
// app/Application/Reply/CreateReplyUseCase.php
class CreateReplyUseCase
{
    public function __construct(
        private ReplyRepositoryInterface $replyRepository,
        private PostRepositoryInterface $postRepository,
    ) {}

    public function execute(string $postId, string $userId, string $userName, string $userHandle, string $content): Reply
    {
        // 対象投稿の存在確認（存在しなければ例外）
        if (! $this->postRepository->findById($postId)) {
            throw new \DomainException('Post not found.');
        }

        $reply = new Reply(
            id: (string) Str::uuid(),
            postId: $postId,
            userId: $userId,
            userName: $userName,
            userHandle: $userHandle,
            content: $content,
            createdAt: new DateTimeImmutable(),
        );

        $this->replyRepository->save($reply);
        return $reply;
    }
}
```

### GetPostUseCase（投稿詳細取得）

```php
// app/Application/Post/GetPostUseCase.php
class GetPostUseCase
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
        private ReplyRepositoryInterface $replyRepository,
    ) {}

    public function execute(string $postId, ?string $authUserId = null): array
    {
        $post = $this->postRepository->findById($postId, $authUserId);
        if (! $post) {
            throw new \DomainException('Post not found.');
        }

        $replies = $this->replyRepository->getByPostId($postId);

        return ['post' => $post, 'replies' => $replies];
    }
}
```

### PostController::show()

```php
public function show(Request $request, Post $post): Response
{
    ['post' => $postEntity, 'replies' => $replies] = $this->getPost->execute(
        postId: $post->id,
        authUserId: $request->user()?->id,
    );

    return Inertia::render('posts/show', [
        'post'    => $postEntity,
        'replies' => $replies,
    ]);
}
```

### ReplyController

```php
// app/Http/Controllers/ReplyController.php
class ReplyController extends Controller
{
    public function __construct(
        private CreateReplyUseCase $createReply,
    ) {}

    public function store(StoreReplyRequest $request, Post $post): RedirectResponse
    {
        $this->createReply->execute(
            postId: $post->id,
            userId: $request->user()->id,
            userName: $request->user()->name,
            userHandle: $request->user()->handle,
            content: $request->validated('content'),
        );

        return back();
    }
}
```

### ルーティング追加

```php
// routes/web.php への追加
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');

Route::middleware('auth')->group(function () {
    // 既存ルート...
    Route::post('/posts/{post}/replies', [ReplyController::class, 'store'])->name('replies.store');
});
```

### フロントエンド: 投稿詳細ページ（pages/posts/show.tsx）

- 元の投稿を `PostCard` で表示（返信ボタンはクリッカブルにしない）
- 認証済みユーザーには `ReplyForm` を表示
- 返信一覧をタイムライン形式で表示（`reply-card.tsx` を使用しても可、またはインライン実装）

### フロントエンド: post-card.tsx の変更

- `repliesCount` プロップを追加
- `MessageCircle` アイコンに数を表示し、投稿詳細ページへのリンクにする

```tsx
// 変更後
<Link href={showPost(post.id)} className="flex items-center gap-1.5 text-sm text-[#8a8784] hover:text-[#3a6c72]">
    <MessageCircle size={16} />
    <span>{post.repliesCount}</span>
</Link>
```

---

## Fortify との統合

Fortifyが参照するUserモデルを `Infrastructure` 層のものに変更する。

```php
// config/fortify.php
'model' => App\Infrastructure\Eloquent\Models\User::class,

// config/auth.php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model'  => App\Infrastructure\Eloquent\Models\User::class,
    ],
],
```

---

## 実装順序

1. **マイグレーション** - UUID対応・既存修正・新規テーブル作成
2. **Domainモデル** - Entities + Repository Interfaces
3. **Infrastructureモデル** - Eloquent Models + Repositories
4. **DIバインディング** - AppServiceProvider
5. **Fortify設定変更** - config更新
6. **Application層** - ユースケース
7. **Presentation層** - コントローラー・ルーティング
8. **フロントエンド** - ページ・コンポーネント

---

## 返信機能 実装タスク一覧

| # | 層 | ファイル | 内容 |
|---|---|---------|------|
| 1 | Migration | `xxxx_create_replies_table.php` | repliesテーブル作成 |
| 2 | Domain | `app/Domain/Reply/Entities/Reply.php` | Replyエンティティ + Unit Test |
| 3 | Domain | `app/Domain/Reply/Repositories/ReplyRepositoryInterface.php` | リポジトリIF |
| 4 | Domain | `app/Domain/Post/Entities/Post.php` | `repliesCount` フィールド追加 |
| 5 | Infrastructure | `app/Infrastructure/Eloquent/Models/Reply.php` | EloquentモデルにrepliesリレーションをPostへ追加 |
| 6 | Infrastructure | `app/Infrastructure/Eloquent/Models/Post.php` | `replies()` HasManyリレーション追加 |
| 7 | Infrastructure | `app/Infrastructure/Eloquent/Repositories/EloquentReplyRepository.php` | リポジトリ実装 |
| 8 | Infrastructure | `EloquentPostRepository.php` | `withCount('replies')` 追加 + `repliesCount` マッピング |
| 9 | Application | `app/Application/Reply/CreateReplyUseCase.php` | 返信作成UC + Unit Test |
| 10 | Application | `app/Application/Post/GetPostUseCase.php` | 投稿詳細取得UC + Unit Test |
| 11 | Provider | `AppServiceProvider.php` | ReplyRepositoryInterface バインディング追加 |
| 12 | Presentation | `app/Http/Requests/StoreReplyRequest.php` | バリデーション（content: required, max:140） |
| 13 | Presentation | `app/Http/Controllers/ReplyController.php` | store() + Feature Test |
| 14 | Presentation | `app/Http/Controllers/PostController.php` | show() 追加 + Feature Test |
| 15 | Routing | `routes/web.php` | posts.show, replies.store ルート追加 |
| 16 | Frontend | `resources/js/pages/posts/show.tsx` | 投稿詳細ページ |
| 17 | Frontend | `resources/js/components/post-card.tsx` | repliesCount 表示 + 詳細ページリンク |
| 18 | Frontend | Wayfinder再生成 | `sail artisan wayfinder:generate` |
