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
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Barryvdh\DomPDF\Facade\Pdf;

class QRCodeService
{
    public function generateForBusiness(Business $business, array $options = []): string
    {
        $feedbackUrl = route('feedback.submit', ['business' => $business->slug]);
        
        $size = $options['size'] ?? 300;
        $margin = $options['margin'] ?? 0;
        
        $foregroundColor = $this->parseColor($options['foreground_color'] ?? '#000000');
        $backgroundColor = $this->parseColor($options['background_color'] ?? '#ffffff');
        
        $renderer = new ImageRenderer(
            new RendererStyle($size, $margin, null, null, Fill::uniformColor($backgroundColor, $foregroundColor)),
            new SvgImageBackEnd()
        );
        
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($feedbackUrl);
        
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

    public function generatePng(Business $business, array $options = []): string
    {
        $svg = $this->generateForBusiness($business, $options);
        
        $manager = new ImageManager(new Driver());
        $image = $manager->read($svg);
        
        return $image->toPng()->toString();
    }

    public function generatePoster(Business $business, array $options = []): string
    {
        $qrSize = $options['qr_size'] ?? 600;
        
        $qrOptions = [
            'size' => $qrSize,
            'foreground_color' => $options['qr_foreground'] ?? '#000000',
            'background_color' => $options['qr_background'] ?? '#ffffff',
            'margin' => 0,
        ];
        $qrSvg = $this->generateForBusiness($business, $qrOptions);
        $qrSvg = preg_replace('/<\?xml[^?]*\?>\s*/', '', $qrSvg);
        
        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);
        
        $data = [
            'bgColor' => $options['bg_color'] ?? '#f8f9fa',
            'textColor' => $options['text_color'] ?? '#1a1a1a',
            'title' => $options['title'] ?? $business->name,
            'description' => $options['description'] ?? '',
            'footer' => $options['footer'] ?? '',
            'qrSvg' => $qrBase64,
            'qrSize' => $qrSize,
            'showLogo' => $options['show_logo'] ?? true,
            'showTitle' => $options['show_title'] ?? true,
            'showDescription' => $options['show_description'] ?? true,
            'showFooter' => $options['show_footer'] ?? true,
            'bgImage' => $options['bg_image'] ?? null,
            'logoData' => $options['logo_data'] ?? null,
        ];
        
        $html = view('posters.modern', $data)->render();
        
        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('margin_left', 0)
            ->setOption('margin_right', 0)
            ->setOption('margin_top', 0)
            ->setOption('margin_bottom', 0);
        
        return $pdf->output();
    }

    protected function getPdfFormat(string $size): array|string
    {
        return match ($size) {
            'a4' => 'A4',
            'a5' => 'A5',
            'letter' => 'Letter',
            'square' => [210, 210],
            'instagram' => [90, 160],
            default => 'A4',
        };
    }

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

    public function getUrl(Business $business): string
    {
        if (! $business->qr_code_path) {
            $this->generateForBusiness($business, ['store' => true]);
            $business->refresh();
        }
        
        return $business->qr_code_url ?? asset('storage/'.$business->qr_code_path);
    }

    public function getPreview(Business $business, array $options = []): string
    {
        return $this->generateForBusiness($business, $options);
    }
}
