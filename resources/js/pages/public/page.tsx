import { Head } from '@inertiajs/react';
import { formatDate } from '@/lib/format';

type Props = {
    page: { title: string; html: string; updated_at: string | null };
};

export default function ContentPage({ page }: Props) {
    return (
        <>
            <Head title={page.title} />
            <article className="mx-auto grid max-w-3xl gap-4">
                <h1 className="text-2xl font-semibold tracking-tight">
                    {page.title}
                </h1>
                {page.updated_at && (
                    <p className="text-xs text-muted-foreground">
                        Diperbarui {formatDate(page.updated_at)}
                    </p>
                )}
                {/* Server-rendered from admin markdown with raw HTML stripped (ContentPageController). */}
                <div
                    className="grid gap-3 text-sm leading-relaxed [&_a]:text-brand [&_a]:underline [&_h2]:mt-4 [&_h2]:text-lg [&_h2]:font-semibold [&_h3]:font-semibold [&_li]:ml-5 [&_ol]:list-decimal [&_ul]:list-disc"
                    dangerouslySetInnerHTML={{ __html: page.html }}
                />
            </article>
        </>
    );
}
