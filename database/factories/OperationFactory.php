<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Operation>
 */
class OperationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'operation' => $this->generateOperation(),
            'description' => $this->faker->sentence(),
        ];
    }

    protected function generateOperation(): string
    {
        $namespaces = [
            'Application',
            'ActiveDirectory',
            'ResourceManager',
            'Administration',
            'Policy'
        ];
        $providers = [
            'OneUp',
            'Azure.Resource',
            'Azure.ActiveDirectory',
        ];
        $resources = [
            'Users',
            'Groups',
            'Operations',
            'Applications',
            'ServicePrincipals',
            'PolicyDefinition',
            'PolicyAssignments',
            'PolicyExemptions',
        ];
        $actions = [
            'read',
            'write',
            'delete',
            'action',
            'create',
            'edit',
        ];

        return $providers[array_rand($providers)] . "/" . $namespaces[array_rand($namespaces)] . "/" . $resources[array_rand($resources)] . "/" . $actions[array_rand($actions)];
    }
}
