# フォロー機能 設計

## 概要

ユーザーをフォロー / アンフォローする。自分自身へのフォローは禁止。二重フォローは冪等。

---

## エンティティ

### Follow（`app/Domain/Follow/Entities/Follow.php`）

```php
class Follow
{
    public function __construct(
        public readonly string $id,
        public readonly string $followerId,   // フォローする側
        public readonly string $followingId,  // フォローされる側
        public readonly \DateTimeImmutable $createdAt,
    ) {}
}
```

### FollowUser（`app/Domain/Follow/Entities/FollowUser.php`）

フォロワー / フォロー中の一覧表示に使用するビュー用エンティティ。

```php
class FollowUser
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $handle,
        public readonly ?string $profileImageUrl,
        public readonly bool $isFollowedByAuthUser,
    ) {}
}
```

---

## リポジトリインターフェース

`app/Domain/Follow/Repositories/FollowRepositoryInterface.php`

```php
interface FollowRepositoryInterface
{
    public function exists(string $followerId, string $followingId): bool;
    public function save(string $followerId, string $followingId): void;
    public function delete(string $followerId, string $followingId): void;

    /** @return FollowUser[] */
    public function getFollowers(string $userId, ?string $authUserId = null): array;

    /** @return FollowUser[] */
    public function getFollowing(string $userId, ?string $authUserId = null): array;

    /**
     * フォロー中のユーザーがフォローしているユーザーを、フォロワー数の多い順に返す。
     * 自分自身は除外する。フォロー済みユーザーも含み isFollowedByAuthUser で状態を示す。
     *
     * @return FollowUser[]
     */
    public function getSuggestedUsers(string $authUserId, int $limit): array;
}
```

---

## ユースケース

### GetSuggestedUsersUseCase（`app/Application/Follow/GetSuggestedUsersUseCase.php`）

```php
public function execute(string $authUserId, int $limit = 5): FollowUser[]
```

- Stale-while-revalidate 戦略でキャッシュを管理する
  - キャッシュなし（初回）: 同期計算 → キャッシュ保存 → 返す
  - キャッシュあり・フレッシュ（1時間以内）: キャッシュをそのまま返す
  - キャッシュあり・ステール（1時間経過）: 古いキャッシュを即返す + バックグラウンドで `ComputeSuggestedUsersJob` をディスパッチ
- `SuggestedUserCacheInterface` / `SuggestedUserRefresherInterface` のみに依存（Laravelクラス非依存）

### ComputeSuggestedUsersUseCase（`app/Application/Follow/ComputeSuggestedUsersUseCase.php`）

```php
public function execute(string $authUserId, int $limit = 5): void
```

- おすすめユーザーを計算し `SuggestedUserCacheInterface` に保存する
- `GetSuggestedUsersUseCase` からの同期呼び出し、および `ComputeSuggestedUsersJob` から利用される

### おすすめユーザーキャッシュ戦略

| インターフェース | 配置 | 実装クラス |
|----------------|------|-----------|
| `SuggestedUserCacheInterface` | `app/Application/Follow/` | `LaravelSuggestedUserCache`（Infrastructure） |
| `SuggestedUserRefresherInterface` | `app/Application/Follow/` | `JobSuggestedUserRefresher`（Infrastructure） |

- データTTL: 2時間（ハード失効）
- フレッシュネスTTL: 1時間（ステール判定のトリガー）
- フォロー・アンフォロー時はキャッシュを無効化しない（リストは最大2時間固定）
- フォロー状態（`isFollowedByAuthUser`）はフロントのローカル状態で管理

### FollowUserUseCase（`app/Application/Follow/FollowUserUseCase.php`）

```php
public function execute(string $followerId, string $followingId): void
```

- `followerId === followingId` の場合は `InvalidArgumentException`
- `exists()` を確認し、まだフォローしていなければ `save()` （二重フォロー冪等）

### UnfollowUserUseCase（`app/Application/Follow/UnfollowUserUseCase.php`）

```php
public function execute(string $followerId, string $followingId): void
```

---

## Presentation層

### FollowController

| メソッド | パス | 処理 |
|---------|------|------|
| `store(request, user)` | POST `/users/{user}/follow` | `FollowUserUseCase` を実行し `back()` |
| `destroy(request, user)` | DELETE `/users/{user}/follow` | `UnfollowUserUseCase` を実行し `back()` |

---

## フロントエンド

### FollowButton（`resources/js/components/follow-button.tsx`）

- `user.isFollowedByAuthUser` フラグでボタン表示（「フォロー中」/「フォロー」）を切り替え
- 自分のプロフィールページでは「プロフィールを編集」ボタンに差し替え

### FollowUserListModal（`resources/js/components/follow-user-list-modal.tsx`）

- フォロワー / フォロー中ユーザーの一覧をモーダルで表示
- `GetUserProfileUseCase` から Deferred props で遅延読み込みした `followers[]` / `following[]` を使用

### ユーザープロフィールページ（`resources/js/pages/users/show.tsx`）

- プロフィールヘッダーにフォロー数 / フォロワー数を表示
- フォロワー / フォロー中の数字クリックで `FollowUserListModal` を開く
- `user.isFollowedByAuthUser` で `FollowButton` の状態を初期化

---

## フォロー状態の伝播

フォロー状態はページ遷移ごとにサーバーから取得する。

- `User` エンティティ: `isFollowedByAuthUser` フィールド
- `Post` エンティティ: 投稿者に対する `isFollowedByAuthUser` は保持しない（プロフィールページで別途取得）
- `FollowUser` エンティティ: 一覧表示時に認証ユーザーのフォロー状態を付与
