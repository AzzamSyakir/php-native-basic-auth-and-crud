<?php
class Task
{
    public $id;
    public $title;
    public $completed;
    public $createdAt;
    public $updatedAt;

    public function __construct($id, $title, $completed, $createdAt, $updatedAt)
    {
        $this->id = $id;
        $this->title = $title;
        $this->completed = $completed;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }
}