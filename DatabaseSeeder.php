<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Condition;
use App\Models\Designation;
use App\Models\Department;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Uom;
use App\Models\User;
use App\Models\Warranty;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin'], ['is_admin' => true, 'permissions' => []]);
        $branch = Branch::firstOrCreate(['code' => 'BR-00001'], ['name' => 'Head Office', 'address' => 'Dhaka', 'is_active' => true]);

        User::firstOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Administrator', 'password' => 'password', 'role_id' => $role->id, 'branch_id' => $branch->id, 'is_active' => true,
        ]);

        foreach (['New', 'Used', 'Refurbished'] as $n) {
            Condition::firstOrCreate(['name' => $n]);
        }
        foreach (['Pcs', 'Box', 'Set', 'Kg', 'Meter'] as $n) {
            Uom::firstOrCreate(['name' => $n], ['short_name' => strtolower($n)]);
        }
        foreach (['No Warranty' => 0, '6 Months' => 6, '1 Year' => 12, '2 Years' => 24, '3 Years' => 36] as $n => $m) {
            Warranty::firstOrCreate(['name' => $n], ['months' => $m]);
        }
        foreach (['Laptop', 'Desktop', 'Printer', 'Furniture', 'Network Device'] as $n) {
            Category::firstOrCreate(['name' => $n]);
        }
        foreach (['Manager', 'Executive', 'Officer', 'Assistant'] as $n) {
            Designation::firstOrCreate(['name' => $n]);
        }
        foreach (['Admin', 'IT', 'Accounts', 'HR', 'Sales'] as $n) {
            Department::firstOrCreate(['name' => $n]);
        }

        Setting::put('company_name', 'My Company Ltd.');
        Setting::put('address', 'Dhaka, Bangladesh');
        Setting::put('currency', '৳');
    }
}
