<?php

use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\DB;

it('uppercases status on get', function () {
    $product = Product::factory()->create(['status' => 'active']);

    expect($product->fresh()->status)->toBe('ACTIVE');
});

it('uppercases status on set', function () {
    $product = Product::factory()->create(['status' => 'draft']);

    $raw = DB::table('products')->where('id', $product->id)->value('status');

    expect($raw)->toBe('DRAFT');
});
