
const renovarButton = document.getElementById('Renovar');

function renovarSub() {
    //Recuperar datos
    const numTarjeta = document.getElementById('card-number').value;
    const titular = document.getElementById('card-holder').value;
    const cvv = document.getElementById('card-cvv').value;
    const fechaExp = document.getElementById('card-expiry').value;
    // Validacion de datos
    if (!numTarjeta.trim() || !titular.trim() || !cvv.trim() || !fechaExp.trim()) {
        alerta = {
          tipo: "simple",
          titulo: "Error",
          texto: "Debe llenar los campos solicitados",
          icono: "error",
        };
        return alertas_ajax(alerta);  
    }

    const fullNameRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ'-]+\s[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ'-]+(\s[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ'-]+)*$/;
    const cardNumberRegex = /^(?:4\d{3}(\s?\d{4}){3}|5[1-5]\d{2}(\s?\d{4}){3}|3[47]\d{2}(\s?\d{6}\s?\d{5})|6(?:011|5\d{2})(\s?\d{4}){3})$/;
    const cvvRegex = /^[0-9]{3,4}$/;
    const expirationDateRegex = /^(0[1-9]|1[0-2])\/?([0-9]{2})$/;

    if (!fullNameRegex.test(titular)) {
        alerta = {
          tipo: "simple",
          titulo: "Error",
          texto: "El titular no tiene el formato correcto",
          icono: "error",
        };
        return alertas_ajax(alerta);  
      }

    if (!cardNumberRegex.test(numTarjeta)) {
        alerta = {
          tipo: "simple",
          titulo: "Error",
          texto: "El numero de tarjeta no tiene el formato correcto",
          icono: "error",
        };
        return alertas_ajax(alerta);  
      }

      if (!cvvRegex.test(cvv)) {
        alerta = {
          tipo: "simple",
          titulo: "Error",
          texto: "El cvv no tiene el formato correcto",
          icono: "error",
        };
        return alertas_ajax(alerta);   
      }

      if (!expirationDateRegex.test(fechaExp)) {
        alerta = {
          tipo: "simple",
          titulo: "Error",
          texto: "La fecha de expiracion no tiene el formato correcto",
          icono: "error",
        };
        return alertas_ajax(alerta);   
      }
    // Envía los datos con fetch
    fetch('php/actualizar.php', {
        method: 'POST',
        body: JSON.stringify({numTarjeta: numTarjeta, titular: titular, cvv: cvv, fechaExp:fechaExp}),
        headers: {
            'Content-Type': 'application/json'
        }
    }).then(response => response.json())
      .then(data => alertas_ajax(data))
      .catch(error => console.error("Error:", error));
}

// Escuchar el evento de clic en el botón
renovarButton.addEventListener('click', function(event) {
    event.preventDefault();  
    Swal.fire({
      title: "¿Estás seguro?",
      text: "El fomulario se enviará",
      icon: "question",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Si, enviar",
      cancelButtonText: "No, cancelar",
    }).then((result) => {
      if (result.isConfirmed) {
        renovarSub();
      }
    });
});