<?php 
namespace model;

class Usuario{
     public $login;
     public $password;
     public $rol;
     public $nombre;
     public $email;
     public $seguidores = 0;
     public $siguiendo = 0;
     public $loSigues = false;
}