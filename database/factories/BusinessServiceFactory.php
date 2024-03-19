<?php

namespace Database\Factories;

use App\Exceptions\BusinessServiceFactoryException;
use App\Models\BusinessService;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BusinessServiceFactory extends Factory
{
    /**
     * @throws BusinessServiceFactoryException
     */
    public function definition(): array
    {
        $names = $this->getAvailableNames();

        $name = $this->faker->unique()->randomElement($names);

        return [
            'name' => $name,
        ];
    }

    /**
     * @throws BusinessServiceFactoryException
     */
    protected function getAvailableNames(): array
    {
        $path = base_path('database/factories/business_service.stup');
        $names = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $usedNames = BusinessService::pluck('name')->toArray();
        $availableNames = array_diff($names, $usedNames);

        if (empty($availableNames)) {
            throw new BusinessServiceFactoryException('No more unique names available in business_service.stup');
        }

        return $availableNames;
    }

    public function configure(): BusinessServiceFactory
    {
        return $this->afterCreating(function (BusinessService $businessService) {
            $businessService->users()
                ->attach(
                    User::factory()
                        ->count(rand(5, 25))
                        ->create()
                        ->pluck('id')
                );

            $user = User::withCount('businessServices')
                ->having('business_services_count', '<=', 1)
                ->whereRelation('businessServices', 'id', '!=', $businessService->id)
                ->get()
                ->pluck('id');

            $businessService->users()->attach($user);
        });
    }
}
