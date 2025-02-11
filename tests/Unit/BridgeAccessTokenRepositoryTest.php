<?php

namespace DiegoAgudo\Passport\Tests\Unit;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use DiegoAgudo\Passport\Bridge\AccessToken;
use DiegoAgudo\Passport\Bridge\AccessTokenRepository;
use DiegoAgudo\Passport\Bridge\Client;
use DiegoAgudo\Passport\Bridge\Scope;
use DiegoAgudo\Passport\TokenRepository;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class BridgeAccessTokenRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function test_access_tokens_can_be_persisted()
    {
        $expiration = CarbonImmutable::now();

        $tokenRepository = m::mock(TokenRepository::class);
        $events = m::mock(Dispatcher::class);

        $tokenRepository->shouldReceive('create')->once()->andReturnUsing(function ($array) use ($expiration) {
            $this->assertSame(1, $array['id']);
            $this->assertSame(2, $array['user_id']);
            $this->assertSame('client-id', $array['client_id']);
            $this->assertEquals(['scopes'], $array['scopes']);
            $this->assertEquals(false, $array['revoked']);
            $this->assertInstanceOf('DateTime', $array['created_at']);
            $this->assertInstanceOf('DateTime', $array['updated_at']);
            $this->assertEquals($expiration, $array['expires_at']);
        });

        $events->shouldReceive('dispatch')->once();

        $accessToken = new AccessToken(2, [new Scope('scopes')], new Client('client-id', 'name', 'redirect'));
        $accessToken->setIdentifier(1);
        $accessToken->setExpiryDateTime($expiration);

        $repository = new AccessTokenRepository($tokenRepository, $events);

        $repository->persistNewAccessToken($accessToken);
    }

    public function test_can_get_new_access_token()
    {
        $tokenRepository = m::mock(TokenRepository::class);
        $events = m::mock(Dispatcher::class);
        $repository = new AccessTokenRepository($tokenRepository, $events);
        $client = new Client('client-id', 'name', 'redirect');
        $scopes = [new Scope('place-orders'), new Scope('check-status')];
        $userIdentifier = 123;

        $token = $repository->getNewToken($client, $scopes, $userIdentifier);

        $this->assertInstanceOf(AccessToken::class, $token);
        $this->assertEquals($client, $token->getClient());
        $this->assertEquals($scopes, $token->getScopes());
        $this->assertSame($userIdentifier, $token->getUserIdentifier());
    }
}
