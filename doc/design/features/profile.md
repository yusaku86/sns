# プロフィール機能 設計

## 概要

ユーザーのプロフィール表示・編集、およびプロフィールページのタブ別コンテンツ（投稿・返信・いいね）を提供する。

---

## エンティティ

### User（`app/Domain/User/Entities/User.php`）

```php
class User
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $handle,
        public readonly string $email,
        public readonly ?string $bio,
        public readonly ?string $headerImageUrl,
        public readonly ?string $profileImageUrl,
        public readonly int $postsCount = 0,
        public readonly int $followersCount = 0,
        public readonly int $followingCount = 0,
        public readonly bool $isFollowedByAuthUser = false,
    ) {}
}
```

---

## リポジトリインターフェース

`app/Domain/User/Repositories/UserRepositoryInterface.php`

```php
interface UserRepositoryInterface
{
    public function findById(string $id, ?string $authUserId = null): ?User;

    public function update(
        string $id,
        string $name,
        ?string $bio,
        ?string $headerImagePath = null,
        ?string $profileImagePath = null,
    ): void;
}
```

---

## ユースケース

### GetUserProfileUseCase（`app/Application/User/GetUserProfileUseCase.php`）

```php
LIMIT = 20

public function execute(
    string $userId,
    ?string $authUserId = null,
    ?string $cursor = null,
): ?array
```

取得するデータ:

```php
[
    'user'       => User,         // プロフィール情報
    'posts'      => Post[],       // 投稿一覧（ページネーション）
    'nextCursor' => string|null,
    'hasMore'    => bool,
    'replies'    => Reply[],      // 返信一覧
    'likedPosts' => Post[],       // いいねした投稿
    'followers'  => FollowUser[], // フォロワー一覧（Deferred props）
    'following'  => FollowUser[], // フォロー中一覧（Deferred props）
]
```

- `followers` / `following` は Inertia Deferred props で遅延読み込みする
- ユーザーが存在しなければ `null` を返す（コントローラーが 404 を返す）

### UpdateUserProfileUseCase（`app/Application/User/UpdateUserProfileUseCase.php`）

```php
public function execute(
    string $targetUserId,
    string $authUserId,
    string $name,
    ?string $bio,
    ?\Illuminate\Http\UploadedFile $headerImage = null,
    ?\Illuminate\Http\UploadedFile $profileImage = null,
): void
```

- `targetUserId !== authUserId` の場合は `AuthorizationException`
- 画像ファイルは `storage/public/` に保存
- `UserRepository::update()` で `name`, `bio`, 画像パスを更新
- 画像が渡されなかった場合（`null`）はパスを変更しない

---

## Eloquent Model

### User Model（`app/Infrastructure/Eloquent/Models/User.php`）

- Laravel Fortify 統合のため、2FA 関連フィールドを持つ
- `profile_image_url` アクセサ: `Storage::disk('public')->url($this->profile_image)`
- `handle` の自動生成: `CreateNewUser` アクション内でメールの `@` 前部分 + UUID短縮で生成（重複回避）

---

## Presentation層

### UserController

| メソッド | パス | 処理 |
|---------|------|------|
| `show(request, user)` | GET `/users/{user}` | `GetUserProfileUseCase` を呼び Inertia で `users/show` を返す |
| `update(UpdateProfileRequest, user)` | PUT `/users/{user}` | `UpdateUserProfileUseCase` を呼び `back()` |

### UpdateProfileRequest

| フィールド | ルール |
|-----------|-------|
| `name` | required, max:255 |
| `bio` | nullable, max:160 |
| `header_image` | nullable, image, mimes:jpeg/png/webp, max:5120KB（5MB） |
| `profile_image` | nullable, image, mimes:jpeg/png/webp, max:2048KB（2MB） |

---

## フロントエンド

### ユーザープロフィールページ（`resources/js/pages/users/show.tsx`）

タブ構成:

| タブ | 内容 |
|------|------|
| 投稿 | `PostCard` 一覧（自分の投稿 + リツイートを日時降順で混合表示、カーソルページネーション） |
| 返信 | リプライ一覧（元投稿の文脈付き） |
| いいね | いいねした投稿の `PostCard` 一覧 |
| フォロワー | `FollowUserListModal` を開く |
| フォロー中 | `FollowUserListModal` を開く |

### EditProfileModal（`resources/js/components/edit-profile-modal.tsx`）

- 自分のプロフィールページのみ表示（「プロフィールを編集」ボタン）
- `name`, `bio`, ヘッダー画像, プロフィール画像を編集
- 画像はファイル選択でプレビュー表示
- `useForm` で `PUT /users/{user}` に送信

### プロフィール画像の表示

- `user.profileImageUrl` を `<img>` で表示
- 未設定の場合はデフォルトアバター（イニシャルアイコン等）を表示
