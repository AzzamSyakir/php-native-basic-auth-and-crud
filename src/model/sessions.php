<?php
class Session
{
    public $id;
    public $userId;
    public $accessToken;
    public $refreshToken;
    public $accessTokenExpiredAt;
    public $refreshTokenExpiredAt;

    public function __construct($id, $userId,  $refreshToken, $accessToken, $accessTokenExpiredAt, $refreshTokenExpiredAt)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->accessTokenExpiredAt = $accessTokenExpiredAt;
        $this->refreshTokenExpiredAt = $refreshTokenExpiredAt;
    }
}