<?php

namespace DiegoAgudo\Passport\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use DiegoAgudo\Passport\Exceptions\MissingScopeException;

class CheckClientCredentialsForAnyScope extends CheckCredentials
{
    /**
     * Validate token credentials.
     *
     * @param  \DiegoAgudo\Passport\Token  $token
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function validateCredentials($token)
    {
        if (! $token) {
            throw new AuthenticationException;
        }
    }

    /**
     * Validate token credentials.
     *
     * @param  \DiegoAgudo\Passport\Token  $token
     * @param  array  $scopes
     * @return void
     *
     * @throws \DiegoAgudo\Passport\Exceptions\MissingScopeException
     */
    protected function validateScopes($token, $scopes)
    {
        if (in_array('*', $token->scopes)) {
            return;
        }

        foreach ($scopes as $scope) {
            if ($token->can($scope)) {
                return;
            }
        }

        throw new MissingScopeException($scopes);
    }
}
