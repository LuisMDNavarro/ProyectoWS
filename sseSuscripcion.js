document.addEventListener("DOMContentLoaded", () => {
    const eventSource = new EventSource("../sseSuscripcion.php");
    eventSource.addEventListener("webhook_type3", (event) => {
        const data = JSON.parse(event.data);
        console.log("Evento recibido del archivo webhook_type3.txt:", data);
        const container = document.getElementById("updates");
        const newUpdate = document.createElement("p");
        newUpdate.textContent = `Suscripción Vigente`;
        Swal.fire({
            title: "Estado de tu suscripción",
            text:  `${data.Message}`,
            icon: `${data.Status}`,
            confirmButtonColor: "#3085d6",
            confirmButtonText: "Aceptar",
        });
        container.appendChild(newUpdate);
    });
    eventSource.onerror = () => {
        console.error("Error en la conexión SSE");
    };
});