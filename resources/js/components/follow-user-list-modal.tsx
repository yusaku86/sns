import { Link, useForm } from '@inertiajs/react';
import * as Dialog from '@radix-ui/react-dialog';
import { X } from 'lucide-react';
import { store as followUser, destroy as unfollowUser } from '@/routes/follows';
import { show as showUser } from '@/routes/users';

export type FollowUser = {
    id: string;
    name: string;
    handle: string;
    profileImageUrl: string | null;
    isFollowedByAuthUser: boolean;
};

type ItemProps = {
    user: FollowUser;
    authUserId: string | undefined;
};

function FollowUserItem({ user, authUserId }: ItemProps) {
    const { post, delete: sendDelete, processing } = useForm();
    const initial = user.name.charAt(0).toUpperCase();
    const showFollowButton = authUserId && authUserId !== user.id;

    function handleFollow() {
        if (user.isFollowedByAuthUser) {
            sendDelete(unfollowUser.url(user.id));
        } else {
            post(followUser.url(user.id));
        }
    }

    return (
        <div className="flex items-center gap-3 border-b border-[#E5E7EB] p-4">
            <Link href={showUser.url(user.id)} className="shrink-0">
                {user.profileImageUrl ? (
                    <img
                        src={user.profileImageUrl}
                        alt={user.name}
                        className="h-10 w-10 rounded-full object-cover"
                    />
                ) : (
                    <div className="flex h-10 w-10 items-center justify-center rounded-full bg-[#3a6c72] text-sm font-semibold text-white">
                        {initial}
                    </div>
                )}
            </Link>

            <div className="min-w-0 flex-1">
                <Link href={showUser.url(user.id)} className="block">
                    <p className="truncate text-sm font-semibold text-[#191816] hover:underline">
                        {user.name}
                    </p>
                    <p className="font-mono text-sm text-[#8a8784]">
                        @{user.handle}
                    </p>
                </Link>
            </div>

            {showFollowButton && (
                <button
                    type="button"
                    onClick={handleFollow}
                    disabled={processing}
                    className={`h-9 shrink-0 rounded-full px-4 text-sm font-semibold transition-colors disabled:opacity-50 ${
                        user.isFollowedByAuthUser
                            ? 'border border-[#E5E7EB] bg-transparent text-[#111827] hover:border-[#b36b09] hover:text-[#b36b09]'
                            : 'bg-[#3a6c72] text-white hover:opacity-90'
                    }`}
                >
                    {user.isFollowedByAuthUser ? 'フォロー中' : 'フォローする'}
                </button>
            )}
        </div>
    );
}

type Props = {
    title: string;
    users: FollowUser[] | undefined;
    authUserId: string | undefined;
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export default function FollowUserListModal({
    title,
    users,
    authUserId,
    open,
    onOpenChange,
}: Props) {
    return (
        <Dialog.Root open={open} onOpenChange={onOpenChange}>
            <Dialog.Portal>
                <Dialog.Overlay className="fixed inset-0 z-40 bg-black/40" />
                <Dialog.Content className="fixed top-1/2 left-1/2 z-50 w-full max-w-md -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-[#f6f3ee] shadow-xl focus:outline-none">
                    <div className="flex items-center justify-between border-b border-[#E5E7EB] px-4 py-3">
                        <Dialog.Title className="text-base font-semibold text-[#191816]">
                            {title}
                        </Dialog.Title>
                        <Dialog.Close asChild>
                            <button
                                type="button"
                                className="rounded-full p-1 text-[#8a8784] hover:bg-[#eae4dc] hover:text-[#2b2a28]"
                                aria-label="閉じる"
                            >
                                <X size={18} />
                            </button>
                        </Dialog.Close>
                    </div>

                    <div className="max-h-[60vh] overflow-y-auto">
                        {users === undefined ? (
                            <p className="p-8 text-center text-sm text-[#8a8784]">
                                読み込み中...
                            </p>
                        ) : users.length === 0 ? (
                            <p className="p-8 text-center text-sm text-[#8a8784]">
                                まだユーザーがいません。
                            </p>
                        ) : (
                            users.map((user) => (
                                <FollowUserItem
                                    key={user.id}
                                    user={user}
                                    authUserId={authUserId}
                                />
                            ))
                        )}
                    </div>
                </Dialog.Content>
            </Dialog.Portal>
        </Dialog.Root>
    );
}
