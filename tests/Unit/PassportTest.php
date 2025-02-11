<?php

namespace DiegoAgudo\Passport\Tests\Unit;

use DiegoAgudo\Passport\AuthCode;
use DiegoAgudo\Passport\Client;
use DiegoAgudo\Passport\ClientRepository;
use DiegoAgudo\Passport\Passport;
use DiegoAgudo\Passport\PersonalAccessClient;
use DiegoAgudo\Passport\RefreshToken;
use DiegoAgudo\Passport\Token;
use PHPUnit\Framework\TestCase;

class PassportTest extends TestCase
{
    public function test_scopes_can_be_managed()
    {
        Passport::tokensCan([
            'user' => 'get user information',
        ]);

        $this->assertTrue(Passport::hasScope('user'));
        $this->assertEquals(['user'], Passport::scopeIds());
        $this->assertSame('user', Passport::scopes()[0]->id);
    }

    public function test_auth_code_instance_can_be_created()
    {
        $authCode = Passport::authCode();

        $this->assertInstanceOf(AuthCode::class, $authCode);
        $this->assertInstanceOf(Passport::authCodeModel(), $authCode);
    }

    public function test_client_instance_can_be_created()
    {
        $client = Passport::client();

        $this->assertInstanceOf(Client::class, $client);
        $this->assertInstanceOf(Passport::clientModel(), $client);
    }

    public function test_personal_access_client_instance_can_be_created()
    {
        $client = Passport::personalAccessClient();

        $this->assertInstanceOf(PersonalAccessClient::class, $client);
        $this->assertInstanceOf(Passport::personalAccessClientModel(), $client);
    }

    public function test_missing_personal_access_client_is_reported()
    {
        $this->expectException('RuntimeException');

        Passport::usePersonalAccessClientModel(PersonalAccessClientStub::class);

        $clientRepository = new ClientRepository;
        $clientRepository->personalAccessClient();
    }

    public function test_token_instance_can_be_created()
    {
        $token = Passport::token();

        $this->assertInstanceOf(Token::class, $token);
        $this->assertInstanceOf(Passport::tokenModel(), $token);
    }

    public function test_refresh_token_instance_can_be_created()
    {
        $refreshToken = Passport::refreshToken();

        $this->assertInstanceOf(RefreshToken::class, $refreshToken);
        $this->assertInstanceOf(Passport::refreshTokenModel(), $refreshToken);
    }

    public function test_refresh_token_model_can_be_changed()
    {
        Passport::useRefreshTokenModel(RefreshTokenStub::class);

        $refreshToken = Passport::refreshToken();

        $this->assertInstanceOf(RefreshTokenStub::class, $refreshToken);
        $this->assertInstanceOf(Passport::refreshTokenModel(), $refreshToken);

        Passport::useRefreshTokenModel(RefreshToken::class);
    }
}

class PersonalAccessClientStub
{
    public function exists()
    {
        return false;
    }
}

class RefreshTokenStub
{
}
