<?php
namespace Base\QUISCO;

class MyFireBase {
    private $proyecto;
    
    public function __construct($project){
        $this->proyecto = $project;
    }

    private function runCurl($collection, $document){
        $url = 'https://'.$this->proyecto.'.firebaseio.com/'.$collection.'/'.$document.'.json';

        $ch =  curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        curl_close($ch);
        //Decode para evitar errores de formato
        return json_decode($response, true);
    }

    private function runCurlInsert($collection, $document, $data){
        $url = 'https://'.$this->proyecto.'.firebaseio.com/'.$collection.'/'.$document.'.json';

        $ch =  curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH" );  // en sustitución de curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        if(curl_errno($ch)){
            curl_close($ch);
            return false;
        } else {
            curl_close($ch);
            return true;
        }
    }

    private function runCurlDelete($collection, $document) {
        $url = 'https://' . $this->proyecto . '.firebaseio.com/' . $collection . '/' . $document . '.json';
    
        // Inicializa cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); // Configura el método DELETE
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
        // Ejecuta la solicitud y almacena la respuesta
        $response = curl_exec($ch);
    
        // Maneja errores
        if (curl_errno($ch)) {
            curl_close($ch);
            return false;
        } else {
            curl_close($ch);
            return true;
        }
    }
    
    private function getNextUserID() {
        $res = $this->runCurl('usuarios', '');
        
        $maxNumber = 0;
    
        if (is_array($res)) {
            foreach (array_keys($res) as $id) {
                // Verifica que el identificador comience con "USER" y seguido por números
                if (preg_match('/^USER(\d+)$/', $id, $matches)) {
                    $number = (int)$matches[1]; // Extrae el número
                    if ($number > $maxNumber) {
                        $maxNumber = $number; // Actualiza el máximo si es necesario
                    }
                }
            }
        }
    
        // Genera el siguiente identificador sumándole uno al mayor número encontrado
        $nextID = 'USER' . str_pad($maxNumber + 1, 2, '0', STR_PAD_LEFT);
        return $nextID;
    }

    public function isEmailInDB($email){
        $res = $this->runCurl('usuarios', '');

        if (is_array($res)) {
            foreach ($res as $user) {
                if (isset($user['Correo']) && $user['Correo'] === $email) {
                    return true; // El correo se encontró en la base de datos
                }
            }
        }
        return false; // El correo no se encontró o $res no es un array
    }

    public function insertUser($nombre, $correo, $pass, $numTarjeta, $titular, $cvv, $fechaExp){
        $userID = $this->getNextUserID();
        //Define zona horaria
        date_default_timezone_set('America/Mexico_City');
        // Fecha de hoy en formato dd-mm-yyyy
        $inicio = (new \DateTime())->format('d-m-Y');
        // Fecha 30 días después de hoy en formato dd-mm-yyyy
        $fin = (new \DateTime())->modify('+30 days')->format('d-m-Y');
        $datosUser = json_encode([
            "Contraseña" => md5($pass),
            "Correo" => $correo,
            "Nombre" => $nombre,
        ]);
        
        $datosCard = json_encode([
            "CVV" => $cvv,
            "Fecha" => $fechaExp,
            "Titular" => $titular,
            "Numero" => $numTarjeta
        ]);
        
        $datosSub = json_encode([
            "Fin" => $fin,
            "Inicio" => $inicio
        ]);
        $user =$this->runCurlInsert('usuarios', $userID, $datosUser);
        $tarjeta = $this->runCurlInsert('tarjetas', $userID, $datosCard);
        $sub = $this->runCurlInsert('suscripciones', $userID, $datosSub);
        if($user && $tarjeta && $sub){
            return true;
        } else {
            return false;
        }
    }

    public function isUserInDB($user){
        return $this->runCurl('usuarios', $user);
    }

    public function infoUser($user){
        $sub = $this->runCurl('suscripciones', $user);
        $tarjeta = $this->runCurl('tarjetas', $user);
        $user = $this->runCurl('usuarios', $user);
        // Asegúrate de que las variables no sean null antes de usarlas en array_merge
        $user = $user ?? [];
        $tarjeta = $tarjeta ?? [];
        $sub = $sub ?? [];
        return array_merge($user, $tarjeta, $sub);
    }

    public function updateSub($user, $numTarjeta, $titular, $cvv, $fechaExp){
        //Define zona horaria
        date_default_timezone_set('America/Mexico_City');
        // Fecha de hoy en formato dd-mm-yyyy
        $inicio = (new \DateTime())->format('d-m-Y');
        // Fecha 30 días después de hoy en formato dd-mm-yyyy
        $fin = (new \DateTime())->modify('+30 days')->format('d-m-Y');
        $datosCard = json_encode([
            "CVV" => $cvv,
            "Fecha" => $fechaExp,
            "Titular" => $titular,
            "Numero" => $numTarjeta
        ]);
        $datosSub = json_encode([
            "Fin" => $fin,
            "Inicio" => $inicio
        ]);
        $tarjeta = $this->runCurlInsert('tarjetas', $user, $datosCard);
        $sub = $this->runCurlInsert('suscripciones', $user, $datosSub);
        if($tarjeta && $sub){
            return true;
        } else {
            return false;
        }
    }

    public function deleteUser($user){
        $usuario = $this->runCurlDelete('usuarios', $user);
        $tarjeta = $this->runCurlDelete('tarjetas', $user);
        $sub = $this->runCurlDelete('suscripciones', $user);
        if($user && $tarjeta && $sub){
            return true;
        } else {
            return false;
        }
    }

    public function obtainUser($email){
        $res = $this->runCurl('usuarios', '');

        if (is_array($res)) {
            foreach ($res as $key => $user) { // Usa $key para obtener la clave del arreglo
                if (isset($user['Correo']) && $user['Correo'] === $email) {
                    return $key; // Retorna la clave del arreglo donde se encuentra el correo
                }
            }
        }
        return false; // El correo no se encontró o $res no es un array
    }

    public function obtainPassword($user){
        $userData = $this->runCurl('usuarios', $user);
        return $userData['Contraseña'] ?? null; // Retorna null si no existe
    }

    public function obtainMessage($code){
        return  $this->runCurl('respuestas', $code);
    }
}
?>
