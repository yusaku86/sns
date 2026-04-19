import { Form, Head, Link } from '@inertiajs/react';
import { ArrowRight, Mail } from 'lucide-react';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

export default function VerifyEmail({ status }: { status?: string }) {
    return (
        <>
            <Head title="メール認証" />

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
                            src="/images/mail-cat.png"
                            alt="黒猫のイラスト"
                            className="h-full w-full object-cover"
                        />
                    </div>

                    {/* 右側：メール認証コンテンツ */}
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

                        {/* メール認証カード */}
                        <div
                            className="flex flex-col items-center gap-5 rounded-xl p-8"
                            style={{
                                border: '1.5px solid #7a3b2b',
                                backgroundColor: '#fff6ee',
                            }}
                        >
                            {/* エンベロープアイコン */}
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
                                メール認証
                            </h2>

                            <p
                                className="text-center text-sm leading-relaxed"
                                style={{ color: '#7a3b2b' }}
                            >
                                ご登録いただいたメールアドレスに認証メールを送信しました。
                                <br />
                                メール内のリンクをクリックして認証を完了してください。
                            </p>

                            {status === 'verification-link-sent' && (
                                <p className="text-center text-sm font-medium text-green-600">
                                    認証メールを再送信しました。
                                </p>
                            )}

                            <Form {...send.form()} className="w-full">
                                {({ processing }) => (
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="mt-2 flex w-full items-center justify-center gap-2 rounded-lg py-3 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-60"
                                        style={{
                                            backgroundColor: '#c85a1a',
                                            borderRadius: '8px',
                                        }}
                                    >
                                        {processing ? (
                                            <Spinner />
                                        ) : (
                                            <>
                                                メールを再送信
                                                <ArrowRight className="h-4 w-4" />
                                            </>
                                        )}
                                    </button>
                                )}
                            </Form>

                            <Link
                                href={logout()}
                                method="post"
                                as="button"
                                className="text-sm hover:underline"
                                style={{ color: '#7a3b2b' }}
                            >
                                ログアウト
                            </Link>

                            {/* ヘルプテキスト */}
                            <div
                                className="w-full rounded-lg p-4 text-xs leading-relaxed"
                                style={{
                                    backgroundColor: '#f5e6d8',
                                    border: '1px solid #e8c9a0',
                                    color: '#7a3b2b',
                                }}
                            >
                                メールが届かない場合は、迷惑メールフォルダをご確認ください。
                                それでも届かない場合は、上のボタンからメールを再送信してください。
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}

VerifyEmail.layout = false;
