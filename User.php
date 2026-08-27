<?php 

namespace MVC;

class User 
{
    private Session $session;

    private $id;
    private $username;
    private $role;
    private $isLogged;

    function __construct()
    {
        $this->session = new Session("user");
        $this->username = $this->session->username; 
        $this->isLogged = ($this->username) ? true : false;
    }

    function login($data)
    {
        $data = (object) $data;
        $this->session->username = $data->username;
    }

    function logout()
    {
        $this->session->remove();
    }

    function id()
    {
        return $this->id;
    }

    function username()
    {
        return $this->username;
    }

    function role()
    {
        return $this->role;
    }

    function isLogged()
    {
        return $this->isLogged;
    }
}