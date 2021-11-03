<?php

function get_db_config()
{
    if (getenv('IS_IN_HEROKU')) {
        $url = parse_url(getenv("HEROKU_POSTGRESQL_BLACK_URL"));

        return $db_config = [
            'connection' => 'pgsql',
            'host' => $url["host"],
            'database'  => substr($url["path"], 1),
            'username'  => $url["user"],
            'password'  => $url["pass"],
        ];
    } else {
        return $db_config = [
            'connection' => env('DB_CONNECTION', 'mysql'),
            'host' => env('DB_HOST', 'localhost'),
            'database'  => env('DB_DATABASE', 'forge'),
            'username'  => env('DB_USERNAME', 'forge'),
            'password'  => env('DB_PASSWORD', ''),
        ];
    }
}


function get_passport_private_key()
{
        echo getenv('PASSPORT_PRIVATE_KEY');
        return getenv('PASSPORT_PRIVATE_KEY');

}
function get_passport_public_key()
{
    echo getenv('PASSPORT_PUBLIC_KEY');
    return getenv('PASSPORT_PUBLIC_KEY');

}
