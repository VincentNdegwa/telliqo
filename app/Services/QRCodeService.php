<?php

namespace App\Services;

use App\Models\Business;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage;

class QRCodeService
{
    /**
     * Generate QR code for a business with customization
     */
    public function generateForBusiness(Business $business, array $options = []): string
    {
        $feedbackUrl = route('feedback.submit', ['business' => $business->slug]);
        
        $size = $options['size'] ?? 300;
        $margin = $options['margin'] ?? 0;
        
        // Get colors from options or business branding
        $foregroundColor = $this->parseColor($options['foreground_color'] ?? '#000000');
        $backgroundColor = $this->parseColor($options['background_color'] ?? '#ffffff');
        
        $renderer = new ImageRenderer(
            new RendererStyle($size, $margin, null, null, Fill::uniformColor($backgroundColor, $foregroundColor)),
            new SvgImageBackEnd()
        );
        
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($feedbackUrl);
        
        // Store SVG file if storing
        if ($options['store'] ?? false) {
            $filename = "qr-codes/{$business->slug}.svg";
            Storage::disk('public')->put($filename, $qrCodeSvg);
            
            $business->update([
                'qr_code_path' => $filename,
                'qr_code_url' => asset('storage/'.$filename),
            ]);
        }
        
        return $qrCodeSvg;
    }

    /**
     * Generate PNG QR code (placeholder - requires Intervention Image)
     */
    public function generatePng(Business $business, array $options = []): string
    {
        // For now, return SVG
        // TODO: Install intervention/image package to convert SVG to PNG
        return $this->generateForBusiness($business, $options);
    }

    /**
     * Generate poster with QR code (placeholder - requires Intervention Image)  
     */
    public function generatePoster(Business $business, array $options = []): string
    {
        // For now, return large QR code SVG
        // TODO: Install intervention/image package for full poster generation
        $options['size'] = 1000;
        
        return $this->generateForBusiness($business, $options);
    }


    /**
     * Get poster dimensions based on size (for future use)
     */
    protected function getPosterDimensions(string $size): array
    {
        return match ($size) {
            'a4' => [2480, 3508], // 210mm x 297mm at 300 DPI
            'a5' => [1748, 2480], // 148mm x 210mm at 300 DPI
            'letter' => [2550, 3300], // 8.5" x 11" at 300 DPI
            'square' => [3000, 3000], // Square format
            'instagram' => [1080, 1920], // Instagram story
            default => [2480, 3508],
        };
    }

    /**
     * Parse color string to RGB
     */
    protected function parseColor(string $color): Rgb
    {
        $color = ltrim($color, '#');
        
        if (strlen($color) === 3) {
            $color = $color[0].$color[0].$color[1].$color[1].$color[2].$color[2];
        }
        
        return new Rgb(
            hexdec(substr($color, 0, 2)),
            hexdec(substr($color, 2, 2)),
            hexdec(substr($color, 4, 2))
        );
    }

    /**
     * Parse color to hex
     */
    protected function parseColorToHex(string $color): string
    {
        return str_starts_with($color, '#') ? $color : '#'.$color;
    }

    /**
     * Get QR code URL for a business
     */
    public function getUrl(Business $business): string
    {
        if (! $business->qr_code_path) {
            $this->generateForBusiness($business, ['store' => true]);
            $business->refresh();
        }
        
        return $business->qr_code_url ?? asset('storage/'.$business->qr_code_path);
    }

    /**
     * Get preview SVG
     */
    public function getPreview(Business $business, array $options = []): string
    {
        return $this->generateForBusiness($business, $options);
    }
}
