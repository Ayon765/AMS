<?php

namespace App\Support;

class Menu
{
    /** [label, [route, params...], permission] */
    public static function all(): array
    {
        return [
            ['title' => 'Manage Branch', 'items' => [
                ['Branch List', ['crud.index', 'branches'], 'branches'],
            ]],
            ['title' => 'Manage Employee', 'items' => [
                ['Employee List', ['crud.index', 'employees'], 'employees'],
                ['Designation List', ['crud.index', 'designations'], 'designations'],
                ['Department', ['crud.index', 'departments'], 'departments'],
            ]],
            ['title' => 'Manage Product', 'items' => [
                ['Categories', ['crud.index', 'categories'], 'categories'],
                ['Brand', ['crud.index', 'brands'], 'brands'],
                ['UoM', ['crud.index', 'uoms'], 'uoms'],
                ['Product', ['crud.index', 'products'], 'products'],
                ['Condition', ['crud.index', 'conditions'], 'conditions'],
                ['Warranty', ['crud.index', 'warranties'], 'warranties'],
            ]],
            ['title' => 'Manage Purchase', 'items' => [
                ['Purchase List', ['doc.index', 'purchase'], 'purchase'],
                ['Inventory Stock', ['stock.index'], 'stock'],
                ['Supplier List', ['crud.index', 'suppliers'], 'suppliers'],
            ]],
            ['title' => 'Manage Inventory', 'items' => [
                ['Delivery List', ['doc.index', 'delivery'], 'delivery'],
                ['Adjustment List', ['doc.index', 'adjustment'], 'adjustment'],
                ['Damage List', ['doc.index', 'damage'], 'damage'],
                ['Internal Transfer List', ['doc.index', 'transfer'], 'transfer'],
            ]],
            ['title' => 'Manage Cash Book', 'items' => [
                ['Cash In', ['cash.index', 'in'], 'cash_in'],
                ['Cash Out', ['cash.index', 'out'], 'cash_out'],
            ]],
            ['title' => 'Manage Approval', 'items' => [
                ['Purchase List', ['approval.index', 'purchase'], 'approval'],
                ['Delivery List', ['approval.index', 'delivery'], 'approval'],
                ['Adjustment List', ['approval.index', 'adjustment'], 'approval'],
                ['Damage List', ['approval.index', 'damage'], 'approval'],
            ]],
            ['title' => 'Manage Report', 'items' => [
                ['Purchase', ['reports.show', 'purchase'], 'reports'],
                ['Delivery', ['reports.show', 'delivery'], 'reports'],
                ['Adjustment', ['reports.show', 'adjustment'], 'reports'],
                ['Damage', ['reports.show', 'damage'], 'reports'],
                ['Cash In', ['reports.show', 'cash_in'], 'reports'],
                ['Cash Out', ['reports.show', 'cash_out'], 'reports'],
            ]],
            ['title' => 'Manage Sell', 'items' => [
                ['Sell Proposal', ['doc.index', 'proposal'], 'proposal'],
                ['Invoice', ['doc.index', 'invoice'], 'invoice'],
            ]],
            ['title' => 'Manage Settings', 'items' => [
                ['Company Profile', ['company.edit'], 'company'],
                ['User Create List', ['crud.index', 'users'], 'users'],
                ['Role Create List', ['roles.index'], 'roles'],
                ['Backup / Restore', ['backup.index'], 'backup'],
            ]],
        ];
    }

    public static function visible($user): array
    {
        $current = request()->url();
        $out = [];

        foreach (self::all() as $group) {
            $items = [];
            $open = false;

            foreach ($group['items'] as [$label, $route, $perm]) {
                if (! $user->hasPerm($perm)) {
                    continue;
                }
                $url = route($route[0], array_slice($route, 1));
                $active = $current === $url || str_starts_with($current, $url . '/');
                $open = $open || $active;
                $items[] = compact('label', 'url', 'active');
            }

            if ($items) {
                $out[] = ['title' => $group['title'], 'items' => $items, 'open' => $open];
            }
        }

        return $out;
    }

    /** permission key => label, grouped (for the role form) */
    public static function permissionGroups(): array
    {
        $override = ['approval' => 'Approve / Reject documents', 'reports' => 'View all reports'];
        $out = [];

        foreach (self::all() as $group) {
            foreach ($group['items'] as [$label, $route, $perm]) {
                $out[$group['title']][$perm] = $override[$perm] ?? $label;
            }
        }

        return $out;
    }
}
