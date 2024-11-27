<?php
    session_start();
    // Paso 1: Eliminar todas las variables de sesión
    session_unset();

    // Paso 2: Destruir la sesión
    session_destroy();

    // Paso 3: (Opcional) Eliminar la cookie de sesión en el cliente
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    header("Location: ../index.html");
    exit;
?>
