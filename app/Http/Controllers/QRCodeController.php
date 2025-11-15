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

        if (!user_can('qr.manage', $business)) {
            abort(403, 'You do not have permission to access QR codes.');
        }

        if (! $business->qr_code_path) {
            $this->qrCodeService->generateForBusiness($business, ['store' => true]);
            $business->refresh();
        }

        $qrCodeUrl = $this->qrCodeService->getUrl($business);

        return Inertia::render('QRCode/Designer', [
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
        
        $bgImage = null;
        if ($request->hasFile('bg_image')) {
            $file = $request->file('bg_image');
            $bgImage = 'data:' . $file->getMimeType() . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
        }
        
        $logoData = null;
        if ($business->logo && file_exists(storage_path('app/public/' . $business->logo))) {
            $logoPath = storage_path('app/public/' . $business->logo);
            $logoMime = mime_content_type($logoPath);
            $logoData = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
        }
        
        $options = [
            'bg_color' => $request->input('bg_color', '#f8f9fa'),
            'text_color' => $request->input('text_color', '#1a1a1a'),
            'title' => $request->input('title', $business->name),
            'description' => $request->input('description', ''),
            'footer' => $request->input('footer', ''),
            'qr_size' => $request->input('qr_size', 600),
            'qr_foreground' => $request->input('qr_foreground', '#000000'),
            'qr_background' => $request->input('qr_background', '#ffffff'),
            'show_logo' => $request->input('show_logo') === '1',
            'show_title' => $request->input('show_title') === '1',
            'show_description' => $request->input('show_description') === '1',
            'show_footer' => $request->input('show_footer') === '1',
            'bg_image' => $bgImage,
            'logo_data' => $logoData,
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
        
        if (!user_can('qr.create', $business)) {
            return redirect()->back()->with('error', 'You do not have permission to download QR codes.');
        }
        
        $format = $request->input('format', 'svg');
        
        if ($format === 'poster') {
            $bgImage = null;
            if ($request->hasFile('bg_image')) {
                $file = $request->file('bg_image');
                $bgImage = 'data:' . $file->getMimeType() . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
            }
            
            $logoData = null;
            if ($business->logo && file_exists(storage_path('app/public/' . $business->logo))) {
                $logoPath = storage_path('app/public/' . $business->logo);
                $logoMime = mime_content_type($logoPath);
                $logoData = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
            
            $options = [
                'bg_color' => $request->input('bg_color', '#f8f9fa'),
                'text_color' => $request->input('text_color', '#1a1a1a'),
                'title' => $request->input('title', $business->name),
                'description' => $request->input('description', ''),
                'footer' => $request->input('footer', ''),
                'qr_size' => $request->input('qr_size', 600),
                'qr_foreground' => $request->input('qr_foreground', '#000000'),
                'qr_background' => $request->input('qr_background', '#ffffff'),
                'show_logo' => $request->input('show_logo') === '1',
                'show_title' => $request->input('show_title') === '1',
                'show_description' => $request->input('show_description') === '1',
                'show_footer' => $request->input('show_footer') === '1',
                'bg_image' => $bgImage,
                'logo_data' => $logoData,
            ];
            
            $content = $this->qrCodeService->generatePoster($business, $options);
            $filename = "{$business->slug}-poster.pdf";
            $contentType = 'application/pdf';
        } elseif ($format === 'png') {
            $size = $request->input('size', 1000);
            $qrOptions = [
                'size' => $size,
                'foreground_color' => $request->input('foreground_color', '#000000'),
                'background_color' => $request->input('background_color', '#ffffff'),
                'margin' => $request->input('margin', 20),
            ];
            

            $options = [
                'qr_size' => $size,
                'qr_foreground' => $qrOptions['foreground_color'],
                'qr_background' => $qrOptions['background_color'],
                'bg_color' => '#ffffff',
                'text_color' => '#000000',
                'title' => '',
                'description' => '',
                'footer' => '',
                'show_logo' => false,
                'show_title' => false,
                'show_description' => false,
                'show_footer' => false,
                'bg_image' => null,
                'logo_data' => null,
            ];
            
            $content = $this->qrCodeService->generatePoster($business, $options);
            $filename = "{$business->slug}-qr-code.pdf";
            $contentType = 'application/pdf';
        } else {
            $size = $request->input('size', 1000);
            $options = [
                'size' => $size,
                'foreground_color' => $request->input('foreground_color', '#000000'),
                'background_color' => $request->input('background_color', '#ffffff'),
                'margin' => $request->input('margin', 20),
            ];
            
            $content = $this->qrCodeService->generateForBusiness($business, $options);
            $filename = "{$business->slug}-qr-code.svg";
            $contentType = 'image/svg+xml';
        }

        return response($content)
            ->header('Content-Type', $contentType)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
