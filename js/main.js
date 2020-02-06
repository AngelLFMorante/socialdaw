function confirmarLogout() {
    if (confirm("¿Seguro que desea salir?")) {
        location.href=URL_PATH + "/logout";
    }
}