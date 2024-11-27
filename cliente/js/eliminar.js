function showDeleteForm() {
    const deleteForm = document.getElementById('delete-form');
    deleteForm.style.display = 'block';
}

function closeDeleteForm() {
    const deleteForm = document.getElementById('delete-form');
    deleteForm.style.display = 'none';
}


const eliminarButton = document.getElementById('Eliminar');

function eliminarCuenta() {
    //Recuperar datos
    const pass = document.getElementById('passE').value;
    // Validacion de datos
    if (!pass.trim()) {
        alerta = {
          tipo: "simple",
          titulo: "Error",
          texto: "Debe llenar los campos solicitados",
          icono: "error",
        };
        return alertas_ajax(alerta);  
    }
    // Envía los datos con fetch
    fetch('php/eliminar.php', {
        method: 'POST',
        body: JSON.stringify({pass: pass}),
        headers: {
            'Content-Type': 'application/json'
        }
    }).then(response => response.json())
      .then(data => alertas_ajax(data))
      .catch(error => console.error("Error:", error));
}

// Escuchar el evento de clic en el botón
eliminarButton.addEventListener('click', function(event) {
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
        eliminarCuenta();
      }
    });
});