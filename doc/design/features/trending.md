# トレンド機能 設計

## 概要

全ハッシュタグのうち投稿数の多い順に上位5件をサイドバーに表示する。投稿数が多い場合もパフォーマンスを維持するため、完全リアルタイムではなくキャッシュ（TTL: 5分）で提供する。

---

## キャッシュ戦略

| 項目 | 内容 |
|------|------|
| キャッシュキー | `trending_hashtags` |
| TTL | 300秒（5分） |
| ドライバー | Laravelデフォルト（`.env` の `CACHE_STORE`） |
| 更新タイミング | 投稿作成・削除後に Job をキューへディスパッチ |

```
投稿作成 / 削除
    → PostObserver (created / deleted)
        → UpdateTrendingHashtagsJob（キューワーカー）
            → Cache::put('trending_hashtags', [...], 300)
```

---

## ユースケース

### GetTrendingHashtagsUseCase（`app/Application/Hashtag/GetTrendingHashtagsUseCase.php`）

```php
private const CACHE_KEY = 'trending_hashtags';
private const CACHE_TTL = 300; // 秒
private const LIMIT = 5;

/** キャッシュから取得（なければDBから取得してキャッシュに保存） */
public function execute(): array  // returns Hashtag[]

/** キャッシュを破棄して再構築（Job から呼ばれる） */
public function refresh(): array
```

---

## Job

### UpdateTrendingHashtagsJob（`app/Jobs/UpdateTrendingHashtagsJob.php`）

```php
class UpdateTrendingHashtagsJob implements ShouldQueue, ShouldBeUnique
{
    public int $uniqueFor = 60; // 60秒間は重複実行を抑制
}
```

- `ShouldBeUnique` により、同一 Job が 60秒以内に複数キューに積まれても1回だけ実行する
- `handle(GetTrendingHashtagsUseCase $uc): void` で `$uc->refresh()` を呼ぶ

---

## PostObserver との連携

`PostObserver`（`app/Infrastructure/Eloquent/Observers/PostObserver.php`）が以下のタイミングで Job をディスパッチする。

| イベント | 処理 |
|---------|------|
| `created` | `UpdateTrendingHashtagsJob::dispatch()` |
| `deleted` | `UpdateTrendingHashtagsJob::dispatch()` |

Observer は `AppServiceProvider::boot()` で登録する。

---

## Inertia Shared Props

トレンドデータは全ページで使うため、`HandleInertiaRequests` ミドルウェアで shared props として渡す。

```php
// app/Http/Middleware/HandleInertiaRequests.php
'trendingHashtags' => fn () => $this->getTrendingHashtags->execute(),
```

クロージャで渡すことで、実際に使うページのみ評価される（遅延評価）。

---

## フロントエンド

### RightSidebar（`resources/js/components/right-sidebar.tsx`）

- `usePage().props.trendingHashtags` から取得
- 上位5件を「{postsCount}件の投稿」とともに表示
- 各ハッシュタグをクリックで `/hashtags/{name}` へ遷移する `<Link>` にする

```tsx
type TrendingHashtag = { name: string; postsCount: number };

const { trendingHashtags } = usePage<{ trendingHashtags: TrendingHashtag[] }>().props;
```
