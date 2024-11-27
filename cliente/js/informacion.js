function loadInfo() {
    const nombre = document.getElementById('nombre');
    const correo = document.getElementById('correo');
    const numTarjeta = document.getElementById('card-number');
    const titular = document.getElementById('card-holder');
    const cvv = document.getElementById('card-cvv');
    const fechaExp = document.getElementById('card-expiry');
    const fechaInicio = document.getElementById('fechaInicio');
    const fechaFin = document.getElementById('fechaFin');

    // Envía los datos con fetch
    fetch('php/informacion.php', {
        method: 'POST',
        body: "",
        headers: {
            'Content-Type': 'application/json'
        }
    }).then(response => response.json())
      .then(data =>{
        nombre.innerHTML ='Hola ' + data.Nombre;
        correo.innerHTML =data.Correo;
        numTarjeta.value =data.Numero;
        titular.value =data.Titular;
        cvv.value =data.CVV;
        fechaExp.value =data.Fecha;
        fechaInicio.innerHTML = 'Fecha de Inicio: ' + data.Inicio;
        fechaFin.innerHTML = 'Fecha de Fin: ' + data.Fin;
      })
      .catch(error => console.error("Error:", error));
}

document.addEventListener('DOMContentLoaded', function() {
    loadInfo();
});