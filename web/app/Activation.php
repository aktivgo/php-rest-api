<?php

namespace aktivgo\PhpRestApi\app;

use aktivgo\PhpRestApi\database\Database;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Activation
{
    private static string $key = 'jrgdfklgicohvbaWD';

    public static function generateToken($id): string
    {
        $token = [
            'exp' => time() + 60,
            'id' => $id
        ];
        return JWT::encode($token, self::$key);
    }

    private static function decodeToken($jwt): object
    {
        return JWT::decode($jwt, self::$key, array('HS256'));
    }

    public static function sendMessage($email, $token)
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

            $mail->Host = 'ssl://smtp.yandex.ru';
            $mail->Port = 465;
            $mail->Username = 'vladko4kin1@yandex.ru';
            $mail->Password = 'Kasper0809_5';

            $mail->setFrom('vladko4kin1@yandex.ru', 'task2');
            $mail->addAddress($email);

            $mail->Subject = 'Подтвердите регистрацию на сайте';
            $body = '<H1> Здравствуйте! </H1> <br/> Чтобы подтвердить регистрацию на нашем сайте, пожалуйста, пройдите по <a href="'.'http://task2.loc/users/activation?hash='.$token .'"> ссылке </a> . <br> Если это были не Вы, то просто игнорируйте это письмо. <br/> <br/> <strong>Внимание!</strong> Ссылка действительна 30 секунд.';
            $mail->msgHTML($body);

            $mail->send();
        } catch (Exception $e) {
            APP::echoResponseCode([], 404);
            die();
        }
    }

    public static function confirmEmail($token)
    {
        try {
            $data = self::decodeToken($token);
            $id = $data->id;

            $db = Database::getConnection();
            $sth = $db->prepare("update users set status = true where id = $id and status = false");
            if($sth->execute()) {
                APP::echoResponseCode('Почта успешно подтверждена!', 200);
                return;
            }
            APP::echoResponseCode('Почта уже подтверждена!', 200);
        } catch (ExpiredException $e) {
            APP::echoResponseCode('Ссылка больше не действительна', 404);
            die();
        }
    }
}