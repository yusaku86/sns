import { Form, Head, Link } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, Mail } from 'lucide-react';
import InputError from '@/components/input-error';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { email } from '@/routes/password';

export default function ForgotPassword({ status }: { status?: string }) {
    return (
        <>
            <Head title="パスワード再設定" />

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
                    {/* 左側：黒猫イラスト */}
                    <div
                        className="hidden w-1/2 items-center justify-center lg:flex"
                        style={{ backgroundColor: '#f5e6d8' }}
                    >
                        <img
                            src="/images/forgot-password-cat.png"
                            alt="黒猫のイラスト"
                            className="h-full w-full object-contain"
                        />
                    </div>

                    {/* 右側：フォームエリア */}
                    <div className="flex w-full flex-col justify-center px-10 py-12 lg:w-1/2">
                        {/* ロゴ・ブランド */}
                        <div className="mb-8 flex flex-col items-center gap-3">
                            <img
                                src="/images/cat-logo.png"
                                alt="Tsuttakataロゴ"
                                className="h-14 w-14 object-contain"
                            />
                            <h1
                                className="text-3xl text-[#c85a1a]"
                                style={{ fontFamily: "'Abril Fatface', serif" }}
                            >
                                Tsuttakata
                            </h1>
                        </div>

                        {/* フォームカード */}
                        <div
                            className="flex flex-col gap-5 rounded-xl p-8"
                            style={{
                                border: '1.5px solid #7a3b2b',
                                backgroundColor: '#fff6ee',
                            }}
                        >
                            <div className="flex flex-col items-center gap-3">
                                <div
                                    className="flex h-16 w-16 items-center justify-center rounded-full"
                                    style={{ backgroundColor: '#f5e6d8' }}
                                >
                                    <Mail
                                        className="h-8 w-8"
                                        style={{ color: '#c85a1a' }}
                                    />
                                </div>

                                <h2
                                    className="text-2xl font-bold"
                                    style={{
                                        color: '#3b1a0e',
                                        fontFamily: "'Abril Fatface', serif",
                                    }}
                                >
                                    パスワードを再設定
                                </h2>

                                <p
                                    className="text-center text-sm leading-relaxed"
                                    style={{ color: '#7a3b2b' }}
                                >
                                    登録済みのメールアドレスを入力してください。
                                    <br />
                                    パスワード再設定用のリンクをお送りします。
                                </p>
                            </div>

                            {status && (
                                <p className="text-center text-sm font-medium text-green-600">
                                    {status}
                                </p>
                            )}

                            <Form
                                {...email.form()}
                                className="flex flex-col gap-4"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="flex flex-col gap-1">
                                            <input
                                                id="email"
                                                type="email"
                                                name="email"
                                                autoComplete="off"
                                                autoFocus
                                                placeholder="example@email.com"
                                                className="w-full rounded-lg border px-4 py-3 text-sm outline-none focus:ring-2"
                                                style={{
                                                    borderColor: '#7a3b2b',
                                                    color: '#2b1e16',
                                                    backgroundColor: '#fff6ee',
                                                    // @ts-expect-error CSS custom property not in CSSProperties type
                                                    '--tw-ring-color':
                                                        '#c85a1a',
                                                }}
                                            />
                                            <InputError
                                                message={errors.email}
                                            />
                                        </div>

                                        <button
                                            type="submit"
                                            disabled={processing}
                                            data-test="email-password-reset-link-button"
                                            className="flex w-full items-center justify-center gap-2 rounded-lg py-3 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-60"
                                            style={{
                                                backgroundColor: '#c85a1a',
                                                borderRadius: '8px',
                                            }}
                                        >
                                            {processing ? (
                                                <Spinner />
                                            ) : (
                                                <>
                                                    再設定メールを送信
                                                    <ArrowRight className="h-4 w-4" />
                                                </>
                                            )}
                                        </button>
                                    </>
                                )}
                            </Form>

                            <Link
                                href={login()}
                                className="flex items-center justify-center gap-1 text-sm hover:underline"
                                style={{ color: '#7a3b2b' }}
                            >
                                <ArrowLeft className="h-4 w-4" />
                                ログイン画面に戻る
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}

ForgotPassword.layout = false;
