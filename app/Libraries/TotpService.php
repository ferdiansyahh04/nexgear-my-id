<?php

namespace App\Libraries;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use RobThree\Auth\TwoFactorAuth;

/**
 * Wrapper around RobThree\TwoFactorAuth + bacon QR code so the controller
 * stays slim. Returns an inline SVG data URI for the QR — no temp files.
 */
class TotpService
{
    private TwoFactorAuth $tfa;

    public function __construct()
    {
        $this->tfa = new TwoFactorAuth(issuer: 'NexGear Store');
    }

    public function newSecret(int $bits = 160): string
    {
        return $this->tfa->createSecret($bits);
    }

    public function verify(string $secret, string $code): bool
    {
        $code = preg_replace('/\D+/', '', $code);
        if ($code === '' || strlen($code) !== 6) return false;
        return $this->tfa->verifyCode($secret, $code, 2);
    }

    /**
     * Build an otpauth:// URI then render it as an inline SVG data URI.
     */
    public function qrDataUri(string $email, string $secret, int $size = 220): string
    {
        $uri = $this->tfa->getQRText($email, $secret);
        $renderer = new ImageRenderer(
            new RendererStyle($size, 1),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $svg = $writer->writeString($uri);
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    public function otpauthUri(string $email, string $secret): string
    {
        return $this->tfa->getQRText($email, $secret);
    }
}
