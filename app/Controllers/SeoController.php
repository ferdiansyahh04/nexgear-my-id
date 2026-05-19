<?php

namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\ProductModel;

/**
 * SEO endpoints — sitemap.xml and robots.txt served dynamically so they
 * always reflect the current product catalogue.
 */
class SeoController extends BaseController
{
    public function sitemap()
    {
        $base = rtrim(base_url('/'), '/');
        $now  = date('c');

        $urls = [];
        $urls[] = ['loc' => $base . '/',            'priority' => '1.0', 'changefreq' => 'daily'];
        $urls[] = ['loc' => $base . '/products',    'priority' => '0.9', 'changefreq' => 'daily'];
        $urls[] = ['loc' => $base . '/collection',  'priority' => '0.8', 'changefreq' => 'daily'];
        $urls[] = ['loc' => $base . '/login',       'priority' => '0.3', 'changefreq' => 'yearly'];
        $urls[] = ['loc' => $base . '/register',    'priority' => '0.3', 'changefreq' => 'yearly'];
        $urls[] = ['loc' => $base . '/contact',     'priority' => '0.4', 'changefreq' => 'monthly'];

        $categories = (new CategoryModel())->orderBy('sort_order', 'ASC')->findAll();
        foreach ($categories as $cat) {
            $urls[] = [
                'loc'        => $base . '/products?category=' . (int) $cat['id'],
                'priority'   => '0.7',
                'changefreq' => 'weekly',
            ];
        }

        $products = (new ProductModel())
            ->select('id, updated_at')
            ->orderBy('updated_at', 'DESC')
            ->findAll();
        foreach ($products as $p) {
            $urls[] = [
                'loc'        => $base . '/products/' . (int) $p['id'],
                'lastmod'    => ! empty($p['updated_at']) ? date('c', strtotime($p['updated_at'])) : $now,
                'priority'   => '0.6',
                'changefreq' => 'weekly',
            ];
        }

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
            if (! empty($u['lastmod'])) {
                $xml .= '    <lastmod>' . $u['lastmod'] . "</lastmod>\n";
            }
            $xml .= '    <changefreq>' . $u['changefreq'] . "</changefreq>\n";
            $xml .= '    <priority>' . $u['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        return $this->response
            ->setHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->setHeader('Cache-Control', 'public, max-age=3600')
            ->setBody($xml);
    }

    public function robots()
    {
        $base = rtrim(base_url('/'), '/');

        $body  = "User-agent: *\n";
        $body .= "Allow: /\n";
        $body .= "Disallow: /admin\n";
        $body .= "Disallow: /admin/\n";
        $body .= "Disallow: /account\n";
        $body .= "Disallow: /cart\n";
        $body .= "Disallow: /checkout\n";
        $body .= "Disallow: /coupon\n";
        $body .= "\n";
        $body .= "Sitemap: {$base}/sitemap.xml\n";

        return $this->response
            ->setHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->setHeader('Cache-Control', 'public, max-age=86400')
            ->setBody($body);
    }
}
