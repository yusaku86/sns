# 画像投稿機能 設計

## 概要

投稿に最大8枚の画像を添付できる。投稿削除時に画像ファイルも一緒に削除する。フロントへは署名付き一時URLで渡す。

---

## テーブル設計

```sql
CREATE TABLE post_images (
    id         CHAR(36)           PRIMARY KEY,
    post_id    CHAR(36)           NOT NULL REFERENCES posts(id) ON DELETE CASCADE,
    path       VARCHAR(255)       NOT NULL,
    `order`    TINYINT UNSIGNED   NOT NULL DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

- `order` カラムで表示順を保持（0始まり）
- `post_id` の CASCADE DELETE で投稿削除時に画像レコードも自動削除

---

## エンティティ

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

`app/Domain/Post/Repositories/PostImageRepositoryInterface.php`（または同ディレクトリ内に配置）

```php
interface PostImageRepositoryInterface
{
    /** @param string[] $paths */
    public function saveForPost(string $postId, array $paths): void;

    public function deleteByPostId(string $postId): void;
}
```

---

## 画像ストレージ

### PostImageStorageInterface（`app/Application/Post/PostImageStorageInterface.php`）

```php
interface PostImageStorageInterface
{
    /**
     * @param \Illuminate\Http\UploadedFile[] $files
     * @return string[] 保存したファイルパスの配列
     */
    public function storeAll(array $files): array;
}
```

### PostImageStorage（`app/Infrastructure/Storage/PostImageStorage.php`）

- `storage/local/post_images/` に `{UUID}.{拡張子}` で保存
- Laravel の `Storage::disk('local')` を使用

---

## ユースケースでの画像処理

`CreatePostUseCase::execute()` 内で画像を処理する。

```php
// ファイルをストレージに保存
$imagePaths = $this->postImageStorage->storeAll($uploadedFiles);

// DB にパスと順序を記録
$this->postImageRepository->saveForPost($postId, $imagePaths);
```

投稿削除時は `PostObserver::deleting()` で画像ファイルをストレージから削除する。

---

## PostPresenter による URL 変換

フロントに画像パスをそのまま渡すのではなく、`PostPresenter` で署名付き一時URLに変換する。

```php
class PostPresenter
{
    public function toArray(Post $post): array
    {
        $data = $post->jsonSerialize();
        $data['images'] = array_map(
            fn ($img) => Storage::disk('local')->temporaryUrl($img['path'], now()->addHour()),
            $data['images'],
        );
        return $data;
    }
}
```

- 一時URLの有効期限は1時間
- コントローラーは `PostPresenter::collection()` を通してから Inertia に渡す

---

## Presentation層

### StorePostRequest

| フィールド | ルール |
|-----------|-------|
| `images` | nullable, array, max:8件 |
| `images.*` | image, mimes:jpeg,png,webp |
| withValidator | テキストまたは画像の少なくとも一方が必須 |

---

## フロントエンド

### PostForm（`resources/js/components/post-form.tsx`）

- ファイル選択で画像を追加（最大8枚）
- 追加した画像のプレビューを表示
- 個別に削除できる
- `useForm` の `images` フィールドに `File[]` を設定して送信

### PostImages（`resources/js/components/post-images.tsx`）

- `PostCard` 内で使用
- 画像数に応じてグリッドレイアウトを変える（1枚: 全幅、2枚: 2列、3-4枚: 2×2グリッド等）
- 画像クリックで拡大表示（実装状況は要確認）
