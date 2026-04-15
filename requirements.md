# SNSアプリケーション 要件定義

## 概要
学習目的で作成するシンプルなSNSアプリケーション。

---

## 技術スタック

| 項目 | 技術 |
|------|------|
| バックエンド | Laravel 13 |
| フロントエンド | React 19 + Vite 8 |
| スタイリング | Tailwind CSS 4 |
| DB | MySQL |
| ルーティング/通信 | Inertia.js（Laravel ↔ React の橋渡し） |
| 認証 | Laravel Fortify |
| 構成 | モノレポ（バックエンド・フロントエンド同一ディレクトリ） |

### Inertia.js について
REST API を作らず、Laravelのコントローラーから `Inertia::render()` で React コンポーネントにデータを渡す構成。
フロントからの操作（フォーム送信等）は Inertia の `router` や `useForm` を使ってサーバーへリクエストする。

---

## 機能一覧

### 認証
- ユーザー登録（name, email, password）
- ログイン / ログアウト

### ユーザー
- プロフィール表示（name, bio, 投稿数, フォロー数, フォロワー数）
- プロフィール編集（name, bio）
- フォロー / アンフォロー

### 投稿
- 投稿作成（テキストのみ、最大140文字）
- 投稿削除（自分の投稿のみ）
- タイムライン表示（フォロー中のユーザーの投稿）
- 全体投稿一覧（新着順）

### いいね
- いいね / いいね取り消し
- いいね数の表示

### トレンド
- 全ハッシュタグのうち、投稿数の多い順に上位5件をサイドバーに表示する
- 各ハッシュタグの投稿数を表示する
- ハッシュタグをクリックするとそのハッシュタグの投稿一覧ページへ遷移する
- **非機能要件：** 投稿数が非常に多い場合もパフォーマンスを維持するため、完全リアルタイムではなくキャッシュ（TTL: 5分）で提供する
  - 投稿作成・削除のたびにキャッシュを更新する（キャッシュ更新はイベント駆動で非同期に行う）

### 返信（リプライ）
- ログイン済みユーザーは任意の投稿に対して返信を作成できる
- 返信は最大140文字のテキストのみ
- 返信は元の投稿に紐付けて表示される（スレッド形式）
- 返信の返信（ネスト）は対象外（フェーズ1）
- 投稿カードに返信数を表示する
- 投稿詳細画面で返信一覧と返信フォームを表示する

---

## データモデル

### users
| カラム | 型 | 備考 |
|--------|----|------|
| id | bigint | PK |
| name | varchar(255) | |
| email | varchar(255) | unique |
| password | varchar(255) | |
| bio | text | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

### posts
| カラム | 型 | 備考 |
|--------|----|------|
| id | bigint | PK |
| user_id | bigint | FK → users |
| content | varchar(140) | |
| created_at | timestamp | |
| updated_at | timestamp | |

### likes
| カラム | 型 | 備考 |
|--------|----|------|
| id | bigint | PK |
| user_id | bigint | FK → users |
| post_id | bigint | FK → posts |
| created_at | timestamp | |

### follows
| カラム | 型 | 備考 |
|--------|----|------|
| id | bigint | PK |
| follower_id | bigint | FK → users（フォローする側） |
| following_id | bigint | FK → users（フォローされる側） |
| created_at | timestamp | |

### replies
| カラム | 型 | 備考 |
|--------|----|------|
| id | uuid | PK |
| post_id | uuid | FK → posts |
| user_id | uuid | FK → users |
| content | varchar(140) | |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## 画面一覧

| 画面 | パス | 認証 |
|------|------|------|
| ログイン | `/login` | 不要 |
| 新規登録 | `/register` | 不要 |
| タイムライン | `/` | 必要 |
| 全体投稿一覧 | `/explore` | 不要 |
| プロフィール | `/users/{id}` | 不要 |
| 投稿詳細 | `/posts/{post}` | 不要（返信投稿は必要） |

---

## ルーティング

Inertia.js 構成のため、すべて `web.php` で定義する。
コントローラーは `Inertia::render('PageName', ['props' => ...])` でデータを渡す。

### 認証（Laravel Fortify が自動提供）
| メソッド | パス | 概要 |
|----------|------|------|
| GET | `/login` | ログイン画面 |
| POST | `/login` | ログイン処理 |
| POST | `/logout` | ログアウト処理 |
| GET | `/register` | 新規登録画面 |
| POST | `/register` | 新規登録処理 |

### 投稿
| メソッド | パス | 認証 | 概要 |
|----------|------|------|------|
| GET | `/` | 必要 | タイムライン表示 |
| GET | `/explore` | 不要 | 全体投稿一覧 |
| POST | `/posts` | 必要 | 投稿作成 |
| DELETE | `/posts/{post}` | 必要 | 投稿削除（自分のみ） |

### いいね
| メソッド | パス | 認証 | 概要 |
|----------|------|------|------|
| POST | `/posts/{post}/like` | 必要 | いいね |
| DELETE | `/posts/{post}/like` | 必要 | いいね取り消し |

### 返信
| メソッド | パス | 認証 | 概要 |
|----------|------|------|------|
| GET | `/posts/{post}` | 不要 | 投稿詳細 + 返信一覧 |
| POST | `/posts/{post}/replies` | 必要 | 返信作成 |

### ユーザー
| メソッド | パス | 認証 | 概要 |
|----------|------|------|------|
| GET | `/users/{user}` | 不要 | プロフィール表示 |
| PUT | `/users/{user}` | 必要 | プロフィール更新（自分のみ） |
| POST | `/users/{user}/follow` | 必要 | フォロー |
| DELETE | `/users/{user}/follow` | 必要 | アンフォロー |
