<?php
    session_start();
    $usuario = $_SESSION['usuario'] ?? '0';
    $data = file_get_contents("php://input");

    if(isset($data))
    {
        $datos = json_decode($data, true);
        $datos['user'] = $usuario;
        $apiUrl = "http://localhost/ws/proyecto/CRUDusuariosWS/updateSub";
        
        $ch = curl_init($apiUrl);

       // Configurar cURL para PUT
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        
        $response = curl_exec($ch);
        
        // Manejo de errores
        if (curl_errno($ch)) {
            $alerta = [
                "tipo" => "redireccionar",
                "titulo" => "Renovacion fallida",
                "texto" =>  curl_error($ch),
                "icono" => "error",
                "url" => "perfil.html"
            ];
            echo json_encode($alerta);
            return;
        }
        curl_close($ch);
        $decodedData  = json_decode($response , true);
        $estado = $decodedData ['status'];
        $mensaje=$decodedData ['message'];
        if($estado == "success")
        {
            $alerta = [
                "tipo" => "redireccionar",
                "titulo" => $estado,
                "texto" =>  $mensaje,
                "icono" => $estado,
                "url" => "quiosko.html"
            ];
        } else {
            $alerta = [
                "tipo" => "simple",
                "titulo" => $estado,
                "texto" =>  $mensaje,
                "icono" => $estado,
            ];
        }
        echo json_encode($alerta);
        return;
    }
    else {
        $alerta = [
            "tipo" => "redireccionar",
            "titulo" => "Renovacion fallida",
            "texto" =>  "No se recibieron los datos",
            "icono" => "error",
            "url" => "perfil.html"
        ];
        echo json_encode($alerta);
        return;
    }
    
?>
