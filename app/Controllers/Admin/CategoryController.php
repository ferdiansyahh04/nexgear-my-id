<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CategoryModel;

class CategoryController extends BaseController
{
    public function index()
    {
        $categories = (new CategoryModel())
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();

        // Count products per category
        $db = db_connect();
        $counts = $db->table('products')
            ->select('category_id, COUNT(*) AS total')
            ->groupBy('category_id')
            ->get()
            ->getResultArray();
        $countById = [];
        foreach ($counts as $c) $countById[(int) $c['category_id']] = (int) $c['total'];

        foreach ($categories as &$cat) {
            $cat['product_count'] = $countById[(int) $cat['id']] ?? 0;
        }
        unset($cat);

        return view('admin/categories/index', [
            'title'      => 'Categories',
            'categories' => $categories,
        ]);
    }

    public function store()
    {
        $rules = [
            'name' => 'required|min_length[2]|max_length[80]',
            'slug' => 'permit_empty|alpha_dash|max_length[100]',
            'sort_order' => 'permit_empty|integer',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $name = trim((string) $this->request->getPost('name'));
        $slug = $this->slugify(trim((string) $this->request->getPost('slug')) ?: $name);

        // Ensure slug uniqueness
        $model = new CategoryModel();
        $base = $slug;
        $i = 2;
        while ($model->where('slug', $slug)->countAllResults() > 0) {
            $slug = $base . '-' . $i++;
        }

        $model->insert([
            'name'        => $name,
            'slug'        => $slug,
            'description' => trim((string) $this->request->getPost('description')) ?: null,
            'sort_order'  => (int) $this->request->getPost('sort_order'),
        ]);

        return redirect()->to('/admin/categories')->with('success', 'Category created.');
    }

    public function update(int $id)
    {
        $model    = new CategoryModel();
        $existing = $model->find($id);
        if (! $existing) {
            return redirect()->to('/admin/categories')->with('error', 'Category not found.');
        }

        $rules = [
            'name' => 'required|min_length[2]|max_length[80]',
            'slug' => 'permit_empty|alpha_dash|max_length[100]',
            'sort_order' => 'permit_empty|integer',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $name = trim((string) $this->request->getPost('name'));
        $slug = $this->slugify(trim((string) $this->request->getPost('slug')) ?: $name);

        // Ensure slug uniqueness against OTHER rows
        $base = $slug;
        $i = 2;
        while ($model->where('slug', $slug)->where('id !=', $id)->countAllResults() > 0) {
            $slug = $base . '-' . $i++;
        }

        $model->update($id, [
            'name'        => $name,
            'slug'        => $slug,
            'description' => trim((string) $this->request->getPost('description')) ?: null,
            'sort_order'  => (int) $this->request->getPost('sort_order'),
        ]);

        return redirect()->to('/admin/categories')->with('success', 'Category updated.');
    }

    public function delete(int $id)
    {
        (new CategoryModel())->delete($id);
        return redirect()->to('/admin/categories')->with('success', 'Category removed.');
    }

    private function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value);
        return trim((string) $value, '-') ?: 'category';
    }
}
