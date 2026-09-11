<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$config_path = __DIR__ . '/config.local.php';
if (!is_file($config_path)) {
    http_response_code(503);
    exit('La messagerie SMTP nâ€™est pas encore configurÃ©e.');
}

$smtp = require $config_path;
if (!is_array($smtp)) {
    http_response_code(500);
    exit('Configuration SMTP invalide.');
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');
$website = trim($_POST['website'] ?? '');

$string_length = static function (string $value): int {
    return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
};

if ($website !== '') {
    http_response_code(400);
    exit('RequÃªte invalide.');
}

if ($name === '' || $subject === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)
    || $string_length($name) > 100 || $string_length($subject) > 150 || $string_length($message) > 5000) {
    http_response_code(400);
    exit('Veuillez remplir correctement tous les champs.');
}

if (empty($smtp['host']) || empty($smtp['username']) || empty($smtp['password']) || empty($smtp['from_email'])) {
    http_response_code(503);
    exit('La messagerie SMTP nâ€™est pas encore configurÃ©e.');
}

require_once __DIR__ . '/../vendor/autoload.php';

try {
    $mailer = new PHPMailer(true);
    $mailer->isSMTP();
    $mailer->Host = (string) $smtp['host'];
    $mailer->SMTPAuth = true;
    $mailer->Username = (string) $smtp['username'];
    $mailer->Password = (string) $smtp['password'];
    $mailer->SMTPSecure = (string) ($smtp['encryption'] ?? PHPMailer::ENCRYPTION_STARTTLS);
    $mailer->Port = (int) ($smtp['port'] ?? 587);
    $mailer->CharSet = PHPMailer::CHARSET_UTF8;

    $mailer->setFrom((string) $smtp['from_email'], (string) ($smtp['from_name'] ?? 'Portfolio Becaye Doumbouya'));
    $mailer->addAddress((string) ($smtp['to_email'] ?? $smtp['from_email']));
    $mailer->addReplyTo($email, $name);
    $mailer->isHTML(false);
    $mailer->Subject = $subject;
    $mailer->Body = "Nom : {$name}\nEmail : {$email}\n\n{$message}";
    $mailer->send();

    echo 'OK';
} catch (Exception $exception) {
    error_log('Erreur SMTP du formulaire de contact : ' . $exception->getMessage());
    http_response_code(500);
    exit('Le message nâ€™a pas pu Ãªtre envoyÃ© pour le moment.');
}

