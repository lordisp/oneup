<?php

namespace Tests\Feature\Validators;

use App\Validators\PdnsValidate;
use Database\Seeders\TokenCacheProviderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PdnsValidatorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            TokenCacheProviderSeeder::class,
        ]);
    }

    /** @test */
    public function record_type_validation_successful()
    {
        $toValidate = 'A';

        $result = PdnsValidate::recordType($toValidate);

        $this->assertEquals($toValidate, $result[0]);
    }

    /** @test */
    public function failed_record_type_validation_throws_an_exception()
    {
        $toValidate = 'B';

        $this->expectException(ValidationException::class);

        PdnsValidate::recordType($toValidate);
    }

    /** @test */
    public function provider_validation_successful()
    {
        $toValidate = 'lhg_arm';

        $result = PdnsValidate::provider($toValidate);

        $this->assertEquals($toValidate, $result);
    }

    /** @test */
    public function failed_provider_validation_throws_an_exception()
    {
        $toValidate = 'foo';

        $this->expectException(ValidationException::class);

        PdnsValidate::provider($toValidate);
    }
}
