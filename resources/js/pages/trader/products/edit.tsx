import { Head, router, useForm, usePage } from '@inertiajs/react';
import { TriangleAlert } from 'lucide-react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { MoneyInput } from '@/components/domain/money-input';
import { StatusPill } from '@/components/domain/status-pill';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { statusTone } from '@/lib/status';
import { index, store, submit, update } from '@/routes/trader/products';
import type { MasterOption } from '@/types';

type Product = {
    id: number;
    category_id: number;
    name: string;
    summary: string;
    description: string;
    platform: string;
    price: number;
    version: string;
    tradingview_url: string | null;
    has_file: boolean;
    screenshot_urls: string[];
    status: string;
    status_label: string;
    rejection_reason: string | null;
};

type Props = {
    product: Product | null;
    blockReason: string | null;
    categories: MasterOption[];
    platforms: MasterOption[];
    maxFileMb: number;
    commissionPercent: number;
};

type FormData = {
    category_id: string;
    name: string;
    summary: string;
    description: string;
    platform: string;
    price: string;
    version: string;
    tradingview_url: string;
    file: File | null;
    screenshots: File[];
    _method?: string;
};

export default function EditProduct({
    product,
    blockReason,
    categories,
    platforms,
    maxFileMb,
    commissionPercent,
}: Props) {
    const { errors: pageErrors } = usePage<{ errors: Record<string, string> }>()
        .props;
    const form = useForm<FormData>({
        category_id: product ? String(product.category_id) : '',
        name: product?.name ?? '',
        summary: product?.summary ?? '',
        description: product?.description ?? '',
        platform: product?.platform ?? 'mt5',
        price: product ? String(product.price) : '',
        version: product?.version ?? '1.0.0',
        tradingview_url: product?.tradingview_url ?? '',
        file: null,
        screenshots: [],
    });
    const errors = form.errors as Record<string, string | undefined>;
    const isTradingView = form.data.platform === 'tradingview';

    if (blockReason) {
        return (
            <div className="p-4 md:p-6">
                <Head title="Produk Baru" />
                <div className="flex gap-3 rounded-xl border border-pending/40 bg-pending-soft p-4 text-sm">
                    <TriangleAlert className="size-4 shrink-0 text-pending" />{' '}
                    <p>{blockReason}</p>
                </div>
            </div>
        );
    }

    const save = (event: React.FormEvent) => {
        event.preventDefault();

        if (product) {
            // Multipart PUT: send as POST with method spoofing.
            form.transform((data) => ({ ...data, _method: 'put' }));
            form.post(update(product.id).url, {
                forceFormData: true,
                preserveScroll: true,
            });
        } else {
            form.submit(store(), { forceFormData: true });
        }
    };

    const field = (
        id: keyof FormData,
        label: string,
        input: React.ReactNode,
        hint?: string,
    ) => (
        <div className="grid gap-2">
            <Label htmlFor={id}>{label}</Label>
            {input}
            {hint && <p className="text-xs text-muted-foreground">{hint}</p>}
            <InputError message={errors[id]} />
        </div>
    );

    return (
        <>
            <Head title={product ? `Ubah ${product.name}` : 'Produk Baru'} />
            <div className="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <Heading
                        title={product ? product.name : 'Produk Baru'}
                        description="Mengubah file atau deskripsi produk yang sudah tayang akan mengirimnya kembali ke review. Perubahan harga tidak perlu review."
                    />
                    {product && (
                        <StatusPill
                            label={product.status_label}
                            tone={statusTone(product.status)}
                        />
                    )}
                </div>
                {product?.rejection_reason && (
                    <p className="rounded-lg bg-loss-soft p-3 text-sm text-loss">
                        Ditolak: {product.rejection_reason}
                    </p>
                )}
                <InputError message={pageErrors.product} />

                <form
                    onSubmit={save}
                    className="grid gap-5 rounded-xl border p-4 md:p-6"
                >
                    <div className="grid gap-5 sm:grid-cols-2">
                        {field(
                            'category_id',
                            'Kategori',
                            <Select
                                value={form.data.category_id}
                                onValueChange={(value) =>
                                    form.setData('category_id', value)
                                }
                            >
                                <SelectTrigger id="category_id">
                                    <SelectValue placeholder="Pilih…" />
                                </SelectTrigger>
                                <SelectContent>
                                    {categories.map((option) => (
                                        <SelectItem
                                            key={option.value}
                                            value={String(option.value)}
                                        >
                                            {option.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>,
                        )}
                        {field(
                            'platform',
                            'Platform',
                            <Select
                                value={form.data.platform}
                                onValueChange={(value) =>
                                    form.setData('platform', value)
                                }
                            >
                                <SelectTrigger id="platform">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {platforms.map((option) => (
                                        <SelectItem
                                            key={option.value}
                                            value={String(option.value)}
                                        >
                                            {option.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>,
                        )}
                    </div>
                    {field(
                        'name',
                        'Nama produk',
                        <Input
                            id="name"
                            value={form.data.name}
                            onChange={(e) =>
                                form.setData('name', e.target.value)
                            }
                        />,
                    )}
                    {field(
                        'summary',
                        'Ringkasan',
                        <Input
                            id="summary"
                            value={form.data.summary}
                            onChange={(e) =>
                                form.setData('summary', e.target.value)
                            }
                        />,
                        'Satu kalimat. Klaim seperti “pasti profit” tidak diizinkan.',
                    )}
                    {field(
                        'description',
                        'Deskripsi',
                        <Textarea
                            id="description"
                            rows={8}
                            value={form.data.description}
                            onChange={(e) =>
                                form.setData('description', e.target.value)
                            }
                        />,
                        'Jelaskan cara kerja, setting yang disarankan, dan risikonya.',
                    )}
                    <div className="grid gap-5 sm:grid-cols-2">
                        {field(
                            'price',
                            'Harga',
                            <MoneyInput
                                id="price"
                                value={form.data.price}
                                onChange={(e) =>
                                    form.setData('price', e.target.value)
                                }
                            />,
                            `Isi 0 untuk gratis. Komisi platform ${commissionPercent}%.`,
                        )}
                        {field(
                            'version',
                            'Versi',
                            <Input
                                id="version"
                                value={form.data.version}
                                onChange={(e) =>
                                    form.setData('version', e.target.value)
                                }
                            />,
                        )}
                    </div>
                    {isTradingView
                        ? field(
                              'tradingview_url',
                              'Link script TradingView',
                              <Input
                                  id="tradingview_url"
                                  type="url"
                                  value={form.data.tradingview_url}
                                  onChange={(e) =>
                                      form.setData(
                                          'tradingview_url',
                                          e.target.value,
                                      )
                                  }
                              />,
                              'Script invite-only; berikan akses ke pembeli setelah pembelian.',
                          )
                        : field(
                              'file',
                              'File produk',
                              <Input
                                  id="file"
                                  type="file"
                                  accept=".ex4,.ex5,.mq4,.mq5,.zip"
                                  onChange={(e) =>
                                      form.setData(
                                          'file',
                                          e.target.files?.[0] ?? null,
                                      )
                                  }
                              />,
                              `${product?.has_file ? 'File sudah ada; unggah untuk mengganti. ' : ''}.ex4, .ex5, .mq4, .mq5, atau .zip, maksimal ${maxFileMb} MB.`,
                          )}
                    {field(
                        'screenshots',
                        'Screenshot',
                        <Input
                            id="screenshots"
                            type="file"
                            accept="image/*"
                            multiple
                            onChange={(e) =>
                                form.setData(
                                    'screenshots',
                                    Array.from(e.target.files ?? []),
                                )
                            }
                        />,
                        'Maksimal 5 gambar. Unggah ulang untuk mengganti semua screenshot.',
                    )}
                    {product && product.screenshot_urls.length > 0 && (
                        <div className="grid grid-cols-3 gap-2">
                            {product.screenshot_urls.map((url) => (
                                <img
                                    key={url}
                                    src={url}
                                    alt=""
                                    className="aspect-video rounded-md border object-cover"
                                />
                            ))}
                        </div>
                    )}
                    <div className="flex flex-wrap gap-2">
                        <Button type="submit" disabled={form.processing}>
                            {form.processing && <Spinner />} Simpan
                        </Button>
                        {product &&
                            (product.status === 'draft' ||
                                product.status === 'rejected') && (
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() =>
                                        router.visit(submit(product.id), {
                                            preserveScroll: true,
                                        })
                                    }
                                >
                                    Ajukan review
                                </Button>
                            )}
                    </div>
                </form>
            </div>
        </>
    );
}

EditProduct.layout = {
    breadcrumbs: [{ title: 'Produk Saya', href: index() }],
};
