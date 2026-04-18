import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/hooks/use-initials';
import type { User } from '@/types';

export function UserInfo({
    user,
    showEmail = false,
}: {
    user: User;
    showEmail?: boolean;
}) {
    const getInitials = useInitials();
    const showAvatar = Boolean(user.profile_image_url);

    return (
        <>
            <Avatar className="h-8 w-8 overflow-hidden rounded-full">
                {showAvatar ? (
                    <AvatarImage
                        src={user.profile_image_url!}
                        alt={user.name}
                    />
                ) : null}
                <AvatarFallback className="rounded-full bg-[#3a6c72] text-white">
                    {getInitials(user.name)}
                </AvatarFallback>
            </Avatar>
            <div className="grid flex-1 text-left text-sm leading-tight">
                <span className="truncate font-medium">{user.name}</span>
                {showEmail ? (
                    <span className="truncate text-xs text-muted-foreground">
                        {user.email}
                    </span>
                ) : null}
            </div>
        </>
    );
}
