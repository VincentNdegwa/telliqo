<?php

namespace App\Http\Controllers;

use App\Services\QRCodeService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QRCodeController extends Controller
{
    public function __construct(
        protected QRCodeService $qrCodeService
    ) {}

    /**
     * Display the QR code page.
     */
    public function index(Request $request): Response
    {
        $business = $request->user()->getCurrentBusiness();

        // Generate QR code if not exists
        if (! $business->qr_code_path) {
            $this->qrCodeService->generateForBusiness($business);
            $business->refresh();
        }

        $qrCodeUrl = $this->qrCodeService->getUrl($business);

        return Inertia::render('QRCode/Index', [
            'business' => $business,
            'qrCodeUrl' => $qrCodeUrl,
            'reviewUrl' => route('feedback.submit', ['business' => $business->slug]),
        ]);
    }

    /**
     * Download QR code.
     */
    public function download(Request $request)
    {
        $business = $request->user()->getCurrentBusiness();
        
        $format = $request->input('format', 'svg');
        $size = $request->input('size', 300);

        $qrCode = $this->qrCodeService->generateCustom($business, [
            'size' => $size,
        ]);

        return response($qrCode)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', "attachment; filename=\"{$business->slug}-qr-code.svg\"");
    }

    /**
     * Regenerate QR code.
     */
    public function regenerate(Request $request)
    {
        $business = $request->user()->getCurrentBusiness();
        
        $this->qrCodeService->regenerate($business);

        return back()->with('success', 'QR code regenerated successfully.');
    }
}
