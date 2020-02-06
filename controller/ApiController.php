<?php

namespace controller;

use \model\Orm;

require_once("funciones.php");

class ApiController extends Controller
{
    public function likeClick($postid)
    {
        // mandar la data como cuerpo de la respuesta
        // ¡¡ RECORDAR cambiar el Content-type, si no, se asumiría html
        header('Content-type: application/json');

        if (!isset($_SESSION["login"])) {
            http_response_code(403);
            die (json_encode(["msg"=>"No logueado"]));
        }

        $orm = new Orm;
        $data["estado"] = $orm->darOQuitarLike($postid, $_SESSION["login"]);
        $data["numLikes"] = $orm->contarLikes($postid);

        echo json_encode( $data );
    }

    public function existeLogin($login){
        header('Content-type: application/json');

        $nombreLogin = sanitizar(strtolower($login));
        $loginExiste = (new Orm)->loginExistente($nombreLogin);
        if(!$loginExiste == $login ){
            echo json_encode("correcto");
        }else{
            echo json_encode("existe");
        }
            //programar para que devuelva true o false dado su id
    }


}
