<?php

$email    = $_POST['email'];
$phone    = $_POST['phone'];
$message  = $_POST['message'];
$response = 'INCOMPLETE';

if (filter_var($email, FILTER_VALIDATE_EMAIL) &&
    $phone !== '' &&
    $message !== '')
{
    if (mail(
        'romain.weeger@gmail.com',
        'Contact Respon.site',
        'Voici un nouveau message de : ' . PHP_EOL .
        'Tel : ' . $phone . PHP_EOL .
        'E-mail : ' . $email . PHP_EOL .
        'Message : ' . PHP_EOL . PHP_EOL . $message,
        'From: noreply@respon.site' . "\r\n" .
        'Reply-To: noreply@respon.site' . "\r\n" .
        'X-Mailer: PHP/' . phpversion()
    ))
    {
        $response = 'SENT';
    }
    else
    {
        $response = 'NOT_SENT';
    }
}

echo $response;
