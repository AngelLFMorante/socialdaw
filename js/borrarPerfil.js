function borrarPerfil(login) {
    if (confirm("¿Seguro que desea borrar el perfil de "+login+"?")) {
        location.href=URL_PATH + "/borrarPerfil/"+login;
    }
}