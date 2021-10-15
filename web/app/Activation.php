<?php

namespace aktivgo\PhpRestApi\app;

use aktivgo\PhpRestApi\database\Database;
use Firebase\JWT\JWT;
use PDO;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Activation
{

    public static function generateToken(string $id): string
    {
        $token = [
            'id' => $id,
            'lifeTime' => time() + 100000
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
            $body = '<H1> Здравствуйте! </H1> <br/> Чтобы подтвердить регистрацию на нашем сайте, пожалуйста, пройдите по <a href="' . 'http://' . $baseUrl . '/users/activation?token=' . $token . '"> ссылке </a> . <br> Если это были не Вы, то просто игнорируйте это письмо. <br/> <br/> <strong>Внимание!</strong> Ссылка действительна 24 часа.';
            $mail->msgHTML($body);

            $mail->send();
        } catch (Exception $e) {
            APP::echoResponseCode([], 404);
            die();
        }
    }

    public static function confirmEmail(string $token)
    {
        $data = self::decodeToken($token);
        $id = $data->id;

        $db = Database::getConnection();

        // Если подтверждённый пользователь снова переходит по ссылке
        $sth = $db->prepare("select * from users where id = :id and status = true");
        $sth->execute(['id' => $id]);
        if ($sth->fetch(PDO::FETCH_ASSOC)) {
            APP::echoResponseCode(['The mail has already been confirmed'], 200);
            die();
        }

        // Если время жизни токена закончилось, удаляем пользователя
        if ($data->lifeTime <= time()) {
            APP::deleteUser(Database::getConnection(), $id);
            APP::echoResponseCode(['The token is invalid'], 204);
            die();
        }

        // Подтверждение почты
        $sth = $db->prepare("update users set status = true where id = :id");
        if ($sth->execute(['id' => $id])) {
            APP::echoResponseCode(['The mail has been successfully confirmed'], 200);
        }
    }
}