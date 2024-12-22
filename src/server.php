<?php

// Настройки
$server_ip = '127.0.0.1'; // IP-адрес сервера
$server_port = 1337; // Порт сервера

// Создание UDP-сокета
$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
if ($socket === false) {
    die("Не удалось создать сокет: " . socket_strerror(socket_last_error()));
}

// Привязка сокета к IP и порту
if (socket_bind($socket, $server_ip, $server_port) === false) {
    die("Не удалось привязать сокет: " . socket_strerror(socket_last_error()));
}

echo "Сервер запущен и ожидает данные...\n";

while (true) {
    $buffer = '';
    $remote_ip = '';
    $remote_port = 0;

    // Получение данных
    $bytes_received = socket_recvfrom($socket, $buffer, 1024, 0, $remote_ip, $remote_port);
    if ($bytes_received === false) {
        echo "Ошибка при получении данных: " . socket_strerror(socket_last_error()) . "\n";
        continue;
    }

    // Распоковка данных
    $data = unpack('Cclient_id/Ccommand_id/Npayload_length', $buffer);
    $payload = rtrim(substr($buffer, 6, $data['payload_length']), "0"); 
    $timestamp = unpack('Ntimestamp', substr($buffer, 6 + $data['payload_length'], 4))['timestamp']; 
    $received_crc = unpack('Ncrc', substr($buffer, 6 + $data['payload_length'] + 4))['crc']; 

    // Проверка контрольной суммы
    $calculated_crc = crc32($data['client_id'] . $data['command_id'] . $data['payload_length'] . substr($buffer, 6, $data['payload_length']) . $timestamp);

    if ($received_crc !== $calculated_crc) {
        die("Контрольная сумма не совпадает!");
    }

    echo "Получены данные от клиента: client_id={$data['client_id']}, command_id={$data['command_id']}, payload={$payload}, payload_length={$data['payload_length']} timestamp={$timestamp}\n";
}

socket_close($socket);
?>