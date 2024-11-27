<?php
    session_start();
    $usuario = $_SESSION['usuario'] ?? '0';

    if(isset($_SESSION['usuario']))
    {
        $apiUrl = "http://localhost/ws/proyecto/CRUDusuariosWS/getUser";

        // Agregar parámetros a la URL
        $apiUrl .= '/' . $usuario;

        $ch = curl_init($apiUrl);

        // Configurar cURL para GET
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, []);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo "Error: " . curl_error($ch);
        }
        curl_close($ch);
        $decodedData  = json_decode($response , true);
        $data = $decodedData['data'];
        echo json_encode($data);
        return;
    } else {
        echo "Usuario no encontrado";
        return;
    }
?>
