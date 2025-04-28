<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Validator;

class LoginRequestTest extends TestCase
{
    protected function getValidationRules()
    {
        return (new LoginRequest())->rules();
    }

    /** @test */
    public function email_is_required()
    {
        $validator = Validator::make(
            ['password' => 'password123'],
            $this->getValidationRules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->messages());
    }

    /** @test */
    public function email_must_be_valid()
    {
        $validator = Validator::make([
            'email' => 'not-an-email',
            'password' => 'password123'
        ], $this->getValidationRules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->messages());
    }

    /** @test */
    public function email_must_not_exceed_maximum_length()
    {
        $longEmail = str_repeat('a', 256) . '@example.com'; // Creates an email > 255 characters

        $validator = Validator::make([
            'email' => $longEmail,
            'password' => 'password123'
        ], $this->getValidationRules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->messages());
    }

    /** @test */
    public function password_is_required()
    {
        $validator = Validator::make([
            'email' => 'test@example.com'
        ], $this->getValidationRules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->messages());
    }

    /** @test */
    public function password_must_be_string()
    {
        $validator = Validator::make([
            'email' => 'test@example.com',
            'password' => 123456
        ], $this->getValidationRules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->messages());
    }

    /** @test */
    public function valid_credentials_pass_validation()
    {
        $validator = Validator::make([
            'email' => 'test@example.com',
            'password' => 'password123'
        ], $this->getValidationRules());

        $this->assertFalse($validator->fails());
    }
}
