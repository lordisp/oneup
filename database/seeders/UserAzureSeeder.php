<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserAzureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        User::firstOrCreate(['email' => 'rafael.camison@austrian.com'], [
            'firstName' => 'Rafael',
            'lastName' => 'Camison',
            'displayName' => 'Camison, Rafael',
            'provider_id' => '1f4db4e4-93c9-4f58-b060-6757b2e621a3',
            'provider' => 'oneup_aad',
        ])->save();
        User::firstOrCreate(['email' => 'stephan.abel@dlh.de'], [
            'firstName' => 'Stephan',
            'lastName' => 'Abel',
            'displayName' => 'Abel, Stephan',
            'provider_id' => '5d5a1b47-ecae-4b73-9eb5-39a1d9e9ab6b',
            'provider' => 'oneup_aad',
        ])->save();
        User::firstOrCreate(['email' => 'bernd.zeinar@dlh.de'], [
            'firstName' => 'Bernd',
            'lastName' => 'Zeinar',
            'displayName' => 'Zeinar, Bernd',
            'provider_id' => '7dc98c09-d66f-4bdb-aa42-6b01b105af04',
            'provider' => 'oneup_aad',
        ])->save();
        User::firstOrCreate(['email' => 'joerg.peise@dlh.de'], [
            'firstName' => 'Jörg',
            'lastName' => 'Peise',
            'displayName' => 'Peise, Jörg',
            'provider_id' => '7761796b-20da-4c22-9497-485df7e7a7c8',
            'provider' => 'oneup_aad',
        ])->save();
    }
}
