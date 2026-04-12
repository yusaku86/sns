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
            className={`h-10 rounded-full px-4 text-sm font-semibold transition-colors disabled:opacity-50 ${
                isFollowing
                    ? 'border border-[#E5E7EB] bg-transparent text-[#111827] hover:border-[#b36b09] hover:text-[#b36b09]'
                    : 'border-transparent bg-[#3a6c72] text-white hover:opacity-90'
            }`}
        >
            {isFollowing ? 'フォロー中' : 'フォローする'}
        </button>
    );
}
