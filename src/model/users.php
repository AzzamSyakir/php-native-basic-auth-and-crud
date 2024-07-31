<?php
class User
{
    public $id;
    public $username;
    public $email;
    public $password;
    public $token;
    public $confirmed;

    public function __construct($id, $email, $username, $password, $token, $confirmed)
    {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->token = $token;
        $this->confirmed = $confirmed;
    }
}