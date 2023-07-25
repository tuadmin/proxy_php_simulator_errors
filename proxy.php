<?php
require_once __DIR__ .'/_main.php';
// Obtener el método de la solicitud original (GET, POST, DELETE, etc.)
$method = $_SERVER['REQUEST_METHOD'];
$url = $_SERVER['REQUEST_URI'];
$peticion_url = str_replace("/proxy.php","",$url);
//var_dump($_SERVER);die();
$archivo_peticion = $_SERVER["SCRIPT_NAME"];
if( $archivo_peticion!="/proxy.php" && trim($archivo_peticion,"/")!='' && file_exists( __DIR__. $archivo_peticion) ){
    if(substr($archivo_peticion,-1) == '/'){
        if(file_exists( __DIR__. $archivo_peticion."index.php")){
            $archivo_peticion = $archivo_peticion."index.php";
        } else if (file_exists( __DIR__. $archivo_peticion."index.html")){
            $archivo_peticion = $archivo_peticion."index.html";
        }else{
            echo "SHOW DIRECTORY IS BLOCKED";
            die();
        }
    }
    $partes = explode(".",$archivo_peticion);
    $extension = array_pop($partes);
    $mimetypes = array(
        'css' => 'text/css',
        'js' => 'text/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'webp' => 'image/webp', // Agrega aquí otros tipos MIME y extensiones permitidas
        'html' => 'text/html',
        'php' => 'text/html'
    );
    if(isset($mimetypes[$extension])){
        header("Content-Type: {$mimetypes[$extension]}");
    }
    if($extension=='php'){
        require __DIR__. $archivo_peticion;
    }else{
        readfile(__DIR__. $archivo_peticion);
    }
    

}else{
    $STDERR = fopen('php://stderr', 'w');    
    $_listaAcciones = array();
    $_inicio = new Inicio();
    if(file_exists(FILE_ACCIONES)){
        $lista = json_decode(file_get_contents(FILE_ACCIONES),true);
        foreach($lista as $linea){
            $_listaAcciones[] = jsonToAccion($linea);
        }            
    }
    if(file_get_contents(FILE_INICIO)){
        $inicio = json_decode(file_get_contents(FILE_INICIO),true);
        foreach($inicio as $k=>$v){
            $_inicio->$k = $v;
        }
    }
    if($_inicio->sleep>0){
        sleep($_inicio->sleep);
    }
    // Obtener la URL de destino
    $destination_url = rtrim($_inicio->redirige,'/').$url ; // Cambiar esto con la URL de destino deseada
    foreach($_listaAcciones as $accion){
        if($accion->activo){
            fwrite($STDERR,var_export([$peticion_url,$accion],true));
            
            if($accion->coincide_url($peticion_url)){
                http_response_code((int) $accion->retornar_httpcode);                
                if($accion->retornar_mimetype){
                    header("Content-Type: {$accion->retornar_mimetype}");
                }
                if($accion->retornar_texto != ""){
                    echo $accion->retornar_texto;
                    
                }
                
                die();
            }
        }
    }
    // Crear una nueva solicitud cURL
    $ch = curl_init();

    // Configurar la URL y otras opciones
    curl_setopt($ch, CURLOPT_URL, $destination_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // Establecer las cabeceras de la solicitud
    $headers = [];
    foreach (getallheaders() as $key => $value) {
        $key = trim($key);
        $n = strtolower($key);
        // para evitar errores al servidor que se envian las peticiones(dejamos que CURL se encargue)
        if($n=='x-forwarded-host' || $n == 'host'){
            continue;
            
        }
        $headers[] = $key . ': ' . $value;
        
    }
    //var_dump($headers);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);    
    // Agregar datos POST si es necesario
    if ($method === 'POST' || $method === 'PUT') {
        
        // Verificar si hay archivos en la solicitud
        if (!empty($_FILES)) {
            // Agregar los archivos al cuerpo de la solicitud
            $post_fields = $_POST;
            foreach ($_FILES as $name => $file) {
                $post_fields[$name] = new CURLFile($file['tmp_name'], $file['type'], $file['name']);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        } else {
            $raw_data = file_get_contents('php://input');
            $content_type = $_SERVER['CONTENT_TYPE'];
            // Si no hay archivos, usar los datos POST normales
            curl_setopt($ch, CURLOPT_POSTFIELDS, $raw_data);

            foreach($_listaAcciones as $accion){
                if($accion->activo){
                    
                    if($accion->coincide_texto($raw_data)){
                        http_response_code((int) $accion->retornar_httpcode);                
                        if($accion->retornar_mimetype){
                            header("Content-Type: {$accion->retornar_mimetype}");
                        }
                        if($accion->retornar_texto != ""){
                            echo $accion->retornar_texto;                            
                        }
                        
                        die();
                    }
                }
            }
        }
    }

    // Obtener la respuesta del servidor de destino
    $response = curl_exec($ch);

    // Obtener información adicional sobre la respuesta
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

    // Cerrar la conexión cURL
    curl_close($ch);

    // Establecer las cabeceras de respuesta
    header("Content-Type: $content_type", true, $http_code);
    foreach (curl_getinfo($ch, CURLINFO_COOKIELIST) as $cookie) {
        header("Set-Cookie: $cookie", false);
    }
    //var_dump($response);
    // Devolver el contenido de la respuesta del servidor de destino
    echo $response;
    guardarRespuestaServidor($destination_url,$response,array(
        'http_code'=>$http_code,
        'content_type'=>$content_type,
    ));
    die();
}