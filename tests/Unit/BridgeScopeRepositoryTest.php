<?php

namespace DiegoAgudo\Passport\Tests\Unit;

use DiegoAgudo\Passport\Bridge\Client;
use DiegoAgudo\Passport\Bridge\Scope;
use DiegoAgudo\Passport\Bridge\ScopeRepository;
use DiegoAgudo\Passport\Passport;
use PHPUnit\Framework\TestCase;

class BridgeScopeRepositoryTest extends TestCase
{
    public function test_invalid_scopes_are_removed()
    {
        Passport::tokensCan([
            'scope-1' => 'description',
        ]);

        $repository = new ScopeRepository;

        $scopes = $repository->finalizeScopes(
            [$scope1 = new Scope('scope-1'), new Scope('scope-2')], 'client_credentials', new Client('id', 'name', 'http://localhost'), 1
        );

        $this->assertEquals([$scope1], $scopes);
    }

    public function test_superuser_scope_cant_be_applied_if_wrong_grant()
    {
        Passport::tokensCan([
            'scope-1' => 'description',
        ]);

        $repository = new ScopeRepository;

        $scopes = $repository->finalizeScopes(
            [$scope1 = new Scope('*')], 'refresh_token', new Client('id', 'name', 'http://localhost'), 1
        );

        $this->assertEquals([], $scopes);
    }
}
