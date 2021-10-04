<?php

namespace Tests\Feature\Service\User;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Support\Str;
use App\Services\User\SignUpService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SignUpServiceTest extends TestCase
{
    use RefreshDatabase;

    private $response;
    private $oAuthService;
    private $signUpService;
    private $csrfToken;

    protected function setUp(): void
    {
        parent::setUp();

        $validation = resolve('Illuminate\Contracts\Validation\Factory');
        $cookie = resolve('Laravel\Passport\ApiTokenCookieFactory');
        $userRepository = resolve('App\Contracts\Repository\UserRepositoryContract');

        /**
         * Put these properties on the class since they are needed
         * by individual test cases.
         */
        $this->response = resolve('Illuminate\Contracts\Routing\ResponseFactory');
        $this->csrfToken = Str::random(10);
        $this->oAuthService = \Mockery::mock('App\Services\OauthService');

        $this->signUpService = new SignUpService(
            $validation,
            $userRepository,
            $this->response,
            $cookie,
            $this->oAuthService
        );
    }

    public function testSigningUpPutsUserInDatabase()
    {
        $userInfo = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->safeEmail,
            'password' => Str::random(10)
        ];

        $oAuthCredentials = [
            'access_token' => Str::random(12),
            'refresh_token' => Str::random(12)
        ];

        $this->oAuthService->shouldReceive('passwordGrantWithResponse->withCookie')
                            ->andReturn($this->response->json($oAuthCredentials));

        $this->signUpService->signUpResponse($userInfo, $this->csrfToken);

        $this->assertDatabaseHas('users', ['email' => $userInfo['email']]);
    }

    public function testInvalidSignUpDataReturnsErrors()
    {
        $userInfo = [
            'first_name' => '',
            'last_name' => $this->faker->lastName,
            'email' => 'invalidemail',
            'password' => Str::random(10)
        ];

        $response = $this->signUpService->signUpResponse($userInfo, $this->csrfToken);
        $responseContent = json_decode($response->getContent(), true);

        $this->assertEquals($response->status(), 422);
        $this->assertArrayHasKey('first_name', $responseContent['messages']);
        $this->assertArrayHasKey('email', $responseContent['messages']);
    }
}
