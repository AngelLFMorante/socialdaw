<?php

namespace controller;

use dawfony\Ti;
use model\Orm;
use model\Usuario;
use model\Post;

require_once("funciones.php");

class UserController extends Controller
{

    public function formularioRegistro()
    {
        $title = "Resgistro";
        $usuario = new Usuario;
        $usuario->login = "";
        $usuario->password = "";
        $usuario->nombre = "";
        $usuario->email = "";
        echo Ti::render("view/formregistro.phtml", compact("title", "usuario"));
    }

    public function login()
    {
        $title = "Login";
        echo Ti::render("view/login.phtml", compact("title"));
    }

    public function perfil($login)
    {
        
            $title = "Perfil";
            $datosUsuario = (new Orm)->datosPerfil($login);
            $posts =  (new Orm)->obtenerPostsUsuario($login);
            $datosUsuario->seguidores = (new Orm)->contarSeguidores($login);
            $datosUsuario->siguiendo = (new Orm)->contarSiguiendo($login);
            if (isset($_SESSION["login"])) {
                $datosUsuario->loSigues = (new Orm)->loSigues($_SESSION["login"], $login);
            }
            echo Ti::render("view/Perfil.phtml", compact("title", "datosUsuario", "posts"));
        
    }

    public function procesarRegistro()
    {
        
        $title = "Login";
        $usuario = new Usuario;

        $usuario->login = sanitizar(strtolower($_REQUEST["login"]) ??  "");
        $usuario->nombre = sanitizar($_REQUEST["nombre"] ?? "");
        $usuario->email = sanitizar($_REQUEST["email"] ?? "");
        $usuario->rol = 1;
        $usuario->password = password_hash($_REQUEST["password"], PASSWORD_DEFAULT);
            
            (new Orm)->insertarRegistro($usuario);
            sleep(3);
            echo Ti::render("view/login.phtml",compact("title"));
            
        
    }



    public function procesarLogin()
    {
        global $URL_PATH;

        $login = strtolower(sanitizar($_REQUEST["login"]));
        $usuario = new Usuario;
        $usuario->login = $login;
        $usuario->password = $_REQUEST["password"];
        $hashpass = (new Orm)->comprobarUsuario($usuario);
        if (!password_verify($usuario->password, $hashpass["password"])) {
            $error_msg = "Login o contraseña incorrecto";
            echo Ti::render("view/login.phtml", compact("error_msg", "login"));
        } else {
            $_SESSION['login'] = $usuario->login;
            $_SESSION['rol_id'] = $hashpass["rol"];
            var_dump($_SESSION["rol_id"]);
            header("Location: $URL_PATH/");
        }
    }

    public function seguirPerfil($login){
        global $URL_PATH;
        if (!isset($_SESSION["rol_id"])) {
            throw new \Exception("Intento de seguir a un usuario por parte de usuario no logueado");
        }
        $orm = new Orm;
        $orm->seguir($_SESSION["login"], $login);
        header("Location: $URL_PATH/perfil/$login");
    }

    public function noSeguirPerfil($login){
        global $URL_PATH;
        if (!isset($_SESSION["rol_id"])) {
            throw new \Exception("Intento de dejar de seguir a un usuario por parte de usuario no logueado");
        }
        $orm = new Orm;
        $orm->noSeguir($_SESSION["login"], $login);
        header("Location: $URL_PATH/perfil/$login");
    }

    public function hacerLogout()
    {
        global $URL_PATH;
        session_destroy();
        header("Location: $URL_PATH/");
    }

    public function borrarPost($id){
        global $URL_PATH;
        var_dump($id);
        (new Orm)->borrarLike($id);
        (new Orm)->borrarComentario($id);
        (new Orm)->borrarPost($id);
        header("Location: $URL_PATH/");
    }
     public function borrarPerfil($login){
        global $URL_PATH;
        (new Orm)->borrarPerfil($login);
        header("Location: $URL_PATH/");
    } 
}
