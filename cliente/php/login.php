<?php
    $data = file_get_contents("php://input");

    if(isset($data))
    {
        $datos  = json_decode($data , true);
        $apiUrl = "http://localhost/ws/proyecto/CRUDusuariosWS/login";
        
        $ch = curl_init();
        
        // Configurar cURL para una solicitud POST
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($datos));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/x-www-form-urlencoded"]);
        
        $response = curl_exec($ch);
        
        // Manejo de errores
        if (curl_errno($ch)) {
            $alerta = [
				"tipo" => "redireccionar",
				"titulo" => "Login fallido",
				"texto" => curl_error($ch),
				"icono" => "error",
                "url" => "index.html"
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
            session_start();
            $_SESSION['usuario'] = $decodedData['data'];

            $apiUrl = "http://localhost:61182/api/Productos/checkSub";
            $usuario = $_SESSION['usuario'] ?? '0';
            $apiUrl .= '/' . $usuario;
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, []);
            $res = curl_exec($ch);
            $arregloResp = json_decode($res , true);
            $status = $arregloResp['Status'];
            if($status == "success") {
                $alerta = [
                    "tipo" => "redireccionar",
                    "titulo" => $estado,
                    "texto" =>  $mensaje,
                    "icono" => $estado,
                    "url" => "quiosko.html"
                ];
            } else {
                $alerta = [
                    "tipo" => "redireccionar",
                    "titulo" => $estado,
                    "texto" =>  $mensaje,
                    "icono" => $estado,
                    "url" => "perfil.html"
                ];
            }
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
            "titulo" => "Login fallido",
            "texto" => "No se recibieron los datos",
            "icono" => "error",
            "url" => "index.html"
        ];
        echo json_encode($alerta);
        return;
    }
    
?>
