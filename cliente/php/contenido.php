<?php
    $data = file_get_contents("php://input");

    if(isset($data))
    {
            session_start();

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
                $datos  = json_decode($data , true);
                $apiUrl = "http://localhost:61182/api/Productos/showProds";

                $categoria = $datos ['categoria'];
                $isbn = $datos ['isbn'];

                // Agregar parámetros a la URL
                $apiUrl .= '/' . $categoria . '/' . $isbn;
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
                $data = json_decode($decodedData['Data'], true);

                echo '<section class="catalogo">';

                foreach ($data as $id => $detalles) {

                    echo '<div class="producto">';
                    echo '<img src=" '. $detalles['Ruta'] .' " alt="Producto 1">';
                    echo '<h2> ' . $id . ' </h2>';
                    unset($detalles['Ruta']);

                    foreach ($detalles as $campo => $valor) {

                        echo '<div class = "alinear">';
                        echo '<p class="descripcion"><b> '. $campo .' :</b>  '.  $valor .' </p>';
                        echo "</div>";
                        
                    }
                    echo "</div>";
                }
                echo "</section>";
                return;
            }
    } else {
        echo "No se recibieron los datos";
        return;
    }
?>
