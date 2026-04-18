import { Form, Head } from '@inertiajs/react';
import { ShieldCheck } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TwoFactorRecoveryCodes from '@/components/two-factor-recovery-codes';
import TwoFactorSetupModal from '@/components/two-factor-setup-modal';
import { useTwoFactorAuth } from '@/hooks/use-two-factor-auth';
import { edit } from '@/routes/security';
import { disable, enable } from '@/routes/two-factor';

type Props = {
    canManageTwoFactor?: boolean;
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
};

export default function Security({
    canManageTwoFactor = false,
    requiresConfirmation = false,
    twoFactorEnabled = false,
}: Props) {
    const passwordInput = useRef<HTMLInputElement>(null);
    const currentPasswordInput = useRef<HTMLInputElement>(null);

    const {
        qrCodeSvg,
        hasSetupData,
        manualSetupKey,
        clearSetupData,
        clearTwoFactorAuthData,
        fetchSetupData,
        recoveryCodesList,
        fetchRecoveryCodes,
        errors,
    } = useTwoFactorAuth();
    const [showSetupModal, setShowSetupModal] = useState<boolean>(false);
    const prevTwoFactorEnabled = useRef(twoFactorEnabled);

    useEffect(() => {
        if (prevTwoFactorEnabled.current && !twoFactorEnabled) {
            clearTwoFactorAuthData();
        }

        prevTwoFactorEnabled.current = twoFactorEnabled;
    }, [twoFactorEnabled, clearTwoFactorAuthData]);

    return (
        <>
            <Head title="セキュリティ設定" />

            <h1 className="sr-only">セキュリティ設定</h1>

            <div className="space-y-8">
                <div>
                    <h2
                        className="text-base font-semibold"
                        style={{ color: '#1a1a1a' }}
                    >
                        パスワードの変更
                    </h2>
                    <p className="mt-1 text-sm text-muted-foreground">
                        安全を保つため、推測されにくいランダムなパスワードを設定してください
                    </p>
                </div>

                <Form
                    {...SecurityController.update.form()}
                    options={{
                        preserveScroll: true,
                    }}
                    resetOnError={[
                        'password',
                        'password_confirmation',
                        'current_password',
                    ]}
                    resetOnSuccess
                    onError={(errors) => {
                        if (errors.password) {
                            passwordInput.current?.focus();
                        }

                        if (errors.current_password) {
                            currentPasswordInput.current?.focus();
                        }
                    }}
                    className="space-y-5"
                >
                    {({ errors, processing }) => (
                        <>
                            <div className="flex flex-col gap-1.5">
                                <label
                                    htmlFor="current_password"
                                    className="text-sm font-medium"
                                    style={{ color: '#374151' }}
                                >
                                    現在のパスワード
                                </label>

                                <PasswordInput
                                    id="current_password"
                                    ref={currentPasswordInput}
                                    name="current_password"
                                    className="w-full rounded-lg border px-4 py-2.5 text-sm transition outline-none focus:ring-2"
                                    style={{
                                        borderColor: '#d1d5db',
                                        backgroundColor: '#ffffff',
                                        color: '#111827',
                                        // @ts-expect-error css variable
                                        '--tw-ring-color': '#2c5f5d',
                                    }}
                                    autoComplete="current-password"
                                    placeholder="現在のパスワード"
                                />

                                <InputError message={errors.current_password} />
                            </div>

                            <div className="flex flex-col gap-1.5">
                                <label
                                    htmlFor="password"
                                    className="text-sm font-medium"
                                    style={{ color: '#374151' }}
                                >
                                    新しいパスワード
                                </label>

                                <PasswordInput
                                    id="password"
                                    ref={passwordInput}
                                    name="password"
                                    className="w-full rounded-lg border px-4 py-2.5 text-sm transition outline-none focus:ring-2"
                                    style={{
                                        borderColor: '#d1d5db',
                                        backgroundColor: '#ffffff',
                                        color: '#111827',
                                        // @ts-expect-error css variable
                                        '--tw-ring-color': '#2c5f5d',
                                    }}
                                    autoComplete="new-password"
                                    placeholder="新しいパスワード"
                                />

                                <InputError message={errors.password} />
                            </div>

                            <div className="flex flex-col gap-1.5">
                                <label
                                    htmlFor="password_confirmation"
                                    className="text-sm font-medium"
                                    style={{ color: '#374151' }}
                                >
                                    新しいパスワード（確認）
                                </label>

                                <PasswordInput
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    className="w-full rounded-lg border px-4 py-2.5 text-sm transition outline-none focus:ring-2"
                                    style={{
                                        borderColor: '#d1d5db',
                                        backgroundColor: '#ffffff',
                                        color: '#111827',
                                        // @ts-expect-error css variable
                                        '--tw-ring-color': '#2c5f5d',
                                    }}
                                    autoComplete="new-password"
                                    placeholder="新しいパスワード（確認）"
                                />

                                <InputError
                                    message={errors.password_confirmation}
                                />
                            </div>

                            <div>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    data-test="update-password-button"
                                    className="rounded-lg px-5 py-2.5 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-60"
                                    style={{ backgroundColor: '#2c5f5d' }}
                                >
                                    パスワードを変更
                                </button>
                            </div>
                        </>
                    )}
                </Form>
            </div>

            {canManageTwoFactor && (
                <>
                    <div
                        className="my-8 border-t"
                        style={{ borderColor: '#e5e7eb' }}
                    />

                    <div className="space-y-8">
                        <div>
                            <h2
                                className="text-base font-semibold"
                                style={{ color: '#1a1a1a' }}
                            >
                                二段階認証
                            </h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                二段階認証の設定を管理します
                            </p>
                        </div>

                        {twoFactorEnabled ? (
                            <div className="flex flex-col items-start justify-start space-y-4">
                                <p className="text-sm text-muted-foreground">
                                    ログイン時にスマートフォンのTOTPアプリからワンタイムパスワードの入力が求められます。
                                </p>

                                <div className="relative inline">
                                    <Form {...disable.form()}>
                                        {({ processing }) => (
                                            <button
                                                type="submit"
                                                disabled={processing}
                                                className="rounded-lg px-5 py-2.5 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-60"
                                                style={{
                                                    backgroundColor: '#dc2626',
                                                }}
                                            >
                                                二段階認証を無効にする
                                            </button>
                                        )}
                                    </Form>
                                </div>

                                <TwoFactorRecoveryCodes
                                    recoveryCodesList={recoveryCodesList}
                                    fetchRecoveryCodes={fetchRecoveryCodes}
                                    errors={errors}
                                />
                            </div>
                        ) : (
                            <div className="flex flex-col items-start justify-start space-y-4">
                                <p className="text-sm text-muted-foreground">
                                    二段階認証を有効にすると、ログイン時にスマートフォンのTOTPアプリからワンタイムパスワードの入力が求められます。
                                </p>

                                <div>
                                    {hasSetupData ? (
                                        <button
                                            onClick={() =>
                                                setShowSetupModal(true)
                                            }
                                            className="inline-flex items-center gap-2 rounded-lg px-5 py-2.5 text-sm font-semibold text-white transition hover:opacity-90"
                                            style={{
                                                backgroundColor: '#2c5f5d',
                                            }}
                                        >
                                            <ShieldCheck className="h-4 w-4" />
                                            設定を続ける
                                        </button>
                                    ) : (
                                        <Form
                                            {...enable.form()}
                                            onSuccess={() =>
                                                setShowSetupModal(true)
                                            }
                                        >
                                            {({ processing }) => (
                                                <button
                                                    type="submit"
                                                    disabled={processing}
                                                    className="rounded-lg px-5 py-2.5 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-60"
                                                    style={{
                                                        backgroundColor:
                                                            '#2c5f5d',
                                                    }}
                                                >
                                                    二段階認証を有効にする
                                                </button>
                                            )}
                                        </Form>
                                    )}
                                </div>
                            </div>
                        )}

                        <TwoFactorSetupModal
                            isOpen={showSetupModal}
                            onClose={() => setShowSetupModal(false)}
                            requiresConfirmation={requiresConfirmation}
                            twoFactorEnabled={twoFactorEnabled}
                            qrCodeSvg={qrCodeSvg}
                            manualSetupKey={manualSetupKey}
                            clearSetupData={clearSetupData}
                            fetchSetupData={fetchSetupData}
                            errors={errors}
                        />
                    </div>
                </>
            )}
        </>
    );
}

Security.layout = {
    breadcrumbs: [
        {
            title: 'セキュリティ設定',
            href: edit(),
        },
    ],
};
