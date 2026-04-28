# SNS アプリケーション

学習目的で作成するシンプルなSNSアプリケーションです。

> **免責事項**
> このリポジトリは学習目的で作成したものです。本ソースコードを使用したことによって生じたいかなる損害についても、作者は責任を負いません。

## 技術スタック

- **バックエンド**: Laravel 13 / PHP 8.5
- **フロントエンド**: React / TypeScript / Inertia.js
- **データベース**: MySQL 8.4
- **開発環境**: Docker / Laravel Sail

---

## ローカル環境構築

### 前提条件

- Docker Desktop がインストール済みであること
- Git がインストール済みであること

> **Windowsユーザーへ**
> Docker Desktop の動作に WSL 2 が必要です。事前に WSL をインストールし、有効化してください。
> また、リポジトリのクローン先は **WSL 内のディレクトリ**（例: `~/`）にしてください。

### sailエイリアスの設定

`sail` を毎回入力する代わりに、以下のエイリアスを `~/.bashrc` または `~/.zshrc` に追加しておくと便利です。

```bash
alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
```

追加後は設定を反映してください。

```bash
source ~/.bashrc  # または source ~/.zshrc
```

以降の手順では `sail` コマンドとして記載します。

### 手順

**1. リポジトリのクローン**

```bash
git clone <リポジトリURL>
cd sns
```

**2. 環境変数ファイルの作成**

```bash
cp .env.example .env
```

**3. コンテナの起動**

```bash
sail up -d
```

**4. Composer依存パッケージのインストール**

```bash
sail composer install
```

**5. アプリケーションキーの生成**

```bash
sail artisan key:generate
```

**6. データベースの初期化**

```bash
sail artisan migrate:fresh --seed
```

**7. フロントエンドのビルド**

```bash
sail npm run build
```

**8. アクセス**

| サービス | URL |
|---------|-----|
| アプリケーション | http://localhost |
| phpMyAdmin | http://localhost:8080 |
| Mailpit（メール確認） | http://localhost:8025 |

---

## 開発コマンド

```bash
# DBの初期化＋シーダー実行
sail artisan migrate:fresh --seed

# PHPコードスタイル修正
sail bin pint

# フロントエンドのビルド
sail npm run build

# TypeScript型チェック
sail npm run types:check

# Wayfinder（TypeScriptルート定義）の再生成
sail artisan wayfinder:generate

# テストの実行
sail artisan test
```

---

## ドキュメント

| ドキュメント | パス |
|------------|------|
| 要件定義 | [doc/requirements.md](doc/requirements.md) |
| アーキテクチャ設計 | [doc/design/architecture.md](doc/design/architecture.md) |
| データモデル | [doc/design/data-model.md](doc/design/data-model.md) |
| 画面・ルーティング | [doc/design/screens.md](doc/design/screens.md) |
| 機能別設計（認証） | [doc/design/features/auth.md](doc/design/features/auth.md) |
| 機能別設計（投稿） | [doc/design/features/post.md](doc/design/features/post.md) |
| 機能別設計（いいね） | [doc/design/features/like.md](doc/design/features/like.md) |
| 機能別設計（リツイート） | [doc/design/features/retweet.md](doc/design/features/retweet.md) |
| 機能別設計（返信） | [doc/design/features/reply.md](doc/design/features/reply.md) |
| 機能別設計（フォロー） | [doc/design/features/follow.md](doc/design/features/follow.md) |
| 機能別設計（プロフィール） | [doc/design/features/profile.md](doc/design/features/profile.md) |
| 機能別設計（画像） | [doc/design/features/image.md](doc/design/features/image.md) |
| 機能別設計（ハッシュタグ） | [doc/design/features/hashtag.md](doc/design/features/hashtag.md) |
| 機能別設計（検索） | [doc/design/features/search.md](doc/design/features/search.md) |
| 機能別設計（トレンド） | [doc/design/features/trending.md](doc/design/features/trending.md) |
