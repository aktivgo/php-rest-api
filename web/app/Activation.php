<?php

namespace aktivgo\PhpRestApi\app;

use aktivgo\PhpRestApi\database\Database;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Activation
{

    public static function generateToken(string $id): string
    {
        $token = [
            'exp' => time() + 30,
            'id' => $id
        ];
        return JWT::encode($token, $_ENV['KEY']);
    }

    private static function decodeToken(string $token): object
    {
        return JWT::decode($token, $_ENV['KEY'], array('HS256'));
    }

    public static function sendMessage(string $email, string $token)
    {
        $mail = new PHPMailer(true);
        try {
            $mail->CharSet = 'UTF-8';

            $mail->isSMTP();
            $mail->SMTPAuth = true;
            $mail->SMTPDebug = 0;
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            $mail->Host = $_ENV['MAIL_HOST'];
            $mail->Port = 465;
            $mail->Username = $_ENV['MAIL_ADDRESS'];
            $mail->Password = $_ENV['MAIL_PASSWORD'];

            $mail->setFrom($_ENV['MAIL_ADDRESS'], 'task2');
            $mail->addAddress($email);
            $mail->Subject = 'Подтвердите регистрацию на сайте';

            $baseUrl = $_SERVER['NGINX_HOST'];
            $body = '<H1> Здравствуйте! </H1> <br/> Чтобы подтвердить регистрацию на нашем сайте, пожалуйста, пройдите по <a href="' . 'https://' . $baseUrl . '/users/activation?token=' . $token . '"> ссылке </a> . <br> Если это были не Вы, то просто игнорируйте это письмо. <br/> <br/> <strong>Внимание!</strong> Ссылка действительна 24 часа.';
            $mail->msgHTML($body);

            $mail->send();
        } catch (Exception $e) {
            APP::echoResponseCode([], 404);
            die();
        }
    }

    public static function confirmEmail(string $token)
    {
        try {
            $data = self::decodeToken($token);
            $id = $data->id;

            $db = Database::getConnection();
            $sth = $db->prepare("update users set status = true where id = :id and status = false");
            if ($sth->execute(['id' => $id])) {
                APP::echoResponseCode(['Почта успешно подтверждена'], 200);
                return;
            }
            APP::echoResponseCode(['Почта уже подтверждена'], 200);
        } catch (ExpiredException $e) {
            APP::echoResponseCode(['Ссылка больше не действительна'], 404);
            die();
        }
    }
}