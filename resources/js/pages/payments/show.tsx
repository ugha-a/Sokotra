import { Head, Link, useForm } from '@inertiajs/react';
import {
    CircleCheck,
    Clock,
    CircleX,
    Building2,
    Copy,
    Check,
    Upload,
    FileCheck2,
    ArrowLeft,
    AlertCircle,
} from 'lucide-react';
import { useState, type FormEvent } from 'react';
import { toast } from 'sonner';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatDate, formatRupiah } from '@/lib/format';
import { proof } from '@/routes/payments';

type Payment = {
    id: number;
    amount: number;
    status: string;
    status_label: string;
    gateway: string;
    gateway_reference: string;
    description: string;
    checkout_url: string | null;
    return_url: string;
    expires_at: string | null;
    created_at: string | null;
    bank_destination?: {
        bank_name: string;
        account_number: string;
        account_holder: string;
        instructions: string;
    };
    proof?: {
        has_proof: boolean;
        sender_name: string | null;
        sender_bank: string | null;
        transferred_at: string | null;
    };
};

export default function PaymentStatus({ payment }: { payment: Payment }) {
    const [copiedField, setCopiedField] = useState<string | null>(null);

    const isBankTransfer = payment.gateway === 'bank_transfer';
    const isPending = payment.status === 'pending';
    const isPaid = payment.status === 'paid';

    const form = useForm<{
        proof: File | null;
        sender_name: string;
        sender_bank: string;
    }>({
        proof: null,
        sender_name: '',
        sender_bank: '',
    });

    const copyToClipboard = (text: string, label: string, key: string) => {
        void navigator.clipboard.writeText(text);
        setCopiedField(key);
        toast.success(`${label} berhasil disalin`);
        setTimeout(() => setCopiedField(null), 2000);
    };

    const handleUploadProof = (e: FormEvent) => {
        e.preventDefault();
        if (!form.data.proof) {
            toast.error('Pilih file bukti transfer terlebih dahulu');
            return;
        }

        form.post(proof.url(payment.id), {
            forceFormData: true,
            preserveScroll: true,
        });
    };

    const Icon = isPaid ? CircleCheck : isPending ? Clock : CircleX;
    const tone = isPaid
        ? 'text-profit'
        : isPending
          ? 'text-pending'
          : 'text-loss';

    return (
        <>
            <Head title="Status Pembayaran" />
            <div className="mx-auto flex w-full max-w-xl flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col items-center gap-2 text-center">
                    <div
                        className={`flex size-14 items-center justify-center rounded-full ${
                            isPaid
                                ? 'bg-profit-soft'
                                : isPending
                                  ? 'bg-pending-soft'
                                  : 'bg-loss-soft'
                        }`}
                    >
                        <Icon className={`size-8 ${tone}`} />
                    </div>
                    <Heading
                        title={
                            isBankTransfer && isPending
                                ? 'Menunggu Transfer Bank'
                                : payment.status_label
                        }
                        description={`${payment.description} · ${formatRupiah(payment.amount)}`}
                    />
                </div>

                {isBankTransfer && isPending && payment.bank_destination && (
                    <div className="flex flex-col gap-4">
                        <div className="rounded-xl border bg-card p-4 shadow-xs md:p-6">
                            <div className="mb-4 flex items-center justify-between border-b pb-3">
                                <div>
                                    <span className="text-xs text-muted-foreground">
                                        Total Tagihan
                                    </span>
                                    <div className="text-2xl font-semibold tracking-tight text-foreground">
                                        {formatRupiah(payment.amount)}
                                    </div>
                                </div>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() =>
                                        copyToClipboard(
                                            payment.amount.toString(),
                                            'Nominal transfer',
                                            'amount',
                                        )
                                    }
                                >
                                    {copiedField === 'amount' ? (
                                        <Check className="size-3.5" />
                                    ) : (
                                        <Copy className="size-3.5" />
                                    )}
                                    Salin
                                </Button>
                            </div>

                            <div className="space-y-4 text-sm">
                                <div className="flex items-start justify-between gap-2">
                                    <div>
                                        <span className="text-xs text-muted-foreground">
                                            Kode / Referensi Pembayaran
                                        </span>
                                        <div className="font-mono font-medium">
                                            {payment.gateway_reference}
                                        </div>
                                    </div>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() =>
                                            copyToClipboard(
                                                payment.gateway_reference,
                                                'Referensi pembayaran',
                                                'ref',
                                            )
                                        }
                                    >
                                        {copiedField === 'ref' ? (
                                            <Check className="size-3.5" />
                                        ) : (
                                            <Copy className="size-3.5" />
                                        )}
                                        Salin
                                    </Button>
                                </div>

                                <div className="rounded-lg border bg-muted/40 p-3.5">
                                    <div className="mb-2 flex items-center gap-2 font-medium">
                                        <Building2 className="size-4 text-brand" />
                                        <span>Rekening Tujuan Admin</span>
                                    </div>
                                    <div className="space-y-2 text-xs md:text-sm">
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">
                                                Bank:
                                            </span>
                                            <span className="font-medium">
                                                {
                                                    payment.bank_destination
                                                        .bank_name
                                                }
                                            </span>
                                        </div>
                                        <div className="flex items-center justify-between">
                                            <span className="text-muted-foreground">
                                                Nomor Rekening:
                                            </span>
                                            <div className="flex items-center gap-1.5">
                                                <span className="font-mono font-semibold">
                                                    {
                                                        payment.bank_destination
                                                            .account_number
                                                    }
                                                </span>
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        copyToClipboard(
                                                            payment
                                                                .bank_destination!
                                                                .account_number,
                                                            'Nomor rekening',
                                                            'account',
                                                        )
                                                    }
                                                    className="inline-flex size-6 items-center justify-center rounded-sm hover:bg-muted"
                                                    title="Salin nomor rekening"
                                                >
                                                    {copiedField ===
                                                    'account' ? (
                                                        <Check className="size-3 text-profit" />
                                                    ) : (
                                                        <Copy className="size-3" />
                                                    )}
                                                </button>
                                            </div>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">
                                                Atas Nama:
                                            </span>
                                            <span className="font-medium">
                                                {
                                                    payment.bank_destination
                                                        .account_holder
                                                }
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {payment.bank_destination.instructions && (
                                    <div className="flex items-start gap-2 rounded-lg border border-pending/30 bg-pending-soft p-3 text-xs text-foreground">
                                        <AlertCircle className="mt-0.5 size-4 shrink-0 text-pending" />
                                        <p>
                                            {
                                                payment.bank_destination
                                                    .instructions
                                            }
                                        </p>
                                    </div>
                                )}
                            </div>
                        </div>

                        {payment.proof?.has_proof ? (
                            <div className="rounded-xl border border-profit/30 bg-profit-soft p-4 text-sm md:p-6">
                                <div className="flex items-center gap-2 font-medium text-profit">
                                    <FileCheck2 className="size-5" />
                                    Bukti transfer telah dikirim
                                </div>
                                <p className="mt-2 text-xs text-muted-foreground md:text-sm">
                                    Terima kasih! Bukti transfer kamu sudah kami
                                    terima dan sedang dalam proses verifikasi
                                    oleh admin. Layanan akan otomatis aktif
                                    setelah pembayaran disetujui.
                                </p>
                                {(payment.proof.sender_name ||
                                    payment.proof.transferred_at) && (
                                    <div className="mt-3 border-t border-profit/20 pt-3 text-xs text-muted-foreground">
                                        {payment.proof.sender_name && (
                                            <div>
                                                Pengirim:{' '}
                                                {payment.proof.sender_name}{' '}
                                                {payment.proof.sender_bank &&
                                                    `(${payment.proof.sender_bank})`}
                                            </div>
                                        )}
                                        {payment.proof.transferred_at && (
                                            <div>
                                                Dikirim:{' '}
                                                {formatDate(
                                                    payment.proof
                                                        .transferred_at,
                                                )}
                                            </div>
                                        )}
                                    </div>
                                )}
                            </div>
                        ) : (
                            <form
                                onSubmit={handleUploadProof}
                                className="rounded-xl border bg-card p-4 shadow-xs md:p-6"
                            >
                                <h3 className="mb-3 font-medium text-foreground">
                                    Konfirmasi Bukti Transfer
                                </h3>
                                <div className="space-y-4">
                                    <div className="space-y-1.5">
                                        <Label htmlFor="proof">
                                            Foto / File Bukti Transfer (JPG,
                                            PNG, PDF maks. 5MB) *
                                        </Label>
                                        <Input
                                            id="proof"
                                            type="file"
                                            accept="image/jpeg,image/png,image/webp,application/pdf"
                                            onChange={(e) =>
                                                form.setData(
                                                    'proof',
                                                    e.target.files?.[0] ?? null,
                                                )
                                            }
                                            required
                                        />
                                        {form.errors.proof && (
                                            <p className="text-xs text-loss">
                                                {form.errors.proof}
                                            </p>
                                        )}
                                    </div>

                                    <div className="grid gap-3 sm:grid-cols-2">
                                        <div className="space-y-1.5">
                                            <Label htmlFor="sender_name">
                                                Nama Pengirim (Opsional)
                                            </Label>
                                            <Input
                                                id="sender_name"
                                                placeholder="Contoh: Budi Santoso"
                                                value={form.data.sender_name}
                                                onChange={(e) =>
                                                    form.setData(
                                                        'sender_name',
                                                        e.target.value,
                                                    )
                                                }
                                            />
                                        </div>
                                        <div className="space-y-1.5">
                                            <Label htmlFor="sender_bank">
                                                Bank Asal (Opsional)
                                            </Label>
                                            <Input
                                                id="sender_bank"
                                                placeholder="Contoh: BCA / Mandiri"
                                                value={form.data.sender_bank}
                                                onChange={(e) =>
                                                    form.setData(
                                                        'sender_bank',
                                                        e.target.value,
                                                    )
                                                }
                                            />
                                        </div>
                                    </div>

                                    <Button
                                        type="submit"
                                        className="w-full"
                                        disabled={form.processing}
                                    >
                                        <Upload className="size-4" />
                                        {form.processing
                                            ? 'Mengunggah...'
                                            : 'Kirim Bukti Pembayaran'}
                                    </Button>
                                </div>
                            </form>
                        )}
                    </div>
                )}

                {!isBankTransfer && isPending && payment.checkout_url && (
                    <Button asChild className="w-full">
                        <a href={payment.checkout_url}>Lanjutkan pembayaran</a>
                    </Button>
                )}

                <div className="flex justify-center">
                    <Button variant="outline" asChild>
                        <Link href={payment.return_url}>
                            <ArrowLeft className="size-4" />
                            Kembali ke {payment.description}
                        </Link>
                    </Button>
                </div>
            </div>
        </>
    );
}
