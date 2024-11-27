document.addEventListener("DOMContentLoaded", () => {
    const eventSource = new EventSource('../sse.php');
    eventSource.addEventListener('webhook-event', (event) => {
        try {
            const data = JSON.parse(event.data);
            const today = new Date().toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            });
            Swal.fire({
                title: `¡Bienvenido ${data.nombre}!`,
                text: `Disfruta de todo el contenido a partir de hoy ${today}!. Inicia sesión y sumérgete en el maravilloso mundo de la lectura.`,
                icon: "success",
                confirmButtonColor: "#3085d6",
                confirmButtonText: "Aceptar",
            });
        } catch (error) {
            console.error('Error procesando el evento:', error);
        }
    });
    eventSource.addEventListener('no-events', () => {
        console.log('No hay eventos disponibles en este momento.');
    });
    eventSource.onerror = () => {
    };
});
