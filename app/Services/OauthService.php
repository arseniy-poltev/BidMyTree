<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Contracts\Cookie\Factory as Cookie;
use Illuminate\Contracts\Routing\ResponseFactory as Response;

class OauthService
{
    private $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function passwordGrantWithResponse($email, $password)
    {
        $oAuthCredentials = $this->passwordGrantAuth($email, $password);

        $response = $this->response
            ->json($oAuthCredentials);

        return $response;
    }

    public function passwordGrantAuth($email, $password)
    {
        $client = new Client();

        $request = $client->post(env('APP_URL') . '/oauth/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => env('OAUTH_CLIENT_ID'),
                'client_secret' => env('OAUTH_CLIENT_SECRET'),
                'username' => $email,
                'password' => $password,
                'scope' => '*',
            ]
        ]);

        $credentials = json_decode((string) $request->getBody(), true);

        return $credentials;
    }
}
