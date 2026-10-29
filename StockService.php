<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockDocument;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function move(int $productId, int $branchId, float $delta): void
    {
        $stock = Stock::firstOrCreate(['product_id' => $productId, 'branch_id' => $branchId], ['qty' => 0]);
        $new = round((float) $stock->qty + $delta, 2);

        if ($new < 0) {
            $name = Product::find($productId)?->name;
            throw new \RuntimeException("Insufficient stock for \"$name\" (available: " . (float) $stock->qty . ').');
        }

        $stock->update(['qty' => $new]);
    }

    /** Apply the document's stock effect and mark it approved / completed. */
    public function apply(StockDocument $doc): void
    {
        DB::transaction(function () use ($doc) {
            foreach ($doc->items as $i) {
                $q = (float) $i->qty;

                switch ($doc->type) {
                    case 'purchase':
                        $this->move($i->product_id, $doc->branch_id, $q);
                        break;
                    case 'delivery':
                    case 'damage':
                    case 'invoice':
                        $this->move($i->product_id, $doc->branch_id, -$q);
                        break;
                    case 'adjustment':
                        $this->move($i->product_id, $doc->branch_id, $i->direction === 'in' ? $q : -$q);
                        break;
                    case 'transfer':
                        $this->move($i->product_id, $doc->branch_id, -$q);
                        $this->move($i->product_id, $doc->to_branch_id, $q);
                        break;
                }
            }

            $doc->update(['status' => in_array($doc->type, ['transfer', 'invoice']) ? 'completed' : 'approved']);
        });
    }
}
