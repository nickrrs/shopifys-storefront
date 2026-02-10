<?php

use App\Models\Store;
use Illuminate\Support\Facades\DB;

it('encrypts access_token', function () {
    $store = Store::factory()->create(['access_token' => 'shpat_test_token']);

    $rawFromDb = DB::table('stores')->where('id', $store->id)->value('access_token');

    expect($rawFromDb)->not->toBe('shpat_test_token');
    expect($store->fresh()->access_token)->toBe('shpat_test_token');
});

it('hides access_token on serialization', function () {
    $store = Store::factory()->create();

    $array = $store->toArray();

    expect($array)->not->toHaveKey('access_token');
});
