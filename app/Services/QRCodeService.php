<?php

namespace App\Services;

use App\Models\Business;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage;

class QRCodeService
{
    /**
     * Generate QR code for a business
     */
    public function generateForBusiness(Business $business): string
    {
        $feedbackUrl = route('feedback.submit', ['business' => $business->slug]);
        
        $renderer = new ImageRenderer(
            new RendererStyle(300, 0),
            new SvgImageBackEnd()
        );
        
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($feedbackUrl);
        
        // Store SVG file
        $filename = "qr-codes/{$business->slug}.svg";
        Storage::disk('public')->put($filename, $qrCodeSvg);
        
        // Update business with QR code details
        $business->update([
            'qr_code_path' => $filename,
            'qr_code_url' => asset('storage/'.$filename),
        ]);
        
        return $qrCodeSvg;
    }

    /**
     * Generate customized QR code with options
     */
    public function generateCustom(Business $business, array $options = []): string
    {
        $feedbackUrl = route('feedback.submit', ['business' => $business->slug]);
        
        $size = $options['size'] ?? 300;
        
        $renderer = new ImageRenderer(
            new RendererStyle($size, 0),
            new SvgImageBackEnd()
        );
        
        $writer = new Writer($renderer);
        
        return $writer->writeString($feedbackUrl);
    }

    /**
     * Get QR code URL for a business
     */
    public function getUrl(Business $business): string
    {
        if (! $business->qr_code_path) {
            $this->generateForBusiness($business);
        }
        
        return $business->qr_code_url ?? asset('storage/'.$business->qr_code_path);
    }

    /**
     * Regenerate QR code for a business
     */
    public function regenerate(Business $business): string
    {
        // Delete old QR code if exists
        if ($business->qr_code_path) {
            Storage::disk('public')->delete($business->qr_code_path);
        }
        
        return $this->generateForBusiness($business);
    }
}
