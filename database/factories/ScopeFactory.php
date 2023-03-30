<?php

namespace Database\Factories;

use App\Models\Scope;
use Illuminate\Database\Eloquent\Factories\Factory;
use Laravel\Passport\Passport;

class ScopeFactory extends Factory
{
    public function definition(): array
    {
        $scopeIds = Passport::scopeIds();

        $scopeName = $scopeIds[array_rand($scopeIds)];

        $scope = Scope::where('scope', $scopeName)->first();

        while ($scope!=null){
            $scopeName = $scopeIds[array_rand($scopeIds)];

            $scope = Scope::where('scope', $scopeName)->first();
        }

            return [
                'scope' => $scopeName
            ];
    }
}
