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
│   └── Follow/
│       ├── Entities/Follow.php
│       └── Repositories/FollowRepositoryInterface.php
│
├── Application/                             ← アプリケーション層（ユースケース）
│   ├── Post/
│   │   ├── CreatePostUseCase.php
│   │   └── DeletePostUseCase.php
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
│       │   └── Follow.php
│       └── Repositories/
│           ├── EloquentUserRepository.php
│           ├── EloquentPostRepository.php
│           ├── EloquentLikeRepository.php
│           └── EloquentFollowRepository.php
│
├── Http/                                    ← プレゼンテーション層
│   ├── Controllers/
│   │   ├── TimelineController.php
│   │   ├── ExploreController.php
│   │   ├── PostController.php
│   │   ├── LikeController.php
│   │   ├── FollowController.php
│   │   └── UserController.php
│   └── Requests/
│       ├── StorePostRequest.php
│       └── UpdateProfileRequest.php
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
│   └── users/
│       └── show.tsx          ← /users/{id}（プロフィール）
└── components/
    ├── post-card.tsx         ← 投稿1件（いいねボタン含む）
    ├── post-form.tsx         ← 投稿作成フォーム
    └── follow-button.tsx     ← フォロー/アンフォローボタン
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
}
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
