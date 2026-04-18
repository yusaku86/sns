import { Form, Head, Link, usePage } from '@inertiajs/react';
import { Mail } from 'lucide-react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/delete-user';
import InputError from '@/components/input-error';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';

export default function Profile({
    mustVerifyEmail,
    status,
}: {
    mustVerifyEmail: boolean;
    status?: string;
}) {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="アカウント設定" />

            <h1 className="sr-only">アカウント設定</h1>

            <div className="space-y-8">
                <div>
                    <h2
                        className="text-base font-semibold"
                        style={{ color: '#1a1a1a' }}
                    >
                        メールアドレス
                    </h2>
                    <p className="mt-1 text-sm text-muted-foreground">
                        ログインに使用するメールアドレスを変更できます
                    </p>
                </div>

                <Form
                    {...ProfileController.update.form()}
                    options={{ preserveScroll: true }}
                    className="space-y-5"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="flex flex-col gap-1.5">
                                <label
                                    htmlFor="email"
                                    className="text-sm font-medium"
                                    style={{ color: '#374151' }}
                                >
                                    メールアドレス
                                </label>
                                <div className="relative">
                                    <Mail className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                    <input
                                        id="email"
                                        type="email"
                                        name="email"
                                        required
                                        autoComplete="username"
                                        defaultValue={auth.user.email}
                                        placeholder="example@email.com"
                                        className="w-full rounded-lg border py-2.5 pr-4 pl-10 text-sm transition outline-none focus:ring-2"
                                        style={{
                                            borderColor: '#d1d5db',
                                            backgroundColor: '#ffffff',
                                            color: '#111827',
                                            // @ts-expect-error css variable
                                            '--tw-ring-color': '#2c5f5d',
                                        }}
                                    />
                                </div>
                                <InputError message={errors.email} />
                            </div>

                            {mustVerifyEmail &&
                                auth.user.email_verified_at === null && (
                                    <div>
                                        <p className="text-sm text-muted-foreground">
                                            メールアドレスが未確認です。{' '}
                                            <Link
                                                href={send()}
                                                as="button"
                                                className="underline underline-offset-4 hover:opacity-80"
                                                style={{ color: '#2c5f5d' }}
                                            >
                                                確認メールを再送する
                                            </Link>
                                        </p>
                                        {status ===
                                            'verification-link-sent' && (
                                            <div className="mt-2 text-sm font-medium text-green-600">
                                                確認メールを送信しました。
                                            </div>
                                        )}
                                    </div>
                                )}

                            <div>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="rounded-lg px-5 py-2.5 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-60"
                                    style={{ backgroundColor: '#2c5f5d' }}
                                >
                                    変更を保存
                                </button>
                            </div>
                        </>
                    )}
                </Form>
            </div>

            <div className="my-8 border-t" style={{ borderColor: '#e5e7eb' }} />

            <DeleteUser />
        </>
    );
}

Profile.layout = {
    breadcrumbs: [
        {
            title: 'アカウント設定',
            href: edit(),
        },
    ],
};
