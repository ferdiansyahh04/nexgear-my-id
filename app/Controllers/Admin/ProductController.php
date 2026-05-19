<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\AuditLogService;
use App\Models\CategoryModel;
use App\Models\ProductImageModel;
use App\Models\ProductModel;

class ProductController extends BaseController
{
    public function index()
    {
        return view('admin/products/index', [
            'title'    => 'Admin Products',
            'products' => (new ProductModel())->orderBy('created_at', 'DESC')->findAll(),
        ]);
    }

    public function create()
    {
        return view('admin/products/form', [
            'title'      => 'Create Product',
            'product'    => null,
            'action'     => site_url('admin/products'),
            'categories' => $this->categories(),
            'extraImages' => [],
        ]);
    }

    public function store()
    {
        $data = $this->validatedData();
        if ($data === null) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data['image']           = $this->handleUpload('image');
        $data['image_secondary'] = $this->handleUpload('image_secondary', false);

        $id = (new ProductModel())->insert($data, true);

        (new AuditLogService())->log('product.create', [
            'target_type' => 'product',
            'target_id'   => (int) $id,
            'meta'        => ['name' => $data['name'], 'price' => $data['price']],
        ]);

        return redirect()->to('/admin/products/' . (int) $id . '/edit')
            ->with('success', 'Product created. You can now add gallery images.');
    }

    public function edit(int $id)
    {
        $product = (new ProductModel())->find($id);
        if (! $product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Product not found');
        }

        $extraImages = (new ProductImageModel())
            ->where('product_id', $id)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        return view('admin/products/form', [
            'title'       => 'Edit Product',
            'product'     => $product,
            'action'      => site_url('admin/products/' . $id),
            'categories'  => $this->categories(),
            'extraImages' => $extraImages,
        ]);
    }

    public function update(int $id)
    {
        $model   = new ProductModel();
        $product = $model->find($id);
        if (! $product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Product not found');
        }

        $data = $this->validatedData();
        if ($data === null) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $image = $this->handleUpload('image', false);
        if ($image !== null) {
            $data['image'] = $image;
            $this->deleteImageFile($product['image']);
        }

        $imageSecondary = $this->handleUpload('image_secondary', false);
        if ($imageSecondary !== null) {
            $data['image_secondary'] = $imageSecondary;
            $this->deleteImageFile($product['image_secondary']);
        }

        $oldStock = (int) $product['stock'];
        $model->update($id, $data);

        (new AuditLogService())->log('product.update', [
            'target_type' => 'product',
            'target_id'   => $id,
            'meta'        => ['name' => $data['name'], 'price' => $data['price'], 'stock' => $data['stock']],
        ]);

        // B8 — When a product transitions out of stock → in stock, dispatch
        // pending stock alerts so subscribed users get notified.
        if ($oldStock <= 0 && (int) ($data['stock'] ?? 0) > 0) {
            (new \App\Libraries\StockAlertService())->dispatchFor($id);
        }

        return redirect()->to('/admin/products')->with('success', 'Product updated.');
    }

    public function delete(int $id)
    {
        $model = new ProductModel();
        $product = $model->find($id);

        if ($product) {
            $this->deleteImageFile($product['image']);
            $this->deleteImageFile($product['image_secondary']);

            // Delete extra gallery files too
            $extras = (new ProductImageModel())->where('product_id', $id)->findAll();
            foreach ($extras as $img) $this->deleteImageFile($img['path']);

            $model->delete($id); // FK CASCADE removes product_images rows

            (new AuditLogService())->log('product.delete', [
                'target_type' => 'product',
                'target_id'   => $id,
                'meta'        => ['name' => $product['name']],
            ]);
        }

        return redirect()->to('/admin/products')->with('success', 'Product deleted.');
    }

    /**
     * B6 — Add an image to a product's gallery.
     */
    public function addImage(int $productId)
    {
        $product = (new ProductModel())->find($productId);
        if (! $product) return redirect()->to('/admin/products');

        $rules = [
            'gallery_image' => 'uploaded[gallery_image]|is_image[gallery_image]|mime_in[gallery_image,image/jpg,image/jpeg,image/png,image/webp]|max_size[gallery_image,2048]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $name = $this->handleUpload('gallery_image', false);
        if ($name === null) {
            return redirect()->back()->with('error', 'Could not upload image.');
        }

        $imageModel = new ProductImageModel();
        $maxOrder = (int) ($imageModel->selectMax('sort_order')->where('product_id', $productId)->first()['sort_order'] ?? 0);

        $imageModel->insert([
            'product_id' => $productId,
            'path'       => $name,
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->to('/admin/products/' . $productId . '/edit')->with('success', 'Image added to gallery.');
    }

    public function deleteImage(int $productId, int $imageId)
    {
        $imageModel = new ProductImageModel();
        $img = $imageModel->find($imageId);
        if ($img && (int) $img['product_id'] === $productId) {
            $this->deleteImageFile($img['path']);
            $imageModel->delete($imageId);
        }
        return redirect()->to('/admin/products/' . $productId . '/edit')->with('success', 'Image removed.');
    }

    private function categories(): array
    {
        return (new CategoryModel())->orderBy('sort_order', 'ASC')->orderBy('name', 'ASC')->findAll();
    }

    private function validatedData(): ?array
    {
        $rules = [
            'name'            => 'required|min_length[3]|max_length[160]',
            'description'     => 'permit_empty|max_length[1200]',
            'category_id'     => 'permit_empty|integer',
            'price'           => 'required|numeric|greater_than_equal_to[0]',
            'stock'           => 'required|integer|greater_than_equal_to[0]',
            'image'           => 'permit_empty|is_image[image]|mime_in[image,image/jpg,image/jpeg,image/png,image/webp]|max_size[image,2048]',
            'image_secondary' => 'permit_empty|is_image[image_secondary]|mime_in[image_secondary,image/jpg,image/jpeg,image/png,image/webp]|max_size[image_secondary,2048]',
        ];

        if (! $this->validate($rules)) {
            return null;
        }

        $catId = (int) $this->request->getPost('category_id');

        return [
            'name'        => trim((string) $this->request->getPost('name')),
            'description' => trim((string) $this->request->getPost('description')),
            'category_id' => $catId > 0 ? $catId : null,
            'price'       => (float) $this->request->getPost('price'),
            'stock'       => (int) $this->request->getPost('stock'),
        ];
    }

    private function handleUpload(string $fieldName, bool $useDefault = true): ?string
    {
        $file = $this->request->getFile($fieldName);

        if (! $file || ! $file->isValid()) {
            return $useDefault ? 'default-product.svg' : null;
        }

        $name = $file->getRandomName();
        $file->move(FCPATH . 'uploads/products', $name);

        return $name;
    }

    private function deleteImageFile(?string $image): void
    {
        if (! $image || $image === 'default-product.svg') return;
        $path = FCPATH . 'uploads/products/' . $image;
        if (is_file($path)) unlink($path);
    }
}
