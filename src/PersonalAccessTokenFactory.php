<?php

namespace DiegoAgudo\Passport;

use Lcobucci\JWT\Parser as JwtParser;
use League\OAuth2\Server\AuthorizationServer;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;

class PersonalAccessTokenFactory
{
    /**
     * The authorization server instance.
     *
     * @var \League\OAuth2\Server\AuthorizationServer
     */
    protected $server;

    /**
     * The client repository instance.
     *
     * @var \DiegoAgudo\Passport\ClientRepository
     */
    protected $clients;

    /**
     * The token repository instance.
     *
     * @var \DiegoAgudo\Passport\TokenRepository
     */
    protected $tokens;

    /**
     * The JWT token parser instance.
     *
     * @var \Lcobucci\JWT\Parser
     *
     * @deprecated This property will be removed in a future Passport version.
     */
    protected $jwt;

    /**
     * Create a new personal access token factory instance.
     *
     * @param  \League\OAuth2\Server\AuthorizationServer  $server
     * @param  \DiegoAgudo\Passport\ClientRepository  $clients
     * @param  \DiegoAgudo\Passport\TokenRepository  $tokens
     * @param  \Lcobucci\JWT\Parser  $jwt
     * @return void
     */
    public function __construct(AuthorizationServer $server,
                                ClientRepository $clients,
                                TokenRepository $tokens,
                                JwtParser $jwt)
    {
        $this->jwt = $jwt;
        $this->tokens = $tokens;
        $this->server = $server;
        $this->clients = $clients;
    }

    /**
     * Create a new personal access token.
     *
     * @param  mixed  $userId
     * @param  string  $name
     * @param  array  $scopes
     * @return \DiegoAgudo\Passport\PersonalAccessTokenResult
     */
    public function make($userId, $name, array $scopes = [])
    {
        $response = $this->dispatchRequestToAuthorizationServer(
            $this->createRequest($this->clients->personalAccessClient(), $userId, $scopes)
        );

        $token = tap($this->findAccessToken($response), function ($token) use ($userId, $name) {
            $this->tokens->save($token->forceFill([
                'user_id' => $userId,
                'name' => $name,
            ]));
        });

        return new PersonalAccessTokenResult(
            $response['access_token'], $token
        );
    }

    /**
     * Create a request instance for the given client.
     *
     * @param  \DiegoAgudo\Passport\Client  $client
     * @param  mixed  $userId
     * @param  array  $scopes
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    protected function createRequest($client, $userId, array $scopes)
    {
        $secret = Passport::$hashesClientSecrets ? $this->clients->getPersonalAccessClientSecret() : $client->secret;

        return (new ServerRequest('POST', 'not-important'))->withParsedBody([
            'grant_type' => 'personal_access',
            'client_id' => $client->id,
            'client_secret' => $secret,
            'user_id' => $userId,
            'scope' => implode(' ', $scopes),
        ]);
    }

    /**
     * Dispatch the given request to the authorization server.
     *
     * @param  \Psr\Http\Message\ServerRequestInterface  $request
     * @return array
     */
    protected function dispatchRequestToAuthorizationServer(ServerRequestInterface $request)
    {
        return json_decode($this->server->respondToAccessTokenRequest(
            $request, new Response
        )->getBody()->__toString(), true);
    }

    /**
     * Get the access token instance for the parsed response.
     *
     * @param  array  $response
     * @return \DiegoAgudo\Passport\Token
     */
    protected function findAccessToken(array $response)
    {
        return $this->tokens->find(
            $this->jwt->parse($response['access_token'])->claims()->get('jti')
        );
    }
}
