<?php

namespace App\Support;

use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Condition;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\Uom;
use App\Models\User;
use App\Models\Warranty;

class Crud
{
    public static function get(string $slug): array
    {
        $all = self::all();
        abort_unless(isset($all[$slug]), 404);

        return $all[$slug] + ['perm' => $slug, 'with' => [], 'auto' => []];
    }

    public static function options(array $field): array
    {
        $o = $field['options'] ?? [];

        if (isset($o['model'])) {
            return $o['model']::orderBy($o['label'])->pluck($o['label'], 'id')->all();
        }

        return $o;
    }

    private static function ref(string $model, string $label = 'name'): array
    {
        return ['model' => $model, 'label' => $label];
    }

    public static function all(): array
    {
        $active = ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox', 'default' => true];

        return [
            'branches' => [
                'title' => 'Branch List', 'model' => Branch::class, 'search' => ['name', 'code', 'phone'],
                'auto' => ['code' => 'BR-'],
                'fields' => [
                    ['name' => 'code', 'label' => 'Code (auto if empty)', 'type' => 'text', 'rules' => 'nullable|max:50|unique:branches,code,{id}'],
                    ['name' => 'name', 'label' => 'Branch Name', 'type' => 'text', 'rules' => 'required|max:255'],
                    ['name' => 'phone', 'label' => 'Phone', 'type' => 'text', 'rules' => 'nullable|max:50'],
                    ['name' => 'address', 'label' => 'Address', 'type' => 'text', 'rules' => 'nullable|max:255'],
                    $active,
                ],
                'columns' => [['code', 'Code'], ['name', 'Name'], ['phone', 'Phone'], ['address', 'Address'], ['is_active', 'Active', 'bool']],
            ],

            'employees' => [
                'title' => 'Employee List', 'model' => Employee::class, 'search' => ['name', 'code', 'phone', 'email'],
                'with' => ['branch', 'designation', 'department'], 'auto' => ['code' => 'EMP-'],
                'fields' => [
                    ['name' => 'code', 'label' => 'Employee ID (auto if empty)', 'type' => 'text', 'rules' => 'nullable|max:50|unique:employees,code,{id}'],
                    ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => 'required|max:255'],
                    ['name' => 'phone', 'label' => 'Phone', 'type' => 'text', 'rules' => 'nullable|max:50'],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => 'nullable|email|max:255'],
                    ['name' => 'branch_id', 'label' => 'Branch', 'type' => 'select', 'options' => self::ref(Branch::class), 'rules' => 'nullable|exists:branches,id'],
                    ['name' => 'designation_id', 'label' => 'Designation', 'type' => 'select', 'options' => self::ref(Designation::class), 'rules' => 'nullable|exists:designations,id'],
                    ['name' => 'department_id', 'label' => 'Department', 'type' => 'select', 'options' => self::ref(Department::class), 'rules' => 'nullable|exists:departments,id'],
                    ['name' => 'joining_date', 'label' => 'Joining Date', 'type' => 'date', 'rules' => 'nullable|date'],
                    ['name' => 'address', 'label' => 'Address', 'type' => 'text', 'rules' => 'nullable|max:255'],
                    $active,
                ],
                'columns' => [['code', 'ID'], ['name', 'Name'], ['phone', 'Phone'], ['branch.name', 'Branch'], ['designation.name', 'Designation'], ['department.name', 'Department'], ['is_active', 'Active', 'bool']],
            ],

            'designations' => self::simple(Designation::class, 'Designation List', 'designations'),
            'departments'  => self::simple(Department::class, 'Department', 'departments'),
            'categories'   => self::simple(Category::class, 'Categories', 'categories'),
            'brands'       => self::simple(Brand::class, 'Brand', 'brands'),
            'conditions'   => self::simple(Condition::class, 'Condition', 'conditions'),

            'uoms' => [
                'title' => 'UoM', 'model' => Uom::class, 'search' => ['name', 'short_name'],
                'fields' => [
                    ['name' => 'name', 'label' => 'Unit Name', 'type' => 'text', 'rules' => 'required|max:100|unique:uoms,name,{id}'],
                    ['name' => 'short_name', 'label' => 'Short Name', 'type' => 'text', 'rules' => 'nullable|max:20'],
                ],
                'columns' => [['name', 'Name'], ['short_name', 'Short Name']],
            ],

            'warranties' => [
                'title' => 'Warranty', 'model' => Warranty::class, 'search' => ['name'],
                'fields' => [
                    ['name' => 'name', 'label' => 'Warranty Name', 'type' => 'text', 'rules' => 'required|max:100|unique:warranties,name,{id}'],
                    ['name' => 'months', 'label' => 'Duration (months)', 'type' => 'number', 'rules' => 'required|integer|min:0', 'default' => 0],
                ],
                'columns' => [['name', 'Name'], ['months', 'Months']],
            ],

            'products' => [
                'title' => 'Product', 'model' => Product::class, 'search' => ['name', 'code'],
                'with' => ['category', 'brand', 'uom', 'condition', 'warranty'], 'auto' => ['code' => 'PRD-'],
                'fields' => [
                    ['name' => 'code', 'label' => 'Product Code (auto if empty)', 'type' => 'text', 'rules' => 'nullable|max:50|unique:products,code,{id}'],
                    ['name' => 'name', 'label' => 'Product Name', 'type' => 'text', 'rules' => 'required|max:255'],
                    ['name' => 'category_id', 'label' => 'Category', 'type' => 'select', 'options' => self::ref(Category::class), 'rules' => 'nullable|exists:categories,id'],
                    ['name' => 'brand_id', 'label' => 'Brand', 'type' => 'select', 'options' => self::ref(Brand::class), 'rules' => 'nullable|exists:brands,id'],
                    ['name' => 'uom_id', 'label' => 'UoM', 'type' => 'select', 'options' => self::ref(Uom::class), 'rules' => 'nullable|exists:uoms,id'],
                    ['name' => 'condition_id', 'label' => 'Condition', 'type' => 'select', 'options' => self::ref(Condition::class), 'rules' => 'nullable|exists:conditions,id'],
                    ['name' => 'warranty_id', 'label' => 'Warranty', 'type' => 'select', 'options' => self::ref(Warranty::class), 'rules' => 'nullable|exists:warranties,id'],
                    ['name' => 'purchase_price', 'label' => 'Purchase Price', 'type' => 'number', 'step' => '0.01', 'rules' => 'required|numeric|min:0', 'default' => 0],
                    ['name' => 'sale_price', 'label' => 'Sale Price', 'type' => 'number', 'step' => '0.01', 'rules' => 'required|numeric|min:0', 'default' => 0],
                    ['name' => 'min_stock', 'label' => 'Minimum Stock Alert', 'type' => 'number', 'rules' => 'required|integer|min:0', 'default' => 0],
                    ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'rules' => 'nullable'],
                    $active,
                ],
                'columns' => [['code', 'Code'], ['name', 'Name'], ['category.name', 'Category'], ['brand.name', 'Brand'], ['uom.name', 'UoM'], ['purchase_price', 'Purchase'], ['sale_price', 'Sale'], ['is_active', 'Active', 'bool']],
            ],

            'suppliers' => [
                'title' => 'Supplier List', 'model' => Supplier::class, 'search' => ['name', 'phone', 'contact_person'],
                'fields' => [
                    ['name' => 'name', 'label' => 'Supplier Name', 'type' => 'text', 'rules' => 'required|max:255'],
                    ['name' => 'contact_person', 'label' => 'Contact Person', 'type' => 'text', 'rules' => 'nullable|max:255'],
                    ['name' => 'phone', 'label' => 'Phone', 'type' => 'text', 'rules' => 'nullable|max:50'],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => 'nullable|email|max:255'],
                    ['name' => 'address', 'label' => 'Address', 'type' => 'text', 'rules' => 'nullable|max:255'],
                ],
                'columns' => [['name', 'Name'], ['contact_person', 'Contact'], ['phone', 'Phone'], ['email', 'Email'], ['address', 'Address']],
            ],

            'users' => [
                'title' => 'User Create List', 'model' => User::class, 'search' => ['name', 'email'],
                'with' => ['role', 'branch'],
                'fields' => [
                    ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => 'required|max:255'],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => 'required|email|unique:users,email,{id}'],
                    ['name' => 'password', 'label' => 'Password (leave blank to keep)', 'type' => 'password'],
                    ['name' => 'role_id', 'label' => 'Role', 'type' => 'select', 'options' => self::ref(Role::class), 'rules' => 'required|exists:roles,id'],
                    ['name' => 'branch_id', 'label' => 'Branch', 'type' => 'select', 'options' => self::ref(Branch::class), 'rules' => 'nullable|exists:branches,id'],
                    $active,
                ],
                'columns' => [['name', 'Name'], ['email', 'Email'], ['role.name', 'Role'], ['branch.name', 'Branch'], ['is_active', 'Active', 'bool']],
            ],
        ];
    }

    private static function simple(string $model, string $title, string $table): array
    {
        return [
            'title'   => $title,
            'model'   => $model,
            'search'  => ['name'],
            'fields'  => [['name' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => "required|string|max:255|unique:$table,name,{id}"]],
            'columns' => [['name', 'Name']],
        ];
    }
}
