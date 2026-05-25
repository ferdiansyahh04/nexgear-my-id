<?php

namespace App\Controllers;

/**
 * Legal pages controller.
 *
 * Each method renders a static legal document. Content is held in views
 * (not the controller) so non-developers can edit copy without PHP.
 *
 * Pages are intentionally cached for 1 hour in production — they change
 * infrequently and load order metadata that's expensive to recompute.
 */
class LegalController extends BaseController
{
    private const COMPANY_INFO = [
        'name'    => 'NexGear Store',
        'email'   => 'hello@nexgear.my.id',
        'city'    => 'Jakarta, Indonesia',
        'country' => 'Indonesia',
        'website' => 'https://nexgear.my.id',
    ];

    public function privacy()
    {
        if (ENVIRONMENT === 'production') $this->cachePage(3600);
        return view('legal/privacy', [
            'title'   => 'Privacy Policy',
            'company' => self::COMPANY_INFO,
            'updated' => '2026-05-25',
        ]);
    }

    public function terms()
    {
        if (ENVIRONMENT === 'production') $this->cachePage(3600);
        return view('legal/terms', [
            'title'   => 'Terms of Service',
            'company' => self::COMPANY_INFO,
            'updated' => '2026-05-25',
        ]);
    }

    public function refund()
    {
        if (ENVIRONMENT === 'production') $this->cachePage(3600);
        return view('legal/refund', [
            'title'   => 'Refund Policy',
            'company' => self::COMPANY_INFO,
            'updated' => '2026-05-25',
        ]);
    }

    public function shipping()
    {
        if (ENVIRONMENT === 'production') $this->cachePage(3600);
        return view('legal/shipping', [
            'title'   => 'Shipping Policy',
            'company' => self::COMPANY_INFO,
            'updated' => '2026-05-25',
        ]);
    }
}
