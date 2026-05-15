<?php

namespace App\Controllers;

use App\Models\ProductModel;

class ProductController extends BaseController
{
    public function index()
    {
        $q = trim((string) $this->request->getGet('q'));
        $model = new ProductModel();

        if ($q !== '') {
            if (strlen($q) >= 3) {
                // Use FULLTEXT search for better performance (requires ft_products_search index)
                $escaped = $model->db->escape($q);
                $model->where("MATCH(name, description) AGAINST({$escaped} IN BOOLEAN MODE)", null, false);
            } else {
                // Fallback to LIKE for very short queries (below MySQL ft_min_word_len)
                $model->groupStart()
                    ->like('name', $q)
                    ->orLike('description', $q)
                    ->groupEnd();
            }
        }

        $products = $model->orderBy('created_at', 'DESC')->paginate(12);

        $response = view('products/index', [
            'title'    => 'Elite Collection',
            'products' => $products,
            'pager'    => $model->pager,
            'q'        => $q,
        ]);

        // Cache non-search listing pages for 5 minutes
        if ($q === '' && ENVIRONMENT === 'production') {
            $this->cachePage(300);
        }

        return $response;
    }

    public function show(int $id)
    {
        $product = (new ProductModel())->find($id);

        if (! $product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Product not found');
        }

        // Cache product detail pages for 10 minutes
        if (ENVIRONMENT === 'production') {
            $this->cachePage(600);
        }

        return view('products/show', [
            'title' => $product['name'],
            'product' => $product,
        ]);
    }
}
