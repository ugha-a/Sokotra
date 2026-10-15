import { router, usePage } from '@inertiajs/react';
import { UserCheck, UserPlus } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { login } from '@/routes';
import { follow, unfollow } from '@/routes/traders';

/**
 * Follow toggle with optimistic state; guests are sent to login.
 */
export function FollowButton({
    traderId,
    isFollowing,
}: {
    traderId: number;
    isFollowing: boolean;
}) {
    const { auth } = usePage().props;
    const [following, setFollowing] = useState(isFollowing);

    const toggle = () => {
        if (!auth.user) {
            router.visit(login());

            return;
        }

        const next = !following;
        setFollowing(next);
        router.visit(next ? follow(traderId) : unfollow(traderId), {
            preserveScroll: true,
            onError: () => setFollowing(!next),
        });
    };

    return (
        <Button variant={following ? 'outline' : 'default'} onClick={toggle}>
            {following ? <UserCheck /> : <UserPlus />}
            {following ? 'Diikuti' : 'Ikuti'}
        </Button>
    );
}
