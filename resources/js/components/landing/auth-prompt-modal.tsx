import { Link } from '@inertiajs/react';
import { CheckCircle2, Lock, X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { login, register } from '@/routes';

type AuthPromptModalProps = {
    isOpen: boolean;
    onClose: () => void;
    traderName?: string;
    actionType?: 'copy' | 'subscribe';
};

export function AuthPromptModal({
    isOpen,
    onClose,
    traderName,
    actionType = 'copy',
}: AuthPromptModalProps) {
    if (!isOpen) return null;

    const actionText =
        actionType === 'copy' ? 'Menyalin Sinyal' : 'Berlangganan Analisa';

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
            {/* Backdrop */}
            <div
                className="fixed inset-0 bg-black/75 transition-opacity"
                onClick={onClose}
            />

            {/* Modal Dialog */}
            <div className="relative z-10 w-full max-w-md rounded-xl border border-[#2a2e39] bg-[#1e222d] p-6 shadow-2xl">
                <button
                    onClick={onClose}
                    className="absolute top-4 right-4 rounded-md p-1 text-[#787b86] hover:bg-[#2a2e39] hover:text-white"
                >
                    <X className="h-5 w-5" />
                    <span className="sr-only">Tutup</span>
                </button>

                <div className="flex h-12 w-12 items-center justify-center rounded-xl border border-[#2962ff]/30 bg-[#2962ff]/10 text-[#2962ff]">
                    <Lock className="h-6 w-6" />
                </div>

                <h3 className="mt-4 text-xl font-bold tracking-tight text-white">
                    {traderName
                        ? `${actionText} ${traderName}`
                        : 'Akses Penuh Sinyal & Trader Pro'}
                </h3>

                <p className="mt-2 text-sm leading-relaxed text-[#b2b5be]">
                    Buat akun SooTa gratis untuk melihat parameter entry, target
                    Stop Loss, Take Profit, dan menghubungkan copy trade
                    langsung ke akun broker legal Anda.
                </p>

                <div className="mt-5 space-y-2 rounded-lg border border-[#2a2e39] bg-[#131722] p-3 text-xs text-[#d1d4dc]">
                    <div className="flex items-center gap-2">
                        <CheckCircle2 className="h-4 w-4 shrink-0 text-[#089981]" />
                        <span>
                            Sinyal real-time dengan proteksi batas risiko (Max
                            DD)
                        </span>
                    </div>
                    <div className="flex items-center gap-2">
                        <CheckCircle2 className="h-4 w-4 shrink-0 text-[#089981]" />
                        <span>
                            Audit kurva ekuitas dan riwayat trading lengkap
                        </span>
                    </div>
                    <div className="flex items-center gap-2">
                        <CheckCircle2 className="h-4 w-4 shrink-0 text-[#089981]" />
                        <span>Dukungan broker resmi teregulasi Bappebti</span>
                    </div>
                </div>

                <div className="mt-6 flex flex-col gap-2.5">
                    <Button
                        size="lg"
                        asChild
                        className="w-full bg-[#2962ff] font-semibold text-white hover:bg-[#1e53e5]"
                    >
                        <Link href={register()}>Daftar Akun Baru (Gratis)</Link>
                    </Button>
                    <Button
                        variant="ghost"
                        size="sm"
                        asChild
                        className="w-full text-[#b2b5be] hover:bg-[#2a2e39] hover:text-white"
                    >
                        <Link href={login()}>Sudah punya akun? Masuk</Link>
                    </Button>
                </div>
            </div>
        </div>
    );
}
