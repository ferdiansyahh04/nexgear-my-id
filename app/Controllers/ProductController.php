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
            $model->groupStart()
                ->like('name', $q)
                ->orLike('description', $q)
                ->groupEnd();
        }

        return view('products/index', [
            'title'    => 'Elite Collection',
            'products' => $model->orderBy('created_at', 'DESC')->findAll(),
            'q'        => $q,
        ]);
    }

    public function show(int $id)
    {
        $product = (new ProductModel())->find($id);

        if (! $product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Product not found');
        }

        return view('products/show', [
            'title' => $product['name'],
            'product' => $product,
        ]);
    }
}
