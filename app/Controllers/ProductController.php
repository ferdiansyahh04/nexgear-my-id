<?php

namespace App\Controllers;

use App\Libraries\RecentlyViewedService;
use App\Libraries\RecommendationService;
use App\Models\CategoryModel;
use App\Models\ProductImageModel;
use App\Models\ProductModel;
use App\Models\ReviewModel;
use App\Models\SearchLogModel;

class ProductController extends BaseController
{
    public function index()
    {
        $q          = trim((string) $this->request->getGet('q'));
        $sort       = (string) $this->request->getGet('sort');
        $minPrice   = $this->request->getGet('min_price');
        $maxPrice   = $this->request->getGet('max_price');
        $stockMode  = (string) $this->request->getGet('stock');
        $categoryId = (int) $this->request->getGet('category');

        $model = new ProductModel();

        if ($q !== '') {
            $this->applySearch($model, $q);
        }

        if ($categoryId > 0) {
            $model->where('category_id', $categoryId);
        }

        if ($minPrice !== null && $minPrice !== '') {
            $model->where('price >=', (float) $minPrice);
        }
        if ($maxPrice !== null && $maxPrice !== '') {
            $model->where('price <=', (float) $maxPrice);
        }

        if ($stockMode === 'in') {
            $model->where('stock >', 0);
        } elseif ($stockMode === 'low') {
            $model->where('stock >', 0)->where('stock <=', 5);
        } elseif ($stockMode === 'out') {
            $model->where('stock', 0);
        }

        // Sorting
        $sortMap = [
            'newest'     => ['created_at', 'DESC'],
            'oldest'     => ['created_at', 'ASC'],
            'price_asc'  => ['price', 'ASC'],
            'price_desc' => ['price', 'DESC'],
            'name_asc'   => ['name', 'ASC'],
        ];
        $sortKey = array_key_exists($sort, $sortMap) ? $sort : 'newest';
        [$sortCol, $sortDir] = $sortMap[$sortKey];
        $model->orderBy($sortCol, $sortDir);

        $products = $model->paginate(12);

        $categories = (new CategoryModel())->orderBy('sort_order', 'ASC')->orderBy('name', 'ASC')->findAll();

        $filters = [
            'q'         => $q,
            'sort'      => $sortKey,
            'min_price' => $minPrice !== null && $minPrice !== '' ? (float) $minPrice : null,
            'max_price' => $maxPrice !== null && $maxPrice !== '' ? (float) $maxPrice : null,
            'stock'     => in_array($stockMode, ['in', 'low', 'out'], true) ? $stockMode : '',
            'category'  => $categoryId > 0 ? $categoryId : 0,
        ];

        // AJAX request → return JSON with rendered partials
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'   => 'success',
                'count'    => $model->pager->getTotal(),
                'page'     => $model->pager->getCurrentPage(),
                'pages'    => $model->pager->getPageCount(),
                'filters'  => $filters,
                'gridHtml' => view('products/_grid', [
                    'products' => $products,
                ]),
                'pagerHtml' => $model->pager->getPageCount() > 1
                    ? $model->pager->links('default', 'nexgear')
                    : '',
            ]);
        }

        $response = view('products/index', [
            'title'      => 'Elite Collection',
            'products'   => $products,
            'pager'      => $model->pager,
            'q'          => $q,
            'filters'    => $filters,
            'categories' => $categories,
        ]);

        // NOTE: We previously called $this->cachePage(300) for the unfiltered
        // listing. The CodeIgniter ResponseCache keys responses purely by
        // method + URI, *not* by request headers — so a cached HTML response
        // for `GET /collection` would be served back to a subsequent AJAX
        // request to the same URL, bypassing the AJAX→JSON branch above.
        // The result on production was an HTML body returned for an AJAX
        // request → JSON.parse() throws → "Network error while filtering"
        // toast on every category-chip click after an unfiltered pageload.
        // Storefront listings are cheap (one paginated query + view render),
        // and Cloudflare already proxies the public unfiltered URL well
        // enough that a server-side page cache adds little value. Removing
        // it eliminates the AJAX/HTML conflation entirely.

        return $response;
    }

    public function show(int $id)
    {
        $product = (new ProductModel())->find($id);

        if (! $product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Product not found');
        }

        // Track this view BEFORE caching consideration so the strip stays fresh
        (new RecentlyViewedService())->track($id);

        // Cache product detail pages for 10 minutes. Safe here because the
        // companion AJAX endpoints (/products/{id}/stock, /products/{id}/quick-view)
        // live on different URLs, so the URL-only cache key can't conflate them.
        if (ENVIRONMENT === 'production') {
            $this->cachePage(600);
        }

        // Gallery: extra images from product_images, ordered by sort_order
        $extraImages = (new ProductImageModel())
            ->where('product_id', $id)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        // Category breadcrumb info
        $category = null;
        if (! empty($product['category_id'])) {
            $category = (new CategoryModel())->find((int) $product['category_id']);
        }

        // Reviews aggregate + recent reviews
        $reviewModel = new ReviewModel();
        $aggregate   = $reviewModel->aggregate($id);
        $reviews     = $reviewModel
            ->select('reviews.*, users.name AS author_name')
            ->join('users', 'users.id = reviews.user_id', 'left')
            ->where('reviews.product_id', $id)
            ->orderBy('reviews.created_at', 'DESC')
            ->limit(20)
            ->find();

        // Eligibility: user must have a delivered/checked-out order containing this product,
        // and must not have already reviewed it.
        $canReview = false;
        $userReview = null;
        if (session('is_logged_in')) {
            $userId = (int) session('user_id');
            $userReview = $reviewModel->where(['user_id' => $userId, 'product_id' => $id])->first();

            if (! $userReview) {
                $hasOrdered = (bool) db_connect()
                    ->table('cart_items')
                    ->join('cart', 'cart.id = cart_items.cart_id')
                    ->where('cart.user_id', $userId)
                    ->where('cart_items.product_id', $id)
                    ->whereIn('cart.status', ['checked_out', 'paid', 'processing', 'shipped', 'delivered'])
                    ->countAllResults();
                $canReview = $hasOrdered;
            }
        }

        return view('products/show', [
            'title'           => $product['name'],
            'product'         => $product,
            'extraImages'     => $extraImages,
            'category'        => $category,
            'aggregate'       => $aggregate,
            'reviews'         => $reviews,
            'canReview'       => $canReview,
            'userReview'      => $userReview,
            'recommendations' => (new RecommendationService())->forProduct($id, 4),
        ]);
    }

    /**
     * AJAX-only — minimal stock snapshot for the live counter on the detail page.
     */
    public function stockSnapshot(int $id)
    {
        if (! $this->request->isAJAX()) {
            return redirect()->to('/products/' . $id);
        }

        $product = (new ProductModel())->select('id, stock')->find($id);
        if (! $product) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Product not found.',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'id'     => (int) $product['id'],
            'stock'  => (int) $product['stock'],
        ]);
    }

    /**
     * Side-by-side product comparison.
     * Reads ids from ?ids=1,2,3 (max 3, deduped, integer-only).
     */
    public function compare()
    {
        $raw = trim((string) $this->request->getGet('ids'));
        $ids = [];
        if ($raw !== '') {
            $ids = array_values(array_unique(array_filter(
                array_map('intval', explode(',', $raw)),
                static fn ($id) => $id > 0
            )));
            $ids = array_slice($ids, 0, 3);
        }

        $products = [];
        if ($ids !== []) {
            $rows = (new ProductModel())->whereIn('id', $ids)->findAll();
            $byId = [];
            foreach ($rows as $row) {
                $byId[(int) $row['id']] = $row;
            }
            foreach ($ids as $id) {
                if (isset($byId[$id])) $products[] = $byId[$id];
            }
        }

        // Build comparison rows: derive from each product's data
        $rows = [];
        if ($products !== []) {
            $rows = [
                'Price'        => array_map(fn ($p) => 'Rp ' . number_format((float) $p['price'], 0, ',', '.'), $products),
                'Stock'        => array_map(fn ($p) => (int) $p['stock'] > 0 ? $p['stock'] . ' units' : 'Sold out', $products),
                'Availability' => array_map(fn ($p) => (int) $p['stock'] > 0 ? 'Available' : 'Unavailable', $products),
                'Description'  => array_map(fn ($p) => mb_strimwidth((string) $p['description'], 0, 200, '…'), $products),
                'Added'        => array_map(fn ($p) => date('M d, Y', strtotime($p['created_at'] ?? 'now')), $products),
            ];
        }

        return view('products/compare', [
            'title'    => 'Compare Products',
            'products' => $products,
            'rows'     => $rows,
            'ids'      => $ids,
        ]);
    }

    /**
     * AJAX-only — returns the quick-view partial as JSON.
     * Used by the modal on product cards across the storefront.
     */
    public function quickView(int $id)
    {
        if (! $this->request->isAJAX()) {
            return redirect()->to('/products/' . $id);
        }

        $product = (new ProductModel())->find($id);

        if (! $product) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Product not found.',
            ]);
        }

        $html = view('products/_quick_view', ['product' => $product]);

        return $this->response->setJSON([
            'status'    => 'success',
            'product'   => [
                'id'    => (int) $product['id'],
                'name'  => $product['name'],
                'price' => (float) $product['price'],
            ],
            'html'      => $html,
            'csrfName'  => csrf_token(),
            'csrfToken' => csrf_hash(),
        ]);
    }

    /**
     * AJAX live search — returns JSON suggestions (max 8).
     * Driven by the nav search overlay.
     */
    public function search()
    {
        if (! $this->request->isAJAX()) {
            return redirect()->to('/products?q=' . urlencode((string) $this->request->getGet('q')));
        }

        $q = trim((string) $this->request->getGet('q'));
        $logModel = new SearchLogModel();

        // No query → return trending searches as suggestions
        if ($q === '' || mb_strlen($q) < 2) {
            return $this->response->setJSON([
                'status'   => 'success',
                'query'    => $q,
                'results'  => [],
                'trending' => $logModel->trending(6),
            ]);
        }

        $model = new ProductModel();

        if (mb_strlen($q) >= 2) {
            $this->applySearch($model, $q);
        } else {
            // Treat single-char as no-op so we don't return the entire catalogue.
            return $this->response->setJSON([
                'status'   => 'success',
                'query'    => $q,
                'results'  => [],
                'trending' => $logModel->trending(6),
            ]);
        }

        $rows = $model->orderBy('stock', 'DESC')->limit(8)->find();

        // Record the query (best-effort)
        try { $logModel->record($q); } catch (\Throwable $e) { /* ignore */ }

        $results = array_map(static function (array $row) {
            $img = $row['image'] ?: 'default-product.svg';

            return [
                'id'    => (int) $row['id'],
                'name'  => $row['name'],
                'price' => (float) $row['price'],
                'stock' => (int) $row['stock'],
                'image' => base_url('uploads/products/' . rawurlencode($img)),
                'url'   => base_url('/products/' . (int) $row['id']),
            ];
        }, $rows);

        return $this->response->setJSON([
            'status'   => 'success',
            'query'    => $q,
            'count'    => count($results),
            'results'  => $results,
            'trending' => [],
        ]);
    }

    /**
     * Apply a robust storefront search to a ProductModel query builder.
     *
     * Strategy:
     *  • Tokenize input on whitespace.
     *  • For each token ≥3 chars with ASCII word characters only, use a
     *    BOOLEAN MODE FULLTEXT clause with a `+token*` prefix wildcard so
     *    "icar" matches "Icarus" and "PAW33" matches "PAW3395".
     *  • For short tokens (<3) or tokens with hyphens / non-word characters,
     *    fall back to LIKE on name + description.
     *  • The combined per-token clauses are AND-ed together so the result set
     *    is intersected (every keyword must appear).
     *
     * Falls back gracefully when the FULLTEXT index is missing — the LIKE
     * branch alone still produces correct (if slower) results.
     */
    private function applySearch(ProductModel $model, string $q): void
    {
        $tokens = preg_split('/\s+/u', $q, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if ($tokens === []) {
            return;
        }

        $db = $model->db;

        foreach ($tokens as $token) {
            $clean = trim($token);
            if ($clean === '') {
                continue;
            }

            $isFulltextSafe = mb_strlen($clean) >= 3
                && preg_match('/^[A-Za-z0-9]+$/', $clean) === 1;

            $model->groupStart();

            if ($isFulltextSafe) {
                // Prefix wildcard so partial typed words still match.
                $escaped = $db->escape('+' . $clean . '*');
                $model->where(
                    "MATCH(name, description) AGAINST({$escaped} IN BOOLEAN MODE)",
                    null,
                    false
                );
                // OR fall through to LIKE in case FULLTEXT is unavailable
                // (e.g., MyISAM-only indexes on legacy MariaDB) — guarantees
                // the row still surfaces.
                $model->orLike('name', $clean);
                $model->orLike('description', $clean);
            } else {
                // Hyphenated SKUs, model numbers with mixed punctuation, or
                // sub-3-character tokens — LIKE is the safer fallback.
                $model->like('name', $clean);
                $model->orLike('description', $clean);
            }

            $model->groupEnd();
        }
    }
}
