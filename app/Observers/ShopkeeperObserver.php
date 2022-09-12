<?php


namespace App\Observers;

use App\Models\Shopkeeper;
use Ramsey\Uuid\Uuid;

class ShopkeeperObserver
{
    public function created(Shopkeeper $shopkeeper) {
        $shopkeeper->wallet()->create([
            'id' => Uuid::uuid4()->toString(),
            'balance' => 0
        ]);
    }
}
