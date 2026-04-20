<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $groupId    = session('active_family_group_id');
        $categories = Category::where('is_system', true)
            ->orWhere('family_group_id', $groupId)
            ->orderBy('is_system', 'desc')
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(StoreCategoryRequest $request)
    {
        $groupId = session('active_family_group_id');

        Category::create([
            ...$request->validated(),
            'family_group_id' => $groupId,
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    public function edit(Category $category)
    {
        $this->authorizeCategory($category);

        return view('categories.edit', compact('category'));
    }

    public function update(StoreCategoryRequest $request, Category $category)
    {
        $this->authorizeCategory($category);

        $category->update($request->validated());

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoría actualizada.');
    }

    public function destroy(Category $category)
    {
        $this->authorizeCategory($category);

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoría eliminada.');
    }

    private function authorizeCategory(Category $category): void
    {
        abort_if(
            $category->is_system || $category->family_group_id !== session('active_family_group_id'),
            403,
            'No tenés permiso para modificar esta categoría.'
        );
    }
}
