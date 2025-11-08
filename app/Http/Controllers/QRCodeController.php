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
     * Display the QR code designer page.
     */
    public function index(Request $request): Response
    {
        $business = $request->user()->getCurrentBusiness();

        // Generate QR code if not exists
        if (! $business->qr_code_path) {
            $this->qrCodeService->generateForBusiness($business, ['store' => true]);
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
     * Preview QR code with customization
     */
    public function preview(Request $request)
    {
        $business = $request->user()->getCurrentBusiness();
        
        $mode = $request->input('mode', 'qr'); // qr or poster
        
        if ($mode === 'poster') {
            // For poster preview, return a simple preview image
            $options = [
                'template' => $request->input('template', 'modern'),
                'poster_size' => $request->input('poster_size', 'a4'),
                'custom_text' => $request->input('custom_text'),
                'qr_size' => $request->input('qr_size', 800),
                'background_color' => $request->input('background_color', '#f3f4f6'),
                'qr_foreground' => $request->input('qr_foreground', '#000000'),
                'qr_background' => $request->input('qr_background', '#ffffff'),
            ];
            
            // Generate a smaller preview QR for poster preview
            $qrOptions = [
                'size' => 300,
                'foreground_color' => $options['qr_foreground'],
                'background_color' => $options['qr_background'],
                'margin' => 10,
            ];
            
            $qrCode = $this->qrCodeService->generateForBusiness($business, $qrOptions);
            
            return response($qrCode)
                ->header('Content-Type', 'image/svg+xml')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
        }
        
        // Regular QR code preview
        $options = [
            'size' => $request->input('size', 300),
            'foreground_color' => $request->input('foreground_color', '#000000'),
            'background_color' => $request->input('background_color', '#ffffff'),
            'margin' => $request->input('margin', 0),
        ];

        $qrCode = $this->qrCodeService->generateForBusiness($business, $options);

        return response($qrCode)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }

    /**
     * Preview poster as PDF
     */
    public function previewPoster(Request $request)
    {
        $business = $request->user()->getCurrentBusiness();
        
        $options = [
            'template' => $request->input('template', 'modern'),
            'poster_size' => $request->input('poster_size', 'a4'),
            'custom_text' => $request->input('custom_text'),
            'qr_size' => $request->input('qr_size', 800),
            'background_color' => $request->input('background_color', '#f3f4f6'),
            'qr_foreground' => $request->input('qr_foreground', '#000000'),
            'qr_background' => $request->input('qr_background', '#ffffff'),
        ];
        
        $pdf = $this->qrCodeService->generatePoster($business, $options);
        
        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }

    /**
     * Download QR code in various formats
     */
    public function download(Request $request)
    {
        $business = $request->user()->getCurrentBusiness();
        
        $format = $request->input('format', 'svg'); // svg, png, poster
        $size = $request->input('size', 1000);
        
        $options = [
            'size' => $size,
            'foreground_color' => $request->input('foreground_color', '#000000'),
            'background_color' => $request->input('background_color', '#ffffff'),
            'margin' => $request->input('margin', 20),
        ];

        if ($format === 'poster') {
            $posterOptions = array_merge($options, [
                'template' => $request->input('template', 'modern'),
                'poster_size' => $request->input('poster_size', 'a4'),
                'custom_text' => $request->input('custom_text'),
                'qr_size' => $request->input('qr_size', 800),
                'qr_foreground' => $request->input('qr_foreground', '#000000'),
                'qr_background' => $request->input('qr_background', '#ffffff'),
            ]);
            
            $content = $this->qrCodeService->generatePoster($business, $posterOptions);
            $filename = "{$business->slug}-poster.pdf";
            $contentType = 'application/pdf';
        } elseif ($format === 'png') {
            $content = $this->qrCodeService->generatePng($business, $options);
            $filename = "{$business->slug}-qr-code.png";
            $contentType = 'image/png';
        } else {
            $content = $this->qrCodeService->generateForBusiness($business, $options);
            $filename = "{$business->slug}-qr-code.svg";
            $contentType = 'image/svg+xml';
        }

        return response($content)
            ->header('Content-Type', $contentType)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
