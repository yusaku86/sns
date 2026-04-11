import { Link, usePage } from '@inertiajs/react';
import { Compass, Home, User } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { explore, timeline } from '@/routes';
import { show } from '@/routes/users';
import type { NavItem } from '@/types';

export function AppSidebar() {
    const { auth } = usePage().props as {
        auth: { user: { id: string } | null };
    };

    const mainNavItems: NavItem[] = [
        {
            title: 'タイムライン',
            href: timeline(),
            icon: Home,
        },
        {
            title: '探索',
            href: explore(),
            icon: Compass,
        },
        ...(auth?.user
            ? [
                  {
                      title: 'プロフィール',
                      href: show(auth.user.id),
                      icon: User,
                  },
              ]
            : []),
    ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={timeline()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
