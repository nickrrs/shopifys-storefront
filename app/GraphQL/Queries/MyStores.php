<?php

namespace App\GraphQL\Queries;

use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MyStores
{
    /**
     * Retorna as lojas associadas ao usuário autenticado.
     *
     * @return iterable<Store>
     */
    public function __invoke(null $_, array $args): iterable
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        return $user->stores()->withCount('products')->get();
    }
}

