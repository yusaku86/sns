# アーキテクチャ設計

## 技術スタック

| 項目 | 技術 |
|------|------|
| バックエンド | Laravel 13 |
| フロントエンド | React 19 + Vite 8 |
| スタイリング | Tailwind CSS 4 |
| DB | MySQL |
| 通信 | Inertia.js（REST API なし） |
| 認証 | Laravel Fortify |
| 構成 | モノレポ（バックエンド・フロントエンド同一ディレクトリ） |

Inertia.js により REST API を作らず、LaravelコントローラーからReactコンポーネントへ直接データを渡す。フロントからの操作は Inertia の `router` / `useForm` でサーバーへリクエストする。

---

## Clean Architecture

依存の方向を内側に向け、ドメインロジックをフレームワークから独立させる。

```
[Presentation] → [Application] → [Domain] ← [Infrastructure]
```

| 層 | ディレクトリ | 役割 | 禁止事項 |
|----|-------------|------|---------|
| Domain | `app/Domain/` | ビジネスエンティティ・リポジトリIF（純粋PHP） | Eloquent・Laravelクラスのimport |
| Application | `app/Application/` | ユースケース（業務ロジックの調整） | EloquentModelの直接使用、Interface以外への依存 |
| Infrastructure | `app/Infrastructure/` | Eloquentモデル・リポジトリ実装 | ドメインロジックの記述 |
| Presentation | `app/Http/` | コントローラー・フォームリクエスト | ビジネスロジックの記述 |

---

## ディレクトリ構成

### バックエンド

```
app/
├── Domain/
│   ├── Follow/
│   │   ├── Entities/Follow.php
│   │   ├── Entities/FollowUser.php
│   │   └── Repositories/FollowRepositoryInterface.php
│   ├── Hashtag/
│   │   ├── Entities/Hashtag.php
│   │   └── Repositories/HashtagRepositoryInterface.php
│   ├── Like/
│   │   ├── Entities/Like.php
│   │   └── Repositories/LikeRepositoryInterface.php
│   ├── Post/
│   │   ├── Entities/Post.php
│   │   ├── Entities/PostImage.php
│   │   └── Repositories/PostRepositoryInterface.php
│   ├── Reply/
│   │   ├── Entities/Reply.php
│   │   └── Repositories/ReplyRepositoryInterface.php
│   ├── Retweet/
│   │   ├── Entities/Retweet.php
│   │   └── Repositories/RetweetRepositoryInterface.php
│   └── User/
│       ├── Entities/User.php
│       └── Repositories/UserRepositoryInterface.php
│
├── Application/
│   ├── Explore/GetExploreUseCase.php
│   ├── Follow/
│   │   ├── FollowUserUseCase.php
│   │   └── UnfollowUserUseCase.php
│   ├── Hashtag/
│   │   ├── GetHashtagPostsUseCase.php
│   │   └── GetTrendingHashtagsUseCase.php
│   ├── Like/
│   │   ├── LikePostUseCase.php
│   │   └── UnlikePostUseCase.php
│   ├── Post/
│   │   ├── CreatePostUseCase.php
│   │   ├── DeletePostUseCase.php
│   │   ├── GetPostUseCase.php
│   │   └── PostImageStorageInterface.php
│   ├── Reply/CreateReplyUseCase.php
│   ├── Retweet/
│   │   ├── RetweetPostUseCase.php
│   │   └── UnretweetPostUseCase.php
│   ├── Timeline/GetTimelineUseCase.php
│   └── User/
│       ├── GetUserProfileUseCase.php
│       └── UpdateUserProfileUseCase.php
│
├── Infrastructure/
│   └── Eloquent/
│       ├── Models/
│       │   ├── Follow.php
│       │   ├── Hashtag.php
│       │   ├── Like.php
│       │   ├── Post.php
│       │   ├── PostImage.php
│       │   ├── Reply.php
│       │   ├── Retweet.php
│       │   └── User.php
│       ├── Repositories/
│       │   ├── EloquentFollowRepository.php
│       │   ├── EloquentHashtagRepository.php
│       │   ├── EloquentLikeRepository.php
│       │   ├── EloquentPostImageRepository.php
│       │   ├── EloquentPostRepository.php
│       │   ├── EloquentReplyRepository.php
│       │   ├── EloquentRetweetRepository.php
│       │   └── EloquentUserRepository.php
│       └── Observers/
│           └── PostObserver.php
│   └── Storage/
│       └── PostImageStorage.php
│
├── Http/
│   ├── Controllers/
│   │   ├── ExploreController.php
│   │   ├── FollowController.php
│   │   ├── HashtagController.php
│   │   ├── LikeController.php
│   │   ├── PostController.php
│   │   ├── ReplyController.php
│   │   ├── RetweetController.php
│   │   ├── TimelineController.php
│   │   └── UserController.php
│   ├── Requests/
│   │   ├── StorePostRequest.php
│   │   ├── StoreReplyRequest.php
│   │   └── UpdateProfileRequest.php
│   ├── Presenters/
│   │   └── PostPresenter.php
│   ├── Responses/
│   │   ├── LoginResponse.php
│   │   ├── RegisterResponse.php
│   │   └── TwoFactorLoginResponse.php
│   └── Middleware/
│       ├── HandleInertiaRequests.php
│       └── HandleAppearance.php
│
├── Jobs/
│   └── UpdateTrendingHashtagsJob.php
│
├── Providers/
│   ├── AppServiceProvider.php
│   └── FortifyServiceProvider.php
│
└── Actions/
    └── Fortify/
        ├── CreateNewUser.php
        └── ResetUserPassword.php
```

### フロントエンド

```
resources/js/
├── pages/
│   ├── auth/                   # ログイン・登録・2FA・パスワード関連
│   ├── hashtags/show.tsx       # ハッシュタグ詳細・投稿一覧
│   ├── posts/show.tsx          # 投稿詳細・返信一覧
│   ├── users/show.tsx          # ユーザープロフィール（タブ）
│   ├── settings/               # プロフィール設定・セキュリティ・外観
│   ├── timeline.tsx            # 認証済みホーム
│   ├── explore.tsx             # 全投稿一覧
│   └── welcome.tsx             # 未認証ランディング
├── components/
│   ├── post-card.tsx           # 投稿1件（いいね・リツイート・返信数・画像）
│   ├── post-form.tsx           # 投稿作成（テキスト・画像アップロード）
│   ├── post-images.tsx         # 投稿画像ギャラリー
│   ├── right-sidebar.tsx       # トレンド・推奨ユーザー
│   ├── follow-button.tsx       # フォロー/アンフォローボタン
│   ├── edit-profile-modal.tsx  # プロフィール編集モーダル
│   ├── follow-user-list-modal.tsx
│   └── ui/                     # UIプリミティブ（Button, Input 等 40+）
├── layouts/
│   ├── app-layout.tsx
│   ├── auth-layout.tsx
│   └── settings/layout.tsx
├── hooks/
│   └── use-infinite-scroll.ts  # 無限スクロール
└── types/
    └── index.ts                # TypeScript 型定義
```

---

## 各層の設計方針

### Domain層

フレームワーク非依存の純粋なPHPクラス。Eloquent を使用しない。日時は `DateTimeImmutable`、JSONレスポンスには `JsonSerializable` を実装する。

```php
// エンティティ例（Post）
class Post implements JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $userId,
        public readonly string $content,
        public readonly \DateTimeImmutable $createdAt,
        public readonly int $likesCount = 0,
        public readonly bool $likedByAuthUser = false,
        // ...
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'id'      => $this->id,
            'content' => $this->content,
            'createdAt' => $this->createdAt->format('Y/m/d H:i'),
            // ...
        ];
    }
}
```

### Application層

ユースケースはリポジトリインターフェースのみに依存し、Eloquent を知らない。

```php
class CreatePostUseCase
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
    ) {}

    public function execute(string $userId, string $content): Post
    {
        // ...
    }
}
```

### Infrastructure層

EloquentモデルとドメインエンティティのマッピングはRepositoryの `toEntity()` が担う。

```php
class EloquentPostRepository implements PostRepositoryInterface
{
    public function save(Post $post): void
    {
        PostModel::create([...]);
    }

    private function toEntity(PostModel $model): Post
    {
        return new Post(
            id: $model->id,
            // ...
        );
    }
}
```

### Presentation層

コントローラーはユースケースを呼び、Inertia でレスポンスを返す。ビジネスロジックを持たない。

```php
class PostController extends Controller
{
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

---

## DI（依存性注入）

`AppServiceProvider::register()` でインターフェースと実装クラスをバインドする。新しいRepositoryを追加したら必ずここに登録する。

```php
public function register(): void
{
    $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
    $this->app->bind(PostRepositoryInterface::class, EloquentPostRepository::class);
    $this->app->bind(LikeRepositoryInterface::class, EloquentLikeRepository::class);
    $this->app->bind(RetweetRepositoryInterface::class, EloquentRetweetRepository::class);
    $this->app->bind(FollowRepositoryInterface::class, EloquentFollowRepository::class);
    $this->app->bind(ReplyRepositoryInterface::class, EloquentReplyRepository::class);
    $this->app->bind(HashtagRepositoryInterface::class, EloquentHashtagRepository::class);
    $this->app->bind(PostImageRepositoryInterface::class, EloquentPostImageRepository::class);
    $this->app->bind(PostImageStorageInterface::class, PostImageStorage::class);
}
```

---

## Eloquentモデルの共通設定

全テーブルの主キーはUUID（文字列型）。`boot()` で自動生成する。

```php
protected $keyType = 'string';
public $incrementing = false;

protected static function boot(): void
{
    parent::boot();
    static::creating(fn ($model) => $model->id ??= (string) Str::uuid());
}
```

Like / Retweet / Follow は `updated_at` を持たないため `$timestamps = false` を設定し、`created_at` のみ `boot()` で設定する。

---

## Fortify との統合

Fortify が参照するUserモデルを Infrastructure 層のものに変更する。

```php
// config/fortify.php
'model' => App\Infrastructure\Eloquent\Models\User::class,

// config/auth.php
'providers' => [
    'users' => [
        'driver'  => 'eloquent',
        'model'   => App\Infrastructure\Eloquent\Models\User::class,
    ],
],
```

`FortifyServiceProvider` でログイン・登録・2FAのレスポンスを Inertia 対応にカスタマイズしている（`app/Http/Responses/`）。

---

## Inertia Shared Props

`HandleInertiaRequests` ミドルウェアで全ページに共有するデータを定義する。

```php
public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'auth' => [
            'user' => $request->user() ? [
                'id', 'name', 'email', 'email_verified_at',
                'profile_image_url', 'two_factor_enabled',
            ] : null,
        ],
        'sidebarOpen'       => /* cookieから判定 */,
        'trendingHashtags'  => fn () => $this->getTrendingHashtags->execute(),
    ];
}
```

---

## Factoryの名前解決

`AppServiceProvider::configureFactories()` でカスタマイズ済み。`App\Infrastructure\Eloquent\Models\Post` → `Database\Factories\PostFactory` と解決される。

---

## Observerの登録

`AppServiceProvider::boot()` で登録する。

```php
PostModel::observe(PostObserver::class);
```

`PostObserver` は以下のイベントを監視する。

| イベント | 処理 |
|---------|------|
| `created` | `UpdateTrendingHashtagsJob` をキューへディスパッチ |
| `deleting` | 添付画像ファイルをストレージから削除 |
| `deleted` | `UpdateTrendingHashtagsJob` をキューへディスパッチ |
