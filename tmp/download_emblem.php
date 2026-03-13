<?php
$url = 'https://upload.wikimedia.org/wikipedia/pt/thumb/9/90/Emblema_Desbravadores.png/300px-Emblema_Desbravadores.png';
$options = [
    'http' => [
        'method' => 'GET',
        'header' => 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko)'
    ]
];
$context = stream_context_create($options);
$data = file_get_contents($url, false, $context);
if ($data) {
    file_put_contents('d:/1. Clientes/50. DesbravaHub/public/assets/images/escudo-desbravador.png', $data);
    echo "Success: downloaded " . strlen($data) . " bytes";
} else {
    echo "Error downloading file";
}
