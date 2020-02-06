<?php

namespace model;

use dawfony\Klasto;

class Orm
{

    public function obtenerDatosPost($pagina = 1)
    {
        global $config;
        $limite = $config["post_per_page"];
        $offset = ($pagina -1) * $limite;
        $bd = Klasto::getInstance();
        $sql = "SELECT post.id,post.fecha, post.resumen, post.texto, post.foto,
         categoria_post.descripcion as categoria_post_id, post.usuario_login 
         from post, categoria_post where categoria_post.id=post.categoria_post_id ORDER BY fecha desc LIMIT $limite OFFSET $offset";
        $posts = $bd->query($sql, [], "model\post");

        /* $categorias = $this->obtenerCategorias(); */
        foreach($posts as $post){
            $post->numLikes = $this->contarLikes($post->id);
            $post->numComentarios = $this->contarComentarios($post->id);
            $post->categoria = $post->categoria_post_id;
        }
        return $posts;
    }

    public function contarUltimoPosts(){
        return Klasto::getInstance()->queryOne("SELECT count(*) as cuenta FROM `post`")["cuenta"];
    }

    public function contarComentarios($postid){
        return Klasto::getInstance()->queryOne(
            "SELECT count(*) as cuenta from comenta WHERE post_id=?",
            [$postid]
        )["cuenta"];
    }
    public function obtenerComentarios($postid){
        return Klasto::getInstance()->query(
            "SELECT usuario_login, texto, fecha FROM comenta WHERE post_id=? ORDER BY fecha DESC",
            [$postid],
            "\model\Comentario"
        );
    }

    public function obtenerPostsUsuario($login)
    {
        $bd = Klasto::getInstance();
        $sql = "SELECT post.id, post.fecha, post.resumen, post.texto, post.foto,
        categoria_post.descripcion as categoria_post_id, post.usuario_login
        from post, categoria_post where categoria_post.id=post.categoria_post_id 
        and post.usuario_login=? ORDER BY fecha desc";
        return $bd->query($sql, [$login], "model\post");
    }

    public function obtenerUnPost($id)
    {
        $post = Klasto::getInstance()->queryOne(
            "SELECT `id`, `fecha`, `resumen`, `texto`, `foto`, `categoria_post_id`, `usuario_login` FROM `post`"
                . " WHERE id=?",
            [$id],
            "model\Post"
        );
        $post->numLikes = $this->contarLikes($id);
        $post->comentarios = $this->obtenerComentarios($id);
        $post->numComentarios = count($post->comentarios);
        $categorias = $this->obtenerCategorias();
        $post->categoria = $categorias[$post->categoria_post_id]["descripcion"];
        return $post;
    }

    public function insertarRegistro($usuario)
    {
        $bd = Klasto::getInstance(); //esto hace que inicie la bd.
        $sql = "INSERT INTO usuario (login, password, rol_id, nombre, email) VALUES (?, ?, ?, ?, ?);";
        return $bd->execute($sql, [$usuario->login, $usuario->password, $usuario->rol, $usuario->nombre, $usuario->email], "model\usuario");
    }

    public function datosPerfil($login)
    {
        $bd = Klasto::getInstance();
        $sql = "SELECT usuario.login, usuario.password, rol.descripcion as rol, usuario.nombre, usuario.email 
        from usuario, rol where rol.id=usuario.rol_id and login=?;";
        return $bd->queryOne($sql, [$login], "model\usuario");
    }

    public function darOQuitarLike($postid, $login)
    {
        $db = Klasto::getInstance();
        $num = $db->execute(
            "DELETE FROM `like` WHERE post_id = ? AND usuario_login = ?",
            [$postid, $login]
        );
        if ($num > 0) {
            return false; // Ya no tiene like
        }
        $db->execute(
            "INSERT INTO `like`(post_id, usuario_login) VALUES(?,?)",
            [$postid, $login]
        );
        return true; // Sí tiene like

    }

    public function contarLikes($postid)
    {
        return Klasto::getInstance()->queryOne(
            "SELECT count(*) as cuenta FROM `like` WHERE post_id = ?",
            [$postid]
        )["cuenta"];
    }

    public function leHaDadoLike($postid, $login)
    {
        return (Klasto::getInstance()->queryOne(
            "SELECT count(*) as cuenta FROM `like` WHERE post_id = ? and usuario_login = ?",
            [$postid, $login]
        )["cuenta"]) > 0;
    }

    public function comprobarUsuario($usuario)
    {
        $bd = Klasto::getInstance();
        $sql = "SELECT usuario.login, usuario.password, rol.id as rol, usuario.nombre, usuario.email 
        from usuario, rol where rol.id=usuario.rol_id and login=?;";
        return $bd->queryOne($sql, [$usuario->login]);
    }

    public function obtenerCategorias(){
        return Klasto::getInstance()->query(
            "SELECT id, descripcion FROM categoria_post"
        );
    }

    public function insertarPost($post){

        $bd = Klasto::getInstance();
        $sql = "INSERT INTO post (`fecha`, `resumen`, `texto`, `foto`, `categoria_post_id`, `usuario_login`) VALUES (?,?,?,?,?,?)";
        return $bd->execute($sql, [$post->fecha, $post->resumen, $post->texto,
        $post->foto, $post->categoria_post_id, $post->usuario_login]);

    }

    public function insertarComentario($comentario){
        Klasto::getInstance()->execute(
            "INSERT INTO `comenta`(`post_id`, `usuario_login`, `fecha`, `texto`)"
                . " VALUES (?,?,?,?)",
            [$comentario->post_id, $comentario->usuario_login,
            $comentario->fecha, $comentario->texto]
        );
    }

    public function obtenerPostsSeguidos($login, $pagina){
        global $config;
        $limit = $config["post_per_page"];
        $offset = ($pagina -1) * $limit;
        $posts = Klasto::getInstance()->query(
            "SELECT `id`, `fecha`, `resumen`, `texto`, `foto`, `categoria_post_id`, `usuario_login`"
                . " FROM `post` JOIN `sigue` ON post.usuario_login = sigue.usuario_login_seguido"
                . " WHERE sigue.usuario_login_seguidor = ?"
                . " ORDER BY `fecha` DESC"
                . " LIMIT $limit OFFSET $offset",
            [$login],
            "model\Post"
        );
        $categorias = $this->obtenerCategorias();
        foreach ($posts as $post) {
            $post->numLikes = $this->contarLikes($post->id);
            $post->numComments = $this->contarComentarios($post->id);
            $post->categoria = $categorias[$post->categoria_post_id]["descripcion"];
        }
        return $posts;
    }

    public function contarPostsSeguidos($login){
        return Klasto::getInstance()->queryOne(
            "SELECT count(*) as cuenta"
                . " FROM `post` JOIN `sigue` ON post.usuario_login = sigue.usuario_login_seguido"
                . " WHERE sigue.usuario_login_seguidor = ?",
            [$login]
        )["cuenta"];
    }

    public function seguir($tulogin, $login) {
        Klasto::getInstance()->execute(
            "INSERT INTO sigue(usuario_login_seguidor, usuario_login_seguido)"
                . " VALUES(?,?)",
                [$tulogin, $login]
        );
    }
    public function noSeguir($tulogin, $login) {
        Klasto::getInstance()->execute(
            "DELETE FROM sigue WHERE usuario_login_seguidor=? AND usuario_login_seguido=?",
                [$tulogin, $login]
        );
    }

    public function contarSeguidores($login)
    {
        return Klasto::getInstance()->queryOne(
            "SELECT count(*) as cuenta from sigue WHERE usuario_login_seguido = ? ",
            [$login]
        )["cuenta"];
    }

    public function contarSiguiendo($login)
    {
        return Klasto::getInstance()->queryOne(
            "SELECT count(*) as cuenta from sigue WHERE usuario_login_seguidor = ? ",
            [$login]
        )["cuenta"];
    }

    public function loSigues($tulogin, $login)
    {
        return Klasto::getInstance()->queryOne(
            "SELECT count(*) as cuenta from sigue"
                ." WHERE usuario_login_seguidor = ? "
                ." AND usuario_login_seguido = ? ",
            [$tulogin, $login]
        )["cuenta"] > 0;
    }

    public function cargarFoto(){
        $target_dir = "assets/fotos/";
        $target_file = $target_dir . basename($_FILES["foto"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    
        if (isset($_POST["submit"])) {
            $check = getimagesize($_FILES["foto"]["tmp_name"]);
            if ($check !== false) {
                echo "es una imagen - " . $check["mime"] . ".";
                $uploadOk = 1;
            } else {
                echo "no es una imagen.";
                $uploadOk = 0;
            }
        }
        
        if (file_exists($target_file)) {
            echo "El archivo ya existe.";
            $uploadOk = 0;
        }
    
        if (
            $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
            && $imageFileType != "gif"
        ) {
            echo "Solo se permite archivos JPG, JPEG, PNG & GIF";
            $uploadOk = 0;
        }
    
        move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file);
        $nombre = trim(str_replace("assets/fotos/", "",$target_file));
        return $nombre;
    }

    public function idLogin($login) {
        return Klasto::getInstance()->queryOne(
            "SELECT id FROM post WHERE usuario_login=? ORDER BY fecha DESC ",
            [$login], "model\Post"
        );
    }

    public function borrarLike($id){
        
        return Klasto::getInstance()->execute(
            "DELETE FROM `like` WHERE `like`.`post_id` = ? ",
            [$id]
        );

    }
    public function borrarComentario($id){
        return Klasto::getInstance()->execute(
            "DELETE FROM `comenta` WHERE `comenta`.`post_id` = ? ",
            [$id]
        );
    }

    public function borrarPost($id){
        
        return Klasto::getInstance()->execute(
            "DELETE FROM `post` WHERE `post`.`id` = ?",
            [$id]
        );

    }

    public function borrarLikePerfil($login){
        
        return Klasto::getInstance()->execute(
            "DELETE FROM `like` WHERE `like`.`usuario_login` = ? ",
            [$login]
        );

    }
    public function borrarComentarioPerfil($login){
        return Klasto::getInstance()->execute(
            "DELETE FROM `comenta` WHERE `comenta`.`usuario_login` = ?" ,
            [$login]
        );
    }
    public function borrarTodosPost($login){
        return Klasto::getInstance()->execute(
            "DELETE FROM `post` WHERE usuario_login=?",
            [$login]
        );
    }

    public function borrarSeguidores($login){
        return Klasto::getInstance()->execute(
            "DELETE FROM `sigue` WHERE usuario_login_seguidor=? OR usuario_login_seguido=?",
            [$login, $login]
        );
    }
    public function borrarUsuario($login){
        return Klasto::getInstance()->execute(
            "DELETE FROM `usuario` WHERE `usuario`.`login` = ?",
            [$login]//para borrar perfil.
            );
    }

    public function borrarPerfil($login){

        Klasto::getInstance()->startTransaction();

        (new Orm)->borrarLikePerfil($login);
        (new Orm)->borrarComentarioPerfil($login);
        (new Orm)->borrarTodosPost($login);
        (new Orm)->borrarSeguidores($login);
        (new Orm)->borrarUsuario($login);
        Klasto::getInstance()->commit();

     }

     public function loginExistente($login){
         return Klasto::getInstance()->queryOne(
             "SELECT login FROM `usuario` WHERE login=?",
             [$login]
         );
     }

}
