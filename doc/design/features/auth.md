# 認証機能 設計

## 概要

Laravel Fortify を使ったパスワード認証と二要素認証（TOTP）。Inertia.js との統合のためレスポンスをカスタマイズしている。

---

## メールアドレス確認（Email Verification）

登録後にメールアドレス確認メールを送信し、未確認ユーザーは認証必須ルートにアクセスできないようにする。

- `User` モデルが `Illuminate\Contracts\Auth\MustVerifyEmail` を implement
- `config/fortify.php` の `Features::emailVerification()` で確認メール送信を有効化
- 認証必須ルートに `verified` ミドルウェアを追加（`routes/web.php`、`routes/settings.php`）
- メール未確認ユーザーは `/email/verify` にリダイレクト

---

## 使用ライブラリ

| ライブラリ | 用途 |
|-----------|------|
| Laravel Fortify | 認証バックエンド（ルート・コントローラー自動提供） |
| TOTP（Google Authenticator 互換） | 二要素認証 |

---

## Fortify 設定（`config/fortify.php`）

```php
'model'    => App\Infrastructure\Eloquent\Models\User::class,
'features' => [
    Features::registration(),
    Features::resetPasswords(),
    Features::emailVerification(),
    Features::updateProfileInformation(),
    Features::updatePasswords(),
    Features::twoFactorAuthentication([
        'confirm'        => true,
        'confirmPassword' => true,
    ]),
],
```

---

## カスタマイズポイント

### FortifyServiceProvider（`app/Providers/FortifyServiceProvider.php`）

Inertia 対応のため、以下のレスポンスをカスタマイズしている。

| インターフェース | カスタム実装 |
|----------------|------------|
| `LoginResponseContract` | `LoginResponse` |
| `RegisterResponseContract` | `RegisterResponse` |
| `TwoFactorLoginResponseContract` | `TwoFactorLoginResponse` |

各レスポンスは認証成功後に Inertia リダイレクト（`/`）を返す。

### Actions（`app/Actions/Fortify/`）

| クラス | 処理 |
|-------|------|
| `CreateNewUser` | ユーザー登録時の処理（handle 自動生成を含む） |
| `ResetUserPassword` | パスワードリセット処理 |

### ビューのマッピング

```php
// FortifyServiceProvider::configureViews()
Fortify::loginView(fn () => Inertia::render('auth/login'));
Fortify::registerView(fn () => Inertia::render('auth/register'));
// ...
```

---

## レート制限

```php
// FortifyServiceProvider::configureRateLimiting()
RateLimiter::for('login',     fn () => Limit::perMinute(5)->by(...));
RateLimiter::for('two-factor', fn () => Limit::perMinute(5)->by(...));
```

---

## ユーザーモデルの統合

Fortify が参照するユーザーモデルを Infrastructure 層に変更する。

```php
// config/auth.php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model'  => App\Infrastructure\Eloquent\Models\User::class,
    ],
],
```

---

## パスワードポリシー

`AppServiceProvider::configureDefaults()` で環境別にポリシーを設定する。

| 環境 | ポリシー |
|------|---------|
| 本番（`APP_ENV=production`） | 最小12文字、大文字・小文字・数字・記号を含む、漏洩済みパスワードチェック |
| 開発 | 制限なし |

---

## 二要素認証（2FA）

- TOTP（Time-based One-Time Password）方式
- QRコードをスキャンして認証アプリ（Google Authenticator 等）に登録
- リカバリーコード（`two_factor_recovery_codes`）で認証アプリなしでも復旧可能
- 有効化時に確認（`confirm: true`）とパスワード再確認（`confirmPassword: true`）が必要

### User テーブルの 2FA 関連カラム

| カラム | 説明 |
|-------|------|
| `two_factor_secret` | TOTP シークレットキー（暗号化保存） |
| `two_factor_recovery_codes` | リカバリーコード（JSON、暗号化保存） |
| `two_factor_confirmed_at` | 2FA を確認した日時（null = 未設定） |

---

## Inertia Shared Props での認証情報

`HandleInertiaRequests` が全ページに認証ユーザー情報を共有する。

```php
'auth' => [
    'user' => $request->user() ? [
        'id'                  => $user->id,
        'name'                => $user->name,
        'email'               => $user->email,
        'email_verified_at'   => $user->email_verified_at,
        'profile_image_url'   => $user->profile_image_url,
        'two_factor_enabled'  => $user->two_factor_confirmed_at !== null,
    ] : null,
],
```

---

## フロントエンド

### 認証ページ（`resources/js/pages/auth/`）

| ページ | パス |
|-------|------|
| `login.tsx` | `/login` |
| `register.tsx` | `/register` |
| `two-factor-challenge.tsx` | `/two-factor-challenge` |
| `forgot-password.tsx` | `/forgot-password` |
| `reset-password.tsx` | `/reset-password` |
| `verify-email.tsx` | `/email/verify` |
| `confirm-password.tsx` | `/confirm-password` |

### 設定ページでの 2FA 管理（`resources/js/pages/settings/security.tsx`）

- 2FA の有効化・無効化
- QRコードの表示と認証アプリへの登録
- リカバリーコードの表示・再生成
