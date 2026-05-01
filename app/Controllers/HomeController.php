<?php

namespace App\Controllers;

use App\Models\ProductModel;

class HomeController extends BaseController
{
    public function index()
    {
        $products = (new ProductModel())
            ->orderBy('created_at', 'DESC')
            ->limit(6)
            ->find();

        return view('home', [
            'title' => 'Hypernex Store',
            'products' => $products,
        ]);
    }
}
