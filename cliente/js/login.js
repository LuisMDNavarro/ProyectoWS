
const loginButton = document.getElementById('Ingresar');

function loginUser() {
    //Recuperar datos
    const email = document.getElementById('correo').value;
    const pass = document.getElementById('pass').value;

    // Validacion de datos
    if (!email.trim() || !pass.trim()) {
        alerta = {
            tipo: "simple",
            titulo: "Error",
            texto: "Debe llenar los campos solicitados",
            icono: "error",
        };
        return alertas_ajax(alerta);  
    }

    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

    if (!emailRegex.test(email)) {
      alerta = {
        tipo: "simple",
        titulo: "Error",
        texto: "El correo no tiene el formato correcto",
        icono: "error",
        };
        return alertas_ajax(alerta);  
    }

    // Envía los datos con fetch
    fetch('php/login.php', {
        method: 'POST',
        body: JSON.stringify({email: email, pass: pass}),
        headers: {
            'Content-Type': 'application/json'
        }
    }).then(response => response.json())
      .then(data =>alertas_ajax(data))
      .catch(error => console.error("Error:", error));
}

// Escuchar el evento de clic en el botón
loginButton.addEventListener('click', function(event) {
    event.preventDefault();  
    loginUser();
});