import { Form, Head, Link } from '@inertiajs/react';
import { Lock, Mail, User } from 'lucide-react';
import InputError from '@/components/input-error';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

const inputStyle = {
    border: '1.5px solid #7a3b2b',
    borderRadius: '8px',
    backgroundColor: '#fff6ee',
    color: '#3b1a0e',
};

export default function Register() {
    return (
        <>
            <Head title="アカウント登録" />

            <div
                className="flex h-svh items-center justify-center p-4"
                style={{ backgroundColor: '#fff6ee' }}
            >
                <div
                    className="flex h-full max-h-[700px] w-full max-w-4xl overflow-hidden shadow-lg"
                    style={{
                        borderRadius: '13px',
                        border: '1.5px solid #7a3b2b',
                    }}
                >
                    {/* 左側：猫イラスト */}
                    <div
                        className="hidden w-1/2 lg:block"
                        style={{ backgroundColor: '#f5e6d8' }}
                    >
                        <img
                            src="/images/register-cat.png"
                            alt="猫のイラスト"
                            className="h-full w-full object-cover"
                        />
                    </div>

                    {/* 右側：登録フォーム */}
                    <div className="flex w-full flex-col justify-center overflow-y-auto px-10 py-8 lg:w-1/2">
                        {/* ロゴ・ブランド */}
                        <div className="mb-5 flex flex-col items-center gap-2">
                            <img
                                src="/images/cat-logo.png"
                                alt="Tsuttakataロゴ"
                                className="h-12 w-12 object-contain"
                            />
                            <h1
                                className="text-2xl text-[#c85a1a]"
                                style={{ fontFamily: "'Abril Fatface', serif" }}
                            >
                                Tsuttakata
                            </h1>
                            <p className="text-sm" style={{ color: '#7a3b2b' }}>
                                新しいアカウントを作成
                            </p>
                        </div>

                        <Form
                            {...store.form()}
                            resetOnSuccess={[
                                'password',
                                'password_confirmation',
                            ]}
                            disableWhileProcessing
                            className="flex flex-col gap-3.5"
                        >
                            {({ processing, errors }) => (
                                <>
                                    {/* お名前 */}
                                    <div className="flex flex-col gap-1.5">
                                        <Label
                                            htmlFor="name"
                                            className="text-sm font-medium"
                                            style={{ color: '#7a3b2b' }}
                                        >
                                            お名前
                                        </Label>
                                        <div className="relative">
                                            <User
                                                className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2"
                                                style={{ color: '#7a3b2b' }}
                                            />
                                            <input
                                                id="name"
                                                type="text"
                                                name="name"
                                                required
                                                autoFocus
                                                tabIndex={1}
                                                autoComplete="name"
                                                placeholder="山田 太郎"
                                                className="w-full rounded-lg py-2.5 pr-4 pl-10 text-sm transition outline-none focus:ring-2"
                                                style={inputStyle}
                                            />
                                        </div>
                                        <InputError message={errors.name} />
                                    </div>

                                    {/* メールアドレス */}
                                    <div className="flex flex-col gap-1.5">
                                        <Label
                                            htmlFor="email"
                                            className="text-sm font-medium"
                                            style={{ color: '#7a3b2b' }}
                                        >
                                            メールアドレス
                                        </Label>
                                        <div className="relative">
                                            <Mail
                                                className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2"
                                                style={{ color: '#7a3b2b' }}
                                            />
                                            <input
                                                id="email"
                                                type="email"
                                                name="email"
                                                required
                                                tabIndex={2}
                                                autoComplete="email"
                                                placeholder="example@email.com"
                                                className="w-full rounded-lg py-2.5 pr-4 pl-10 text-sm transition outline-none focus:ring-2"
                                                style={inputStyle}
                                            />
                                        </div>
                                        <InputError message={errors.email} />
                                    </div>

                                    {/* パスワード */}
                                    <div className="flex flex-col gap-1.5">
                                        <Label
                                            htmlFor="password"
                                            className="text-sm font-medium"
                                            style={{ color: '#7a3b2b' }}
                                        >
                                            パスワード
                                        </Label>
                                        <div className="relative">
                                            <Lock
                                                className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2"
                                                style={{ color: '#7a3b2b' }}
                                            />
                                            <input
                                                id="password"
                                                type="password"
                                                name="password"
                                                required
                                                tabIndex={3}
                                                autoComplete="new-password"
                                                placeholder="••••••••"
                                                className="w-full rounded-lg py-2.5 pr-4 pl-10 text-sm transition outline-none focus:ring-2"
                                                style={inputStyle}
                                            />
                                        </div>
                                        <InputError message={errors.password} />
                                    </div>

                                    {/* パスワード（確認） */}
                                    <div className="flex flex-col gap-1.5">
                                        <Label
                                            htmlFor="password_confirmation"
                                            className="text-sm font-medium"
                                            style={{ color: '#7a3b2b' }}
                                        >
                                            パスワード（確認）
                                        </Label>
                                        <div className="relative">
                                            <Lock
                                                className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2"
                                                style={{ color: '#7a3b2b' }}
                                            />
                                            <input
                                                id="password_confirmation"
                                                type="password"
                                                name="password_confirmation"
                                                required
                                                tabIndex={4}
                                                autoComplete="new-password"
                                                placeholder="••••••••"
                                                className="w-full rounded-lg py-2.5 pr-4 pl-10 text-sm transition outline-none focus:ring-2"
                                                style={inputStyle}
                                            />
                                        </div>
                                        <InputError
                                            message={
                                                errors.password_confirmation
                                            }
                                        />
                                    </div>

                                    {/* アカウント作成ボタン */}
                                    <button
                                        type="submit"
                                        tabIndex={5}
                                        disabled={processing}
                                        data-test="register-user-button"
                                        className="mt-1 flex w-full items-center justify-center gap-2 rounded-lg py-2.5 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-60"
                                        style={{
                                            backgroundColor: '#c85a1a',
                                            borderRadius: '8px',
                                        }}
                                    >
                                        {processing ? (
                                            <Spinner />
                                        ) : (
                                            'アカウント作成'
                                        )}
                                    </button>

                                    {/* または */}
                                    <div className="relative flex items-center">
                                        <div
                                            className="flex-1 border-t"
                                            style={{ borderColor: '#7a3b2b' }}
                                        />
                                        <span
                                            className="mx-3 text-xs"
                                            style={{ color: '#7a3b2b' }}
                                        >
                                            または
                                        </span>
                                        <div
                                            className="flex-1 border-t"
                                            style={{ borderColor: '#7a3b2b' }}
                                        />
                                    </div>

                                    {/* ソーシャルログイン（ダミー） */}
                                    <div className="flex justify-center gap-4">
                                        {/* Google */}
                                        <button
                                            type="button"
                                            className="flex h-11 w-11 items-center justify-center rounded-full border transition hover:opacity-80"
                                            style={{
                                                borderColor: '#7a3b2b',
                                                backgroundColor: '#fff6ee',
                                            }}
                                            aria-label="Googleで登録"
                                        >
                                            <svg
                                                className="h-5 w-5"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    fill="#4285F4"
                                                    d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                                                />
                                                <path
                                                    fill="#34A853"
                                                    d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                                                />
                                                <path
                                                    fill="#FBBC05"
                                                    d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                                                />
                                                <path
                                                    fill="#EA4335"
                                                    d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                                                />
                                            </svg>
                                        </button>

                                        {/* Apple */}
                                        <button
                                            type="button"
                                            className="flex h-11 w-11 items-center justify-center rounded-full border transition hover:opacity-80"
                                            style={{
                                                borderColor: '#7a3b2b',
                                                backgroundColor: '#fff6ee',
                                            }}
                                            aria-label="Appleで登録"
                                        >
                                            <svg
                                                className="h-5 w-5"
                                                viewBox="0 0 24 24"
                                                fill="currentColor"
                                            >
                                                <path d="M12.152 6.896c-.948 0-2.415-1.078-3.96-1.04-2.04.027-3.91 1.183-4.961 3.014-2.117 3.675-.546 9.103 1.519 12.09 1.013 1.454 2.208 3.09 3.792 3.039 1.52-.065 2.09-.987 3.935-.987 1.831 0 2.35.987 3.96.948 1.637-.026 2.676-1.48 3.676-2.948 1.156-1.688 1.636-3.325 1.662-3.415-.039-.013-3.182-1.221-3.22-4.857-.026-3.04 2.48-4.494 2.597-4.559-1.429-2.09-3.623-2.324-4.39-2.376-2-.156-3.675 1.09-4.61 1.09zM15.53 3.83c.843-1.012 1.4-2.427 1.245-3.83-1.207.052-2.662.805-3.532 1.818-.78.896-1.454 2.338-1.273 3.714 1.338.104 2.715-.688 3.559-1.701" />
                                            </svg>
                                        </button>

                                        {/* Twitter / X */}
                                        <button
                                            type="button"
                                            className="flex h-11 w-11 items-center justify-center rounded-full border transition hover:opacity-80"
                                            style={{
                                                borderColor: '#7a3b2b',
                                                backgroundColor: '#fff6ee',
                                            }}
                                            aria-label="Twitterで登録"
                                        >
                                            <svg
                                                className="h-4 w-4"
                                                viewBox="0 0 24 24"
                                                fill="currentColor"
                                            >
                                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                                            </svg>
                                        </button>
                                    </div>

                                    {/* ログインリンク */}
                                    <p
                                        className="text-center text-sm"
                                        style={{ color: '#7a3b2b' }}
                                    >
                                        すでにアカウントをお持ちですか？{' '}
                                        <Link
                                            href={login()}
                                            className="font-semibold hover:underline"
                                            style={{ color: '#c85a1a' }}
                                            tabIndex={6}
                                        >
                                            ログイン
                                        </Link>
                                    </p>
                                </>
                            )}
                        </Form>
                    </div>
                </div>
            </div>
        </>
    );
}

// AuthLayoutを使わずに登録ページ独自のレイアウトを使う
Register.layout = false;
