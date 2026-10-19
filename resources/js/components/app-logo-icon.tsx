import type { ImgHTMLAttributes } from 'react';
import { cn } from '@/lib/utils';

export default function AppLogoIcon({
    className,
    alt = 'Sotra',
    ...props
}: ImgHTMLAttributes<HTMLImageElement> & Record<string, any>) {
    return (
        <img
            src="/images/sotra-logo.png"
            alt={alt}
            className={cn('aspect-square rounded-md object-contain', className)}
            {...props}
        />
    );
}
