<?php
class User
{
    public $id;
    public $name;
    public $password;
    public $createdAt;
    public $updatedAt;

    public function __construct($id, $name, $password, $createdAt, $updatedAt)
    {
        $this->id = $id;
        $this->name = $name;
        $this->password = $password;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }
}