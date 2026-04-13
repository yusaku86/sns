import { Form } from '@inertiajs/react';
import { Camera } from 'lucide-react';
import { useRef, useState } from 'react';
import type { PropsWithChildren } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { update } from '@/routes/users';

type UserProfile = {
    id: string;
    name: string;
    bio: string | null;
    headerImageUrl: string | null;
    profileImageUrl: string | null;
};

export default function EditProfileModal({
    user,
    children,
}: PropsWithChildren<{ user: UserProfile }>) {
    const [open, setOpen] = useState(false);
    const [headerPreview, setHeaderPreview] = useState<string | null>(
        user.headerImageUrl,
    );
    const [profilePreview, setProfilePreview] = useState<string | null>(
        user.profileImageUrl,
    );

    const headerInputRef = useRef<HTMLInputElement>(null);
    const profileInputRef = useRef<HTMLInputElement>(null);

    const initial = user.name.charAt(0).toUpperCase();

    function handleOpenChange(value: boolean) {
        setOpen(value);

        if (!value) {
            setHeaderPreview(user.headerImageUrl);
            setProfilePreview(user.profileImageUrl);
        }
    }

    function handleHeaderChange(e: React.ChangeEvent<HTMLInputElement>) {
        const file = e.target.files?.[0];

        if (file) {
            setHeaderPreview(URL.createObjectURL(file));
        }
    }

    function handleProfileChange(e: React.ChangeEvent<HTMLInputElement>) {
        const file = e.target.files?.[0];

        if (file) {
            setProfilePreview(URL.createObjectURL(file));
        }
    }

    return (
        <Dialog open={open} onOpenChange={handleOpenChange}>
            <DialogTrigger asChild>{children}</DialogTrigger>
            <DialogContent className="gap-0 overflow-hidden p-0 sm:max-w-lg">
                <Form
                    key={String(open)}
                    {...update.form.put(user.id)}
                    onSuccess={() => setOpen(false)}
                >
                    {({ errors, processing }) => (
                        <>
                            <DialogHeader className="px-4 pt-4 pb-0">
                                <DialogTitle>プロフィールを修正</DialogTitle>
                            </DialogHeader>

                            {/* 画像エリア */}
                            <div className="relative mt-3">
                                {/* ヘッダー画像 */}
                                <button
                                    type="button"
                                    className="group relative block h-32 w-full cursor-pointer overflow-hidden bg-[#3a6c72]/20"
                                    onClick={() =>
                                        headerInputRef.current?.click()
                                    }
                                    aria-label="ヘッダー画像を変更"
                                >
                                    {headerPreview ? (
                                        <img
                                            src={headerPreview}
                                            alt=""
                                            className="h-full w-full object-cover"
                                        />
                                    ) : null}
                                    <div className="absolute inset-0 flex items-center justify-center bg-black/30 opacity-0 transition-opacity group-hover:opacity-100">
                                        <Camera
                                            size={28}
                                            className="text-white"
                                        />
                                    </div>
                                    <div className="absolute inset-0 flex items-center justify-center">
                                        <Camera
                                            size={28}
                                            className="text-white opacity-60 transition-opacity group-hover:opacity-0"
                                        />
                                    </div>
                                </button>
                                <input
                                    ref={headerInputRef}
                                    type="file"
                                    name="header_image"
                                    accept="image/jpeg,image/png,image/webp"
                                    className="hidden"
                                    onChange={handleHeaderChange}
                                />

                                {/* プロフィール画像 */}
                                <div className="absolute -bottom-10 left-4">
                                    <button
                                        type="button"
                                        className="group relative h-20 w-20 cursor-pointer"
                                        onClick={() =>
                                            profileInputRef.current?.click()
                                        }
                                        aria-label="プロフィール画像を変更"
                                    >
                                        <div className="flex h-20 w-20 items-center justify-center overflow-hidden rounded-full border-4 border-white bg-[#3a6c72] text-xl font-semibold text-white">
                                            {profilePreview ? (
                                                <img
                                                    src={profilePreview}
                                                    alt=""
                                                    className="h-full w-full object-cover"
                                                />
                                            ) : (
                                                <span>{initial}</span>
                                            )}
                                        </div>
                                        <div className="absolute inset-0 flex items-center justify-center rounded-full bg-black/30 opacity-0 transition-opacity group-hover:opacity-100">
                                            <Camera
                                                size={18}
                                                className="text-white"
                                            />
                                        </div>
                                    </button>
                                    <input
                                        ref={profileInputRef}
                                        type="file"
                                        name="profile_image"
                                        accept="image/jpeg,image/png,image/webp"
                                        className="hidden"
                                        onChange={handleProfileChange}
                                    />
                                </div>
                            </div>

                            {/* フォームフィールド */}
                            <div className="mt-12 space-y-4 px-4 pb-4">
                                {errors.header_image && (
                                    <InputError message={errors.header_image} />
                                )}
                                {errors.profile_image && (
                                    <InputError
                                        message={errors.profile_image}
                                    />
                                )}

                                <div className="grid gap-1.5">
                                    <Label htmlFor="edit-name">表示名</Label>
                                    <Input
                                        id="edit-name"
                                        name="name"
                                        defaultValue={user.name}
                                        required
                                        maxLength={255}
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-1.5">
                                    <Label htmlFor="edit-bio">自己紹介</Label>
                                    <textarea
                                        id="edit-bio"
                                        name="bio"
                                        defaultValue={user.bio ?? ''}
                                        maxLength={160}
                                        rows={3}
                                        className="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50"
                                    />
                                    <InputError message={errors.bio} />
                                </div>

                                <DialogFooter className="gap-2 pt-2">
                                    <DialogClose asChild>
                                        <Button variant="secondary">
                                            キャンセル
                                        </Button>
                                    </DialogClose>
                                    <Button type="submit" disabled={processing}>
                                        {processing ? '保存中...' : '保存'}
                                    </Button>
                                </DialogFooter>
                            </div>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
