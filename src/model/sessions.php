<?php
class Session
{
    public $id;
    public $user_id;
    public $accessToken;
    public $refreshToken;
    public $accessTokenExpiredAt;
    public $refreshTokenExpiredAt;

    public function __construct($id, $user_id,  $refreshToken, $accessToken, $accessTokenExpiredAt, $refreshTokenExpiredAt)
    {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->accessTokenExpiredAt = $accessTokenExpiredAt;
        $this->refreshTokenExpiredAt = $refreshTokenExpiredAt;
    }
}