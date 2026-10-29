<?php

namespace App\Support;

class DocType
{
    public static function all(): array
    {
        return [
            'purchase'   => ['label' => 'Purchase',        'prefix' => 'PUR', 'perm' => 'purchase',   'price_field' => 'purchase_price', 'approval' => true,  'immediate' => false, 'direction' => false],
            'delivery'   => ['label' => 'Delivery',        'prefix' => 'DEL', 'perm' => 'delivery',   'price_field' => 'purchase_price', 'approval' => true,  'immediate' => false, 'direction' => false],
            'adjustment' => ['label' => 'Adjustment',      'prefix' => 'ADJ', 'perm' => 'adjustment', 'price_field' => 'purchase_price', 'approval' => true,  'immediate' => false, 'direction' => true],
            'damage'     => ['label' => 'Damage',          'prefix' => 'DMG', 'perm' => 'damage',     'price_field' => 'purchase_price', 'approval' => true,  'immediate' => false, 'direction' => false],
            'transfer'   => ['label' => 'Internal Transfer', 'prefix' => 'TRF', 'perm' => 'transfer', 'price_field' => 'purchase_price', 'approval' => false, 'immediate' => true,  'direction' => false],
            'proposal'   => ['label' => 'Sell Proposal',   'prefix' => 'PRO', 'perm' => 'proposal',   'price_field' => 'sale_price',     'approval' => false, 'immediate' => false, 'direction' => false],
            'invoice'    => ['label' => 'Invoice',         'prefix' => 'INV', 'perm' => 'invoice',    'price_field' => 'sale_price',     'approval' => false, 'immediate' => true,  'direction' => false],
        ];
    }

    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function get(string $type): array
    {
        $all = self::all();
        abort_unless(isset($all[$type]), 404);

        return $all[$type];
    }
}
