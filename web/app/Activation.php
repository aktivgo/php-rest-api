<?php

namespace aktivgo\PhpRestApi\app;

use aktivgo\PhpRestApi\database\Database;
use Firebase\JWT\JWT;

class Activation
{
    private static string $key = 'jrgdfklgicohvbaWD';

    public static function generateToken($id): string
    {
        return JWT::encode($id, self::$key);
    }

    private static function decodeToken($jwt): string
    {
        return JWT::decode($jwt, self::$key, array('HS256'));
    }

    public static function sendMessage($email, $token)
    {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";
        $headers .= "To: <$email>\r\n";
        $headers .= "From: <mail@example.com>\r\n";
        $message = '
                <html lang="ru">
                <head>
                <title>Подтвердите Email</title>
                </head>
                <body>
                <p>Чтобы подтвердить Email, перейдите по <a href="http://task2.loc/activation?hash=' . $token . '">ссылке</a></p>
                </body>
                </html>
                ';
        if (mail($email, "Подтвердите Email на сайте", $message, $headers)) {
            echo 'Подтвердите на почте';
        }
    }

    public static function confirmEmail($token)
    {
        $id = self::decodeToken($token);

        $db = Database::getConnection();
        $sth = $db->prepare("update users set status = true where id = $id");
        $sth->execute();

        echo 'Почта успешно подтверждена!';

        http_response_code(202);
    }
}