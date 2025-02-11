<?php

namespace DiegoAgudo\Passport;

use Carbon\Carbon;

class TokenRepository
{
    /**
     * Creates a new Access Token.
     *
     * @param  array  $attributes
     * @return \DiegoAgudo\Passport\Token
     */
    public function create($attributes)
    {
        return Passport::token()->create($attributes);
    }

    /**
     * Get a token by the given ID.
     *
     * @param  string  $id
     * @return \DiegoAgudo\Passport\Token
     */
    public function find($id)
    {
        return Passport::token()->where('id', $id)->first();
    }

    /**
     * Get a token by the given user ID and token ID.
     *
     * @param  string  $id
     * @param  int  $userId
     * @return \DiegoAgudo\Passport\Token|null
     */
    public function findForUser($id, $userId)
    {
        return Passport::token()->where('id', $id)->where('user_id', $userId)->first();
    }

    /**
     * Get the token instances for the given user ID.
     *
     * @param  mixed  $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function forUser($userId)
    {
        return Passport::token()->where('user_id', $userId)->get();
    }

    /**
     * Get a valid token instance for the given user and client.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $user
     * @param  \DiegoAgudo\Passport\Client  $client
     * @return \DiegoAgudo\Passport\Token|null
     */
    public function getValidToken($user, $client)
    {
        return $client->tokens()
                    ->whereUserId($user->getAuthIdentifier())
                    ->where('revoked', false)
                    ->where('expires_at', '>', Carbon::now())
                    ->first();
    }

    /**
     * Store the given token instance.
     *
     * @param  \DiegoAgudo\Passport\Token  $token
     * @return void
     */
    public function save(Token $token)
    {
        $token->save();
    }

    /**
     * Revoke an access token.
     *
     * @param  string  $id
     * @return mixed
     */
    public function revokeAccessToken($id)
    {
        return Passport::token()->where('id', $id)->update(['revoked' => true]);
    }

    /**
     * Check if the access token has been revoked.
     *
     * @param  string  $id
     * @return bool
     */
    public function isAccessTokenRevoked($id)
    {
        if ($token = $this->find($id)) {
            return $token->revoked;
        }

        return true;
    }

    /**
     * Find a valid token for the given user and client.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $user
     * @param  \DiegoAgudo\Passport\Client  $client
     * @return \DiegoAgudo\Passport\Token|null
     */
    public function findValidToken($user, $client)
    {
        return $client->tokens()
                      ->whereUserId($user->getAuthIdentifier())
                      ->where('revoked', false)
                      ->where('expires_at', '>', Carbon::now())
                      ->latest('expires_at')
                      ->first();
    }
}
