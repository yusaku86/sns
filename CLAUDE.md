# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## 開発コマンド

開発環境は **Laravel Sail**（Docker）で動作する。全コマンドは `./vendor/bin/sail` 経由で実行する。

```bash
# コンテナ起動・停止
./vendor/bin/sail up -d
./vendor/bin/sail down

# マイグレーション
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan migrate:fresh --seed   # DB初期化＋シーダー

# テスト（全件）
./vendor/bin/sail artisan test

# テスト（単一ファイル）
./vendor/bin/sail artisan test tests/Unit/Domain/Post/PostEntityTest.php

# テスト（単一ケース）
./vendor/bin/sail artisan test --filter "投稿を作成できる"

# フロントエンドビルド
./vendor/bin/sail npm run build   # 本番ビルド
./vendor/bin/sail npm run dev     # 開発サーバー（ホットリロード）

# PHPコードスタイル修正
php vendor/bin/pint

# Wayfinder（TypeScriptルート定義）再生成
./vendor/bin/sail artisan wayfinder:generate
```

---

## アーキテクチャ概要

### レイヤー構成（Clean Architecture）

```
Presentation → Application → Domain ← Infrastructure
```

| 層 | ディレクトリ | 禁止事項 |
|----|-------------|----------|
| Domain | `app/Domain/` | Eloquent・Laravelのクラスをimportしない |
| Application | `app/Application/` | EloquentModelを直接使わない。Interfaceのみに依存 |
| Infrastructure | `app/Infrastructure/` | ドメインロジックを書かない |
| Presentation | `app/Http/` | ビジネスロジックをControllerに書かない |

### DI（依存性注入）の登録

`app/Providers/AppServiceProvider.php` でRepositoryのInterfaceと実装クラスをバインドしている。新しいRepositoryを追加したら必ずここに登録する。

### Eloquentモデルの配置

- **実体**: `app/Infrastructure/Eloquent/Models/`（UUID生成 `boot()` あり）
- **薄いラッパー**: `app/Models/`（Laravelフレームワークが期待するパスのため、Infrastructureクラスを継承するだけ）

`app/Models/` に新規ファイルを作成しない。

### Factoryの名前解決

`AppServiceProvider::configureFactories()` でFactoryの名前解決をカスタマイズしている。`App\Infrastructure\Eloquent\Models\Post` → `Database\Factories\PostFactory` と解決される。

---

## フロントエンド

### ルーティング（Wayfinder）

Ziggyではなく **Wayfinder** を使用。`.gitignore` に含まれる `resources/js/routes/` と `resources/js/actions/` はビルド時に自動生成される。

```typescript
// URL文字列が必要な場合（useForm().post() など）
import { store } from '@/routes/posts';
post(store.url());

// Inertia Link の href に渡す場合（RouteDefinitionオブジェクトも受け付ける）
import { timeline } from '@/routes';
<Link href={timeline()} />
```

### レイアウト自動割り当て（`resources/js/app.tsx`）

| ページパス | レイアウト |
|---|---|
| `welcome` | なし |
| `auth/*` | AuthLayout |
| `settings/*`, `teams/*` | AppLayout + SettingsLayout |
| その他 | AppLayout |

### ドメインエンティティのJSON変換

`DateTimeImmutable` などJSON化できないプロパティを持つEntityには `JsonSerializable` を実装する（`app/Domain/Post/Entities/Post.php` が参考実装）。

---

## テストのルール

- **Pest** を使用し `it()` / `test()` 記法で書く
- Unit Test: `tests/Unit/` — DBに依存しない。UseCaseはRepositoryを `mock()` する
- Feature Test: `tests/Feature/` — SQLiteインメモリDB使用。認証ルートは `actingAs()` を使用
- **実装と同時にテストを作成する**（後回し禁止）

---

## UUID ルール

- 全テーブルの主キーはUUID（文字列型）
- IDの生成はEloquentモデルの `boot()` で `Str::uuid()` を使う
- マイグレーション: `$table->uuid('id')->primary()`
- 外部キー: `$table->foreignUuid('user_id')`

---

## 実装順序

新機能を追加する際は以下の順序で実装する。

1. マイグレーション
2. Domain Entity + **Unit Test**
3. Repository Interface（Domain層）
4. Eloquent Model（Infrastructure層）
5. Repository 実装（Infrastructure層）
6. UseCase（Application層）+ **Unit Test**
7. FormRequest（Presentation層）
8. Controller（Presentation層）+ **Feature Test**
9. ルーティング（`routes/web.php`）
10. フロントエンド（ページ・コンポーネント）

---

## 命名規則

### PHP
| 対象 | 規則 | 例 |
|------|------|----|
| クラス名 | PascalCase | `CreatePostUseCase` |
| メソッド名 | camelCase | `execute()` |
| DBカラム名 | snake_case | `user_id`, `created_at` |
| Repository実装クラス | `Eloquent` + Interface名 | `EloquentPostRepository` |

### TypeScript
| 対象 | 規則 | 例 |
|------|------|----|
| コンポーネント | PascalCase | `PostCard` |
| ファイル名 | kebab-case | `post-card.tsx` |
| ページファイル | Inertiaのパスに合わせる | `pages/users/show.tsx` |

---

## 禁止事項

- Controllerにビジネスロジックを書く
- UseCaseからEloquentModelを直接importする
- Domain層にLaravelのクラスをimportする
- `app/Models/` 配下に新規ファイルを作成する（Infrastructure層を使う）
- フロントエンドからAPIルート（`/api/*`）を叩く
- テストを書かずに実装を完了とする

---

## 参照ドキュメント

- 要件定義: `requirements.md`
- 設計書: `design.md`

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v3
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/wayfinder (WAYFINDER) - v0
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- @inertiajs/react (INERTIA_REACT) - v3
- react (REACT) - v19
- tailwindcss (TAILWINDCSS) - v4
- @laravel/vite-plugin-wayfinder (WAYFINDER_VITE) - v0
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `fortify-development` — ACTIVATE when the user works on authentication in Laravel. This includes login, registration, password reset, email verification, two-factor authentication (2FA/TOTP/QR codes/recovery codes), profile updates, password confirmation, or any auth-related routes and controllers. Activate when the user mentions Fortify, auth, authentication, login, register, signup, forgot password, verify email, 2FA, or references app/Actions/Fortify/, CreateNewUser, UpdateUserProfileInformation, FortifyServiceProvider, config/fortify.php, or auth guards. Fortify is the frontend-agnostic authentication backend for Laravel that registers all auth routes and controllers. Also activate when building SPA or headless authentication, customizing login redirects, overriding response contracts like LoginResponse, or configuring login throttling. Do NOT activate for Laravel Passport (OAuth2 API tokens), Socialite (OAuth social login), or non-auth Laravel features.
- `laravel-best-practices` — Apply this skill whenever writing, reviewing, or refactoring Laravel PHP code. This includes creating or modifying controllers, models, migrations, form requests, policies, jobs, scheduled commands, service classes, and Eloquent queries. Triggers for N+1 and query performance issues, caching strategies, authorization and security patterns, validation, error handling, queue and job configuration, route definitions, and architectural decisions. Also use for Laravel code reviews and refactoring existing Laravel code to follow best practices. Covers any task involving Laravel backend PHP code patterns.
- `wayfinder-development` — Use this skill for Laravel Wayfinder which auto-generates typed functions for Laravel controllers and routes. ALWAYS use this skill when frontend code needs to call backend routes or controller actions. Trigger when: connecting any React/Vue/Svelte/Inertia frontend to Laravel controllers, routes, building end-to-end features with both frontend and backend, wiring up forms or links to backend endpoints, fixing route-related TypeScript errors, importing from @/actions or @/routes, or running wayfinder:generate. Use Wayfinder route functions instead of hardcoded URLs. Covers: wayfinder() vite plugin, .url()/.get()/.post()/.form(), query params, route model binding, tree-shaking. Do not use for backend-only task
- `pest-testing` — Use this skill for Pest PHP testing in Laravel projects only. Trigger whenever any test is being written, edited, fixed, or refactored — including fixing tests that broke after a code change, adding assertions, converting PHPUnit to Pest, adding datasets, and TDD workflows. Always activate when the user asks how to write something in Pest, mentions test files or directories (tests/Feature, tests/Unit, tests/Browser), or needs browser testing, smoke testing multiple pages for JS errors, or architecture tests. Covers: test()/it()/expect() syntax, datasets, mocking, browser testing (visit/click/fill), smoke testing, arch(), Livewire component tests, RefreshDatabase, and all Pest 4 features. Do not use for factories, seeders, migrations, controllers, models, or non-test PHP code.
- `inertia-react-development` — Develops Inertia.js v3 React client-side applications. Activates when creating React pages, forms, or navigation; using <Link>, <Form>, useForm, useHttp, setLayoutProps, or router; working with deferred props, prefetching, optimistic updates, instant visits, or polling; or when user mentions React with Inertia, React pages, React forms, or React navigation.
- `tailwindcss-development` — Always invoke when the user's message includes 'tailwind' in any form. Also invoke for: building responsive grid layouts (multi-column card grids, product grids), flex/grid page structures (dashboards with sidebars, fixed topbars, mobile-toggle navs), styling UI components (cards, tables, navbars, pricing sections, forms, inputs, badges), adding dark mode variants, fixing spacing or typography, and Tailwind v3/v4 work. The core use case: writing or fixing Tailwind utility classes in HTML templates (Blade, JSX, Vue). Skip for backend PHP logic, database queries, API routes, JavaScript with no HTML/CSS component, CSS file audits, build tool configuration, and vanilla CSS.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `vendor/bin/sail npm run build`, `vendor/bin/sail npm run dev`, or `vendor/bin/sail composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `vendor/bin/sail artisan route:list`). Use `vendor/bin/sail artisan list` to discover available commands and `vendor/bin/sail artisan [command] --help` to check parameters.
- Inspect routes with `vendor/bin/sail artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `vendor/bin/sail artisan config:show app.name`, `vendor/bin/sail artisan config:show database.default`. Or read config files directly from the `config/` directory.
- To check environment variables, read the `.env` file directly.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `vendor/bin/sail artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `vendor/bin/sail artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== sail rules ===

# Laravel Sail

- This project runs inside Laravel Sail's Docker containers. You MUST execute all commands through Sail.
- Start services using `vendor/bin/sail up -d` and stop them with `vendor/bin/sail stop`.
- Open the application in the browser by running `vendor/bin/sail open`.
- Always prefix PHP, Artisan, Composer, and Node commands with `vendor/bin/sail`. Examples:
    - Run Artisan Commands: `vendor/bin/sail artisan migrate`
    - Install Composer packages: `vendor/bin/sail composer install`
    - Execute Node commands: `vendor/bin/sail npm run dev`
    - Execute PHP scripts: `vendor/bin/sail php [script]`
- View all available Sail commands by running `vendor/bin/sail` without arguments.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `vendor/bin/sail artisan test --compact` with a specific filename or filter.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-react-development` when working with Inertia client-side patterns.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `vendor/bin/sail artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `vendor/bin/sail artisan list` and check their parameters with `vendor/bin/sail artisan [command] --help`.
- If you're creating a generic PHP class, use `vendor/bin/sail artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `vendor/bin/sail artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `vendor/bin/sail artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `vendor/bin/sail npm run build` or ask the user to run `vendor/bin/sail npm run dev` or `vendor/bin/sail composer run dev`.

## Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== wayfinder/core rules ===

# Laravel Wayfinder

Use Wayfinder to generate TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/sail bin pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/sail bin pint --test --format agent`, simply run `vendor/bin/sail bin pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `vendor/bin/sail artisan make:test --pest {name}`.
- Run tests: `vendor/bin/sail artisan test --compact` or filter: `vendor/bin/sail artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

=== inertia-react/core rules ===

# Inertia + React

- IMPORTANT: Activate `inertia-react-development` when working with Inertia React client-side patterns.

</laravel-boost-guidelines>
