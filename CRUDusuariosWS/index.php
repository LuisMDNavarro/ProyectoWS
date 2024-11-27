<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Slim\Factory\AppFactory;
    use Slim\Middleware\BodyParsingMiddleware;
    require 'vendor/autoload.php';
    use Base\QUISCO\MyFireBase as DB;
    require_once __DIR__ . '/firebase.php';
    $base = new DB('productosws-743c8-default-rtdb');
    $resp = array(
        'code' => 999,
        'message' => $base->obtainMessage(999),
        'data' => '',
        'status' => 'error'
        );

    $app = AppFactory::create();
    $app->addBodyParsingMiddleware();
    $app->setBasePath("/ws/proyecto/CRUDusuariosWS");

    function validateCard($numTarjeta){
        global $base;
        $numero =str_replace(' ', '', $numTarjeta);
        $suma = 0;
        $longitud = strlen($numero);
        if (!is_numeric($numero)) {
            return false; // Si no es un número, la tarjeta no es válida
        }

        if ($numero == 0) {
            return false; // Si no es un número, la tarjeta no es válida
        }
        
        // Iterar sobre los dígitos desde el final
        for ($i = $longitud - 1; $i >= 0; $i--) {
            $digito = (int) $numero[$i];

            // Determinar si la posición es par desde el final
            if (($longitud - $i) % 2 == 0) {
                $digito *= 2;
                if ($digito > 9) {
                    $digito -= 9;
                }
            }

            // Sumar el dígito al total
            $suma += $digito;
        }

        // Si la suma es divisible por 10, la tarjeta es válida
        if($suma % 10 == 0){
            return true;
        } else {
            return false;
        }
    }

    $app->post('/setUser', function(Request $request, Response $response, $args){
        $reqPost = $request->getParsedBody();
        $nombre = $reqPost["nombre"];
        $correo = $reqPost["correo"];
        $pass = $reqPost["pass"];
        $numTarjeta = $reqPost["numTarjeta"];
        $titular = $reqPost["titular"];
        $cvv = $reqPost["cvv"];
        $fechaExp = $reqPost["fechaExp"];

        global $base;
        global $resp;

        if(!$base->isEmailInDB($correo))
        {
            if(validateCard($numTarjeta))
            {
                if($base->insertUser($nombre, $correo, $pass, $numTarjeta, $titular, $cvv, $fechaExp)){
                    $resp['code'] = 206;
                    $resp['message'] = $base->obtainMessage(206);
                    $resp['status'] = "success";

                    // Definir los datos para el webhook
                    $data = [
                        'nombre' => $nombre,
                        'correo' => $correo,
                        'pass' => $pass,
                        'numTarjeta' => $numTarjeta,
                        'titular' => $titular,
                        'cvv' => $cvv,
                        'fechaExp' => $fechaExp,
                        'type' => 1
                    ];

                    // Enviar los datos al webhook
                    $ch = curl_init("http://localhost/ws/proyecto/webhook.php");
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

                    $webhookResponse = curl_exec($ch);
                    curl_close($ch);
                }
            } else  {
                $resp['code'] = 306;
                $resp['message'] = $base->obtainMessage(306);
            }
        } else {
            $resp['code'] = 502;
            $resp['message'] = $base->obtainMessage(502);
        }
        $response->getBody()->write(json_encode($resp, JSON_PRETTY_PRINT));
        return $response;
    });

    $app->get('/getUser[/{user}]', function (Request $request, Response $response, $args) {
        $user = $args["user"];

        global $base;
        global $resp;
        if($base->isUserInDB($user)){
            $resp['code'] = 208;
            $resp['message'] = $base->obtainMessage(208);
            $resp['data'] = $base->infoUser($user); //Arreglo, var_dump() para pruebas
            $resp['status'] = "success";
        } else {
            $resp['code'] = 500;
            $resp['message'] = $base->obtainMessage(500);
        }
        $response->getBody()->write(json_encode($resp, JSON_PRETTY_PRINT));
        return $response;
    });

    $app->put('/updateSub', function (Request $request, Response $response, $args) {
        $reqPut = $request->getParsedBody();
        $user = $reqPut["user"];
        $numTarjeta = $reqPut["numTarjeta"];
        $titular = $reqPut["titular"];
        $cvv = $reqPut["cvv"];
        $fechaExp = $reqPut["fechaExp"];
        global $base;
        global $resp;
        if($base->isUserInDB($user)){
            if(validateCard($numTarjeta))
            {
                if($base->updateSub($user, $numTarjeta, $titular, $cvv, $fechaExp)){
                    $resp['code'] = 209;
                    $resp['message'] = $base->obtainMessage(209);
                    $resp['status'] = "success";
                }
            } else  {
                $resp['code'] = 306;
                $resp['message'] = $base->obtainMessage(306);
            }
        } else {
            $resp['code'] = 500;
            $resp['message'] = $base->obtainMessage(500);
        }
        $response->getBody()->write(json_encode($resp, JSON_PRETTY_PRINT));
        return $response;
    });

    $app->delete('/deleteUser', function (Request $request, Response $response, $args) {
        $reqDel = $request->getParsedBody();
        $user = $reqDel["user"];
        $pass = $reqDel["pass"];
        global $base;
        global $resp;
        if($base->isUserInDB($user)){
            if($base->obtainPassword($user) == md5($pass)){
                if($base->deleteUser($user)){
                    $resp['code'] = 210;
                    $resp['message'] = $base->obtainMessage(210);
                    $resp['status'] = "success";
                }
            } else {
                $resp['code'] = 501;
                $resp['message'] = $base->obtainMessage(501);
            }
        } else {
            $resp['code'] = 500;
            $resp['message'] = $base->obtainMessage(500);
        }
        $response->getBody()->write(json_encode($resp, JSON_PRETTY_PRINT));
        return $response;
    });

    $app->post('/login', function (Request $request, Response $response, $args) {
        $reqPost = $request->getParsedBody();
        $email = $reqPost["email"];
        $pass = $reqPost["pass"];
        global $base;
        global $resp;
        if($base->isEmailInDB($email)){
            if($base->obtainUser($email))
            {
                $user = $base->obtainUser($email);
                if($base->obtainPassword($user) == md5($pass)){
                    $resp['code'] = 000;
                    $resp['message'] = "Bienvenido!";
                    $resp['data'] = $user;
                    $resp['status'] = "success";
                } else {
                    $resp['code'] = 501;
                    $resp['message'] = $base->obtainMessage(501);
                }
            } else {
                $resp['code'] = 500;
                $resp['message'] = $base->obtainMessage(500);
            }
        } else {
            $resp['code'] = 500;
            $resp['message'] = $base->obtainMessage(500);
        }
        $response->getBody()->write(json_encode($resp, JSON_PRETTY_PRINT));
        return $response;
    });

    $app->run();
?>
