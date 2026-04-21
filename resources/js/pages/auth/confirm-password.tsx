import { Form, Head } from '@inertiajs/react';
import { ShieldCheck } from 'lucide-react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/password/confirm';

export default function ConfirmPassword() {
    return (
        <>
            <Head title="パスワードの再入力" />

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
                    {/* 左側：セキュリティ猫イラスト */}
                    <div
                        className="hidden w-1/2 items-center justify-center lg:flex"
                        style={{ backgroundColor: '#f5e6d8' }}
                    >
                        <img
                            src="/images/confirm-password-cat.png"
                            alt="セキュリティシールドを持つ黒猫のイラスト"
                            className="h-full w-full object-contain p-8"
                        />
                    </div>

                    {/* 右側：パスワード確認フォーム */}
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
                            <div>
                                <h2
                                    className="text-xl font-bold"
                                    style={{ color: '#3b1a0e' }}
                                >
                                    パスワードの再入力
                                </h2>
                                <p
                                    className="mt-1 text-sm leading-relaxed"
                                    style={{ color: '#7a3b2b' }}
                                >
                                    このページは保護されたエリアです。
                                    <br />
                                    続行する前にパスワードを確認してください。
                                </p>
                            </div>

                            <Form
                                {...store.form()}
                                resetOnSuccess={['password']}
                            >
                                {({ processing, errors }) => (
                                    <div className="flex flex-col gap-4">
                                        <div className="flex flex-col gap-1">
                                            <label
                                                htmlFor="password"
                                                className="text-sm font-medium"
                                                style={{ color: '#3b1a0e' }}
                                            >
                                                パスワード
                                            </label>
                                            <PasswordInput
                                                id="password"
                                                name="password"
                                                placeholder="パスワード"
                                                autoComplete="current-password"
                                                autoFocus
                                            />
                                            <InputError
                                                message={errors.password}
                                            />
                                        </div>

                                        <button
                                            type="submit"
                                            disabled={processing}
                                            className="mt-2 flex w-full items-center justify-center gap-2 rounded-lg py-3 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-60"
                                            style={{
                                                backgroundColor: '#c85a1a',
                                            }}
                                        >
                                            {processing ? (
                                                <Spinner />
                                            ) : (
                                                'パスワードを確認'
                                            )}
                                        </button>
                                    </div>
                                )}
                            </Form>

                            {/* セキュリティ通知 */}
                            <div
                                className="flex items-center gap-2 text-xs"
                                style={{ color: '#7a3b2b' }}
                            >
                                <ShieldCheck className="h-4 w-4 shrink-0" />
                                <span>
                                    アカウントのセキュリティを保護しています
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}

ConfirmPassword.layout = false;
