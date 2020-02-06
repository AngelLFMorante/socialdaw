function borrarPost(id) {
    if (confirm("¿Seguro que desea borrar el post "+id+"?")) {
        location.href=URL_PATH + "/borrarPost/"+id;
    }
}