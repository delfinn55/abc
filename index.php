<?php

class User
{
    protected int $id;
    protected string $login;
    public string $password;
    protected int $status;

    public function __construct(int $id, string $login, string $password)
    {
        $this->id = $id;
        $this->login = $login;
        $this->password = $password;
        $this->status = 0;
    }

    public function isNew(){
        return $this->status == 0;
    }

    public function isAccepted(){
        return $this->status == 1;
    }

    public function isBanned(){
        return $this->status == 2;
    }

    public function accept(){
        return $this->status = 1;
    }

    public function ban(){
        return $this->status = 2;
    }
}

$user1 = new User(1, 'admin', 'dknfgkdfsjgklfjglk');
$user2 = new User(2, 'manager', 'qwerty');

$user1->accept();
$user1->ban();
