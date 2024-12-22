<?php

$server_ip = '127.0.0.1'; 
$server_port = 1337; 


$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
if ($socket === false) {
    die("Не удалось создать сокет: " . socket_strerror(socket_last_error()));
}

$client_id = 1; 
$command_id = 2;
$payload = "Hello, UDP server!"."0"; 
$payload_length = strlen($payload);
$timestamp = time(); 

$data_to_send = pack('CCN', $client_id, $command_id, $payload_length) . $payload . pack('N', $timestamp);


$crc = crc32($client_id . $command_id . $payload_length . $payload . $timestamp);
$data_to_send .= pack('N', $crc); 

$bytes_sent = socket_sendto($socket, $data_to_send, strlen($data_to_send), 0, $server_ip, $server_port);
if ($bytes_sent === false) {
    die("Ошибка при отправке данных: " . socket_strerror(socket_last_error($socket)));
}

echo "Данные отправлены на сервер.\n";

socket_close($socket);
?>