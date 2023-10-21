<?php

function generateRandomString($length)
{
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $randomString;
}

foreach (range(0, 50) as $i) {
    echo $i.' '.generateRandomString(rand(1, 100)).PHP_EOL;
    usleep(rand(50_000, 250_000));
}
