<?php
// app/Http/Controllers/CategoryController.php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\AuditService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->middleware('auth');
        $this->middleware('permission:manage_categories');
        $this->auditService = $auditService;
    }

    public function index(Request $request)
    {
        $query = Category::with(['parent', 'branch']);

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->has('branch_id')) {
            $query->forBranch($request->get('branch_id'));
        }

        if ($request->has('parent_id')) {
            if ($request->get('parent_id') === 'null') {
                $query->rootCategories();
            } else {
                $query->where('parent_id', $request->get('parent_id'));
            }
        }

        $categories = $query->withCount('products')
                            ->orderBy('sort_order')
                            ->paginate(20);

        $rootCategories = Category::rootCategories()
                                  ->active()
                                  ->get();

        return view('categories.index', compact('categories', 'rootCategories'));
    }

    public function create()
    {
        $parentCategories = Category::rootCategories()
                                   ->active()
                                   ->orderBy('name')
                                   ->get();

        $branches = \App\Models\Branch::active()
                                      ->orderBy('name')
                                      ->get();

        return view('categories.create', compact('parentCategories', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'description' => 'nullable|string|max:1000',
            'parent_id' => 'nullable|exists:categories,id',
            'branch_id' => 'nullable|exists:branches,id',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $category = Category::create($request->all());

        $this->auditService->log('CREATE', 'categories', $category->id, null, $category->toArray());

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully');
    }

    public function show(Category $category)
    {
        $category->load(['parent', 'children', 'branch', 'products']);
        
        return view('categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        $parentCategories = Category::rootCategories()
                                   ->active()
                                   ->where('id', '!=', $category->id)
                                   ->orderBy('name')
                                   ->get();

        $branches = \App\Models\Branch::active()
                                      ->orderBy('name')
                                      ->get();

        return view('categories.edit', compact('category', 'parentCategories', 'branches'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string|max:1000',
            'parent_id' => 'nullable|exists:categories,id',
            'branch_id' => 'nullable|exists:branches,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Prevent setting itself as parent
        if ($request->parent_id == $category->id) {
            return redirect()->back()
                ->withErrors(['parent_id' => 'A category cannot be its own parent.'])
                ->withInput();
        }

        $oldValues = $category->toArray();
        $category->update($request->all());

        $this->auditService->log('UPDATE', 'categories', $category->id, $oldValues, $category->toArray());

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully');
    }

    public function destroy(Category $category)
    {
        // Check if category has children
        if ($category->hasChildren()) {
            return redirect()->route('categories.index')
                ->with('error', 'Cannot delete category with subcategories. Please delete or reassign subcategories first.');
        }

        // Check if category has products
        if ($category->products()->exists()) {
            return redirect()->route('categories.index')
                ->with('error', 'Cannot delete category with products. Please reassign products first.');
        }

        $oldValues = $category->toArray();
        $category->delete();

        $this->auditService->log('DELETE', 'categories', $category->id, $oldValues, null);

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully');
    }

    public function toggleStatus(Category $category)
    {
        $oldValues = $category->toArray();
        $category->update(['is_active' => !$category->is_active]);

        $this->auditService->log(
            $category->is_active ? 'ACTIVATE' : 'DEACTIVATE',
            'categories',
            $category->id,
            $oldValues,
            $category->toArray()
        );

        $status = $category->is_active ? 'activated' : 'deactivated';
        return redirect()->back()
            ->with('success', "Category {$status} successfully");
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:categories,id',
            'categories.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->categories as $categoryData) {
            Category::where('id', $categoryData['id'])
                   ->update(['sort_order' => $categoryData['sort_order']]);
        }

        return response()->json(['success' => true, 'message' => 'Categories reordered successfully']);
    }
}