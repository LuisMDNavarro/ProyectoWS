document.addEventListener("DOMContentLoaded", () => {
    const eventSource = new EventSource("../sseContenido.php");
    eventSource.addEventListener("webhook_type2", (event) => {
        const data = JSON.parse(event.data);
        console.log("Evento recibido del archivo webhook_type2.txt:", data);
        const container = document.getElementById("updates");
        const newUpdate = document.createElement("p");
        newUpdate.textContent = `Nueva actualización: ISBN=${data.ISBN}, Nombre=${data.Nombre}, Categoría=${data.Categoria}, Fecha=${data.FechaRegistro}`;
        Swal.fire({
            title: `¡Nuevo contenido!`,
            text: `${data.Nombre}`,
            icon: "success",
            confirmButtonColor: "#3085d6",
            confirmButtonText: "Aceptar",
        });
        container.appendChild(newUpdate);
    });
    eventSource.onerror = () => {
        console.error("Error en la conexión SSE");
    };
});