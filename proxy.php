<?php
// Obtener el método de la solicitud original (GET, POST, DELETE, etc.)
$method = $_SERVER['REQUEST_METHOD'];

// Obtener la URL de destino
$destination_url = 'https://www.ejemplo.com/destino'; // Cambiar esto con la URL de destino deseada

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
    $headers[] = $key . ': ' . $value;
}
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// Agregar datos POST si es necesario
if ($method === 'POST') {
    curl_setopt($ch, CURLOPT_POSTFIELDS, $_POST);
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

// Devolver el contenido de la respuesta del servidor de destino
echo $response;
?>
