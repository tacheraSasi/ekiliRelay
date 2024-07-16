<?php
// api/sendEmail.php

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function logMessage($message) {
    file_put_contents('../logs/email.log', $message . PHP_EOL, FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['to']) && isset($data['subject']) && isset($data['message'])) {
        $to = $data['to'];
        $subject = $data['subject'];
        $message = $data['message'];
        $headers = isset($data['headers']) ? $data['headers'] : '';

        if (!validateEmail($to)) {
            $response = ['status' => 'error', 'message' => 'Invalid email address.'];
            echo json_encode($response);
            logMessage(json_encode($response));
            exit;
        }

        if (mail($to, $subject, $message, $headers)) {
            $response = ['status' => 'success', 'message' => 'Email sent successfully.'];
            echo json_encode($response);
            logMessage(json_encode($response));
        } else {
            $response = ['status' => 'error', 'message' => 'Failed to send email.'];
            echo json_encode($response);
            logMessage(json_encode($response));
        }
    } else {
        $response = ['status' => 'error', 'message' => 'Invalid request.'];
        echo json_encode($response);
        logMessage(json_encode($response));
    }
} else {
    $response = ['status' => 'error', 'message' => 'Invalid request method.'];
    echo json_encode($response);
    logMessage(json_encode($response));
}
?>
