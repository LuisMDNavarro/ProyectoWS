function alertas_ajax(alerta) {
    if (alerta.tipo == "simple") {
      Swal.fire({
        icon: alerta.icono,
        title: alerta.titulo,
        text: alerta.texto,
        confirmButtonText: "Aceptar",
      });
    } else if (alerta.tipo == "redireccionar") {
      Swal.fire({
        icon: alerta.icono,
        title: alerta.titulo,
        text: alerta.texto,
        confirmButtonText: "Aceptar",
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = alerta.url;
        }
      });
    }
  }