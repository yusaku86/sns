# 投稿検索機能 設計

## 概要

サイドバーの検索フォームから投稿本文に対してキーワード部分一致検索を行い、`/explore?q=キーワード` に遷移して結果を表示する。

---

## 仕様

| 項目 | 内容 |
|------|------|
| 検索対象 | 投稿（`posts`）の `content` カラムのみ（リツイートは対象外） |
| マッチング | 部分一致（LIKE `%keyword%`） |
| 認証 | 不要（ログイン時はいいね済み・リツイート済みを反映） |
| ページネーション | カーソルベース無限スクロール（20件/回）、ハッシュタグ詳細と同方式 |
| URL | `/explore?q=キーワード`（`q` が空または省略の場合は全体一覧を表示） |
| ページ見出し | `「キーワード」の検索結果`（検索なし時は `みんなの投稿`） |

---

## リポジトリインターフェース

`PostRepositoryInterface` に以下を追加する。

```php
/**
 * キーワードで投稿本文を部分一致検索する（リツイートは除外）
 * @return array{posts: Post[], nextCursor: string|null, hasMore: bool}
 */
public function searchByKeyword(
    string $keyword,
    ?string $authUserId = null,
    int $limit = 20,
    ?string $cursor = null,
): array;
```

---

## ユースケース

### SearchPostsUseCase（`app/Application/Post/SearchPostsUseCase.php`）

```php
LIMIT = 20

public function execute(
    string $keyword,
    ?string $authUserId = null,
    ?string $cursor = null,
): array
// returns: ['posts' => Post[], 'nextCursor' => string|null, 'hasMore' => bool]
```

- `PostRepository::searchByKeyword()` を呼ぶだけ
- キーワードのトリム・空チェックはController側で行い、空の場合はこのUseCaseを呼ばない

---

## Infrastructure層

### EloquentPostRepository への追加

```php
public function searchByKeyword(string $keyword, ...): array
// posts テーブルを LIKE 検索（parent_post_id IS NULL でリツイートを除外）
// カーソルページネーション対応（getAll と同方式）
```

---

## Presentation層

### ExploreController（`app/Http/Controllers/ExploreController.php`）の変更

| 条件 | 処理 |
|------|------|
| `?q` パラメータあり（空文字以外） | `SearchPostsUseCase` を呼び検索結果を返す |
| `?q` なし / 空文字 | 既存の `GetExploreUseCase` を呼ぶ |

Inertia props:

```php
[
    'posts'      => PostPresenter::presentMany($posts),
    'nextCursor' => $nextCursor,
    'hasMore'    => $hasMore,
    'query'      => $query, // 検索文字列（空文字 or null の場合は ''）
]
```

---

## フロントエンド

### サイドバー検索フォーム（`resources/js/components/sidebar.tsx` 等）

- テキスト入力フォームをサイドバーに追加
- Enter または送信ボタンで `/explore?q=入力値` へ Inertia `router.visit()` で遷移
- 現在 `/explore` 表示中かつ `q` が一致する場合は入力欄に初期値を設定

### Exploreページ（`resources/js/pages/explore.tsx`）の変更

- `query` prop を受け取る
- ページ見出し:
  - `query` あり → `「{query}」の検索結果`
  - `query` なし → `みんなの投稿`
- 無限スクロールの fetch 先は `/explore?q={query}&cursor={cursor}`（既存の `useInfiniteScroll` を流用）
