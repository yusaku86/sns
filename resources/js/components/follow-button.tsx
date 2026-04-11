import { useForm } from '@inertiajs/react';
import { store as followUser, destroy as unfollowUser } from '@/routes/follows';

type Props = {
    userId: string;
    isFollowing: boolean;
};

export default function FollowButton({ userId, isFollowing }: Props) {
    const { post, delete: sendDelete, processing } = useForm();

    function handleClick() {
        if (isFollowing) {
            sendDelete(unfollowUser.url(userId));
        } else {
            post(followUser.url(userId));
        }
    }

    return (
        <button
            onClick={handleClick}
            disabled={processing}
            className={`rounded-full border px-4 py-1.5 text-sm font-semibold transition-colors disabled:opacity-50 ${
                isFollowing
                    ? 'border-border bg-transparent hover:border-destructive hover:text-destructive'
                    : 'border-transparent bg-foreground text-background hover:opacity-80'
            }`}
        >
            {isFollowing ? 'フォロー中' : 'フォローする'}
        </button>
    );
}
