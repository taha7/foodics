<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create();

        $burger = Product::create(['name' => 'Burger']);
        $onionProduct = Product::create(['name' => 'Onion']);

        $beef = Ingredient::create(['name' => 'Beef']);
        $cheese = Ingredient::create(['name' => 'Cheese']);
        $onion = Ingredient::create(['name' => 'Onion']);

        $beef->openingStock()->create(['quantity' => 20 * 1000]);
        $cheese->openingStock()->create(['quantity' => 5 * 1000]);
        $onion->openingStock()->create(['quantity' => 1 * 1000]);

        $burger->ingredients()->attach($beef->id, ['quantity' => 150]);
        $burger->ingredients()->attach($cheese->id, ['quantity' => 30]);
        $burger->ingredients()->attach($onion->id, ['quantity' => 20]);

        $onionProduct->ingredients()->attach($onion->id, ['quantity' => 10]);
    }
}
