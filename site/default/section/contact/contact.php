<?php

/** @var Site $site */

require SERVER_PATH_FUNCTION . 'data.php';

$email    = $_POST['email'];
$phone    = $_POST['phone'];
$message  = $_POST['message'];
$response = 'INCOMPLETE';

if (filter_var($email, FILTER_VALIDATE_EMAIL) &&
    $phone !== '' &&
    $message !== '')
{

    site_data_append(
        $site,
        'contacts',
        [
            [
                'date'    => date('Y-m-d H:i:s'),
                'email'   => $email,
                'phone'   => $phone,
                'message' => $message,
            ],
        ]
    );

    $response = 'SENT';
}

return $response;
