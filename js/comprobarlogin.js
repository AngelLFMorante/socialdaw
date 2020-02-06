
function comprobarlogin(){
    var validarLogin = document.getElementById("login").value; 
   var loginExistente = document.getElementById("loginExistente");

   fetch(URL_PATH+"/existe/"+validarLogin,{
   method: "GET",
   headers: {
       "Accept": "application/json"
}
   })
   .then(res=>res.json())
   /* console.log(res) */
   .then(data=>{
        /* console.log(data);  */
       if(data == "existe"){
        
           loginExistente.innerHTML=`
           <div class="alert alert-danger" role="alert">
                   Login existente, porfavor cambielo.
               </div>
       `
            error = true;
       }else{
           loginExistente.innerHTML=`
           <div class="alert alert-primary" role="alert">
                   Login correcto
               </div>
       `
       error = false;
       }
       
   })

  }