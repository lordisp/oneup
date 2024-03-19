<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceNowRequestFactory extends Factory
{
    public function definition(): array
    {
        $firstname = $this->faker->firstName();
        $lastname = $this->faker->lastName();
        $name = $firstname.' '.$lastname;
        $email = Str::lower($firstname).'.'.Str::lower($lastname).'@'.$this->faker->safeEmailDomain();

        return [
            'requestor_mail' => $email,
            'description' => $this->faker->text(),
            'requestor_name' => $name,
            'ritm_number' => 'RITM00'.$this->faker->randomNumber(5),
            'subject' => 'REQ00'.$this->faker->randomNumber(5),
            'opened_by' => $name,
            'cost_center' => $this->faker->randomNumber(6),
        ];
    }
}
