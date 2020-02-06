function validarformulario(){
 
    
   var login = document.getElementById("login").value;
   var pass1 = document.getElementById("pass1").value;
   var pass2 = document.getElementById("pass2").value;
   var nombre = document.getElementById("nombre").value;
   var email = document.getElementById("email").value;

   var errLogin = document.getElementById("loginExiste");
   var errPass1 = document.getElementById("errPass1");
   var errPass2 = document.getElementById("errPass2");
   var errNombre = document.getElementById("errNombre");
   var errEmail = document.getElementById("errEmail");



   if(pass1 != pass2){
      errPass1.innerHTML=`
      <div class="alert alert-danger" role="alert">
                   Las password no coinciden.
               </div>
               `
               console.log(pass1 +" "+ pass2);
      return false;
   }else if(nombre === ""){
      errNombre.innerHTML=`
      <div class="alert alert-danger" role="alert">
                   El campo nombre está vacio.
               </div>
               `
      return false;
   }else if(email === ""){
      errEmail.innerHTML=`
      <div class="alert alert-danger" role="alert">
      El campo email está vacio.
               </div>
               `
      return false;
   }else if(login === ""){
      errLogin.innerHTML=`
      <div class="alert alert-danger" role="alert">
      El campo login está vacio.
               </div>
               `
      return false;
   }else if(pass1 === ""){
      errPass1.innerHTML=`
      <div class="alert alert-danger" role="alert">
      Algun campo password está vacio.
               </div>
               `
      return false;
   }else if(pass2 === ""){
      errPass2.innerHTML=`
      <div class="alert alert-danger" role="alert">
      Algun campo password está vacio.
               </div>
               `
      return false;
   }
      return true;
              

   }


