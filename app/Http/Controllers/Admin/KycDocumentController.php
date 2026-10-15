<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KycAccessLog;
use App\Models\KycVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves KYC documents from the private disk through signed links only,
 * logging every access (docs/logic/compliance.md).
 */
class KycDocumentController extends Controller
{
    public function __invoke(Request $request, KycVerification $kyc, string $document): StreamedResponse
    {
        $path = $kyc->getAttribute(KycVerification::DOCUMENTS[$document]);

        abort_unless(is_string($path) && Storage::disk('local')->exists($path), 404);

        KycAccessLog::create([
            'kyc_verification_id' => $kyc->id,
            'user_id' => $request->user()?->id,
            'document' => $document,
            'ip' => $request->ip(),
            'accessed_at' => now(),
        ]);

        return Storage::disk('local')->response($path, headers: [
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }
}
