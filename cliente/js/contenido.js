const searchButton = document.getElementById('buscar');
const filterButton = document.getElementById('filtros');

function loadContent() {
    //Recuperar datos
    categoria = document.getElementById('categoria').value;
    isbn = document.getElementById('isbn').value;

    // Validacion de datos
    if (!categoria.trim()) {
        categoria = "0";
    }
    if (!isbn.trim()) {
        isbn = "0";
    }

    const contenido = document.getElementById('contenido');

    // Envía los datos con fetch
    fetch('php/contenido.php', {
        method: 'POST',
        body: JSON.stringify({categoria: categoria, isbn: isbn}),
        headers: {
            'Content-Type': 'application/json'
        }
    }).then(response => response.text())
      .then(data =>contenido.innerHTML  = data)
      .catch(error => console.error("Error:", error));
}

document.addEventListener('DOMContentLoaded', function() {
    loadContent();
});

// Escuchar el evento de clic en el botón
searchButton.addEventListener('click', function(event) {
    event.preventDefault();  
    loadContent();
});

// Escuchar el evento de clic en el botón
filterButton.addEventListener('click', function(event) {
    event.preventDefault();  
    loadContent();
});