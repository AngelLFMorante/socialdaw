<?php
namespace controller;
require_once "funciones.php";
use \dawfony\Ti;
use \model\Comentario;
use \model\Orm;
use \model\Post;

class PostController extends Controller{

    
    public function listado($pagina = 1)
    {
        global $config;
        global $URL_PATH;
        $posts =  (new Orm)->obtenerDatosPost($pagina);
        if(isset($_SESSION["login"])){
            foreach($posts as $post){
                $post->like = (new Orm)->leHaDadoLike($post->id, $_SESSION["login"]);
            }
        }
        $cuenta = (new Orm)->contarUltimoPosts();
        $numpaginas = ceil ($cuenta / $config["post_per_page"]);
        $title = "Listado";
        $ruta = "$URL_PATH/loUltimo/page/";
        echo Ti::render("view/principal.phtml", compact("title", "posts", "cuenta", "numpaginas", "pagina", "ruta"));
    }
    public function leerPost($id)
    {
        $title = "Post"; //Aqui hay que poner la ID del post elegido para leer mas. recogemos la ID con request.
        $post = (new Orm)->obtenerUnPost($id);

        if(isset($_SESSION["login"])){

                $post->like = (new Orm)->leHaDadoLike($post->id,$_SESSION["login"]);
            
        
            }
        echo Ti::render("view/leerPost.phtml", compact("title", "post"));
    }

    public function formularioNuevoPost(){
        if (!isset($_SESSION["rol_id"])) {
            throw new \Exception("Intento de inserción de usuario no logueado");
        }
        $categorias = (new Orm) ->obtenerCategorias();
        echo Ti::render("view/formpost.phtml", compact("categorias"));
    }

    public function procesarNuevoPost(){
         global $URL_PATH;
         
        if (!isset($_SESSION["rol_id"])) {
            throw new \Exception("Intento de inserción de usuario no logueado");
        }
        // TO DO: Comprobaciones

        $post = new Post;
        $post->fecha = date('Y-m-d H:i:s');
        $post->resumen = sanitizar($_REQUEST["resumen"]);
        $post->texto = sanitizar($_REQUEST["texto"]);
        $post->foto = $_FILES["foto"]["name"];
        $post->categoria_post_id = $_REQUEST["categoria_id"];
        $post->usuario_login = $_SESSION["login"];
       /*  move_uploaded_file($_FILES["foto"]["tmp_name"], "images" . $post->foto); */
        $cargarFoto = (new Orm)->cargarFoto();
        $post->foto = $cargarFoto;// guardamos la foto y la hemos cargado anteriormente en la funcion cargarFoto()
        (new Orm)->insertarPost($post);
        $id = $_SESSION["login"];
        $postId = (new Orm)->idLogin($id);//sacamos la id del post que se acaba de crear.
        header("Location: " . $URL_PATH."/leerMas/".$postId->id); 
    }
    

    public function nuevoComentario($postid){
        global $URL_PATH;
        if (!isset($_SESSION["rol_id"])) {
            throw new \Exception("Intento de comentario de usuario no logueado");
        }
        $comentario = new Comentario;
        $comentario ->post_id = $postid;
        $comentario ->fecha = date('Y-m-d H:i:s');
        $comentario ->texto = sanitizar($_REQUEST["texto"]);
        $comentario ->usuario_login = $_SESSION["login"];
        (new Orm) ->insertarComentario($comentario);
        header("Location: " . $URL_PATH. "/leerMas/" . $postid . "#comentarios");
    }

    function listarSeguidos($pagina = 1) {
        global $config;
        global $URL_PATH;
        $orm = new Orm;
        $login = $_SESSION["login"];
        $posts = $orm ->obtenerPostsSeguidos($login, $pagina);
        // añadir si tienen like del usuario logueado
        if (isset($_SESSION["login"])) {
            foreach($posts as $post) {
                $post->like = $orm->leHaDadoLike($post->id,$_SESSION["login"]);
            }
        }
        $cuenta = $orm ->contarPostsSeguidos($login);
        $numpaginas = ceil($cuenta / $config["post_per_page"]);
        $titulo = "Posts de usuarios seguidos";
        $subtitulo = "Página $pagina de $numpaginas";
        $ruta = "$URL_PATH/siguiendo/pag/";
        echo Ti::render("view/seguidores.phtml",compact("posts","cuenta", "titulo", "subtitulo", "numpaginas", "pagina", "ruta"));
    }
}