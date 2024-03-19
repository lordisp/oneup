<?php

namespace Database\Factories;

use App\Models\ServiceNowRequest;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceNowRequestFactory extends Factory
{
    protected $model = ServiceNowRequest::class;

    public function definition(): array
    {
        $firstname = $this->faker->firstName();
        $lastname = $this->faker->lastName();
        $name = $firstname . ' ' . $lastname;
        $email = Str::lower($firstname) . '.' . Str::lower($lastname) . '@' . $this->faker->safeEmailDomain();

        return [
            'requestor_mail' => $email,
            'description' => $this->faker->text(),
            'requestor_name' => $name,
            'ritm_number' => 'RITM00' . $this->faker->randomNumber(5),
            'subject' => 'REQ00' . $this->faker->randomNumber(5),
            'opened_by' => $name,
            'cost_center' => $this->faker->randomNumber(6),
        ];
    }
}
