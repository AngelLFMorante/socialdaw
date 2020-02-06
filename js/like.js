function likeClick(postid) {
    console.log("Like de " +postid);
    fetch(URL_PATH+"/api/like/"+postid) 
        .then((res)=>res.json())
        .then((res)=>{
            // res contiene un objeto con la respuesta json del servidor.
            console.log(res);
            var corazonEl = document.querySelector("#likecorazon"+postid);
            var contadorEl = document.querySelector("#likecontador"+postid);
            
            if (res.estado) {
                corazonEl.classList.add("text-danger"); // Color rojo
                corazonEl.classList.add("heartBeat"); // efecto de animate.css
            } else {
                corazonEl.classList.remove("text-danger");
                corazonEl.classList.remove("heartBeat");
            }
            contadorEl.innerHTML = res.numLikes;
            
        })
}