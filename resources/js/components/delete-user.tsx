import { Form } from '@inertiajs/react';
import { AlertTriangle } from 'lucide-react';
import { useRef } from 'react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';

export default function DeleteUser() {
    const passwordInput = useRef<HTMLInputElement>(null);

    return (
        <div className="space-y-6">
            <div>
                <h2 className="text-base font-semibold text-gray-900">
                    アカウントを削除する
                </h2>
                <p className="mt-1 text-sm text-muted-foreground">
                    アカウントとすべてのデータを完全に削除します
                </p>
            </div>

            <div className="space-y-4 rounded-lg border border-red-200 bg-red-50 p-4">
                <div className="flex items-start gap-2 text-red-600">
                    <AlertTriangle className="mt-0.5 h-4 w-4 shrink-0" />
                    <div className="space-y-1">
                        <p className="text-sm font-semibold">
                            この操作は取り消すことができません
                        </p>
                        <p className="text-sm">
                            アカウントを削除すると、以下のデータがすべて失われます：
                        </p>
                        <ul className="mt-1 list-inside list-disc space-y-0.5 text-sm">
                            <li>投稿・メディア</li>
                            <li>フォロワー・フォロー中のリスト</li>
                            <li>ブックマーク・メッセージ履歴</li>
                            <li>プロフィール情報・設定</li>
                        </ul>
                    </div>
                </div>

                <Dialog>
                    <DialogTrigger asChild>
                        <button
                            type="button"
                            data-test="delete-user-button"
                            className="rounded-lg px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90"
                            style={{ backgroundColor: '#dc2626' }}
                        >
                            アカウントを削除する
                        </button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogTitle>
                            本当にアカウントを削除しますか？
                        </DialogTitle>
                        <DialogDescription>
                            アカウントを削除すると、すべてのデータが完全に失われます。
                            続行するにはパスワードを入力してください。
                        </DialogDescription>

                        <Form
                            {...ProfileController.destroy.form()}
                            options={{ preserveScroll: true }}
                            onError={() => passwordInput.current?.focus()}
                            resetOnSuccess
                            className="space-y-6"
                        >
                            {({ resetAndClearErrors, processing, errors }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label
                                            htmlFor="password"
                                            className="sr-only"
                                        >
                                            パスワード
                                        </Label>
                                        <PasswordInput
                                            id="password"
                                            name="password"
                                            ref={passwordInput}
                                            placeholder="パスワード"
                                            autoComplete="current-password"
                                        />
                                        <InputError message={errors.password} />
                                    </div>

                                    <DialogFooter className="gap-2">
                                        <DialogClose asChild>
                                            <button
                                                type="button"
                                                className="rounded-lg border px-4 py-2 text-sm font-medium transition hover:bg-gray-50"
                                                onClick={() =>
                                                    resetAndClearErrors()
                                                }
                                            >
                                                キャンセル
                                            </button>
                                        </DialogClose>
                                        <button
                                            type="submit"
                                            disabled={processing}
                                            data-test="confirm-delete-user-button"
                                            className="rounded-lg px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-60"
                                            style={{
                                                backgroundColor: '#dc2626',
                                            }}
                                        >
                                            削除する
                                        </button>
                                    </DialogFooter>
                                </>
                            )}
                        </Form>
                    </DialogContent>
                </Dialog>
            </div>
        </div>
    );
}
