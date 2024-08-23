<?php
# Including the configuration file for the database connection
include_once './config.php';

# Allowing cross-origin requests (CORS)
header("Access-Control-Allow-Origin: *");

# Setting the content type to JSON
header('Content-Type: application/json');

# Function for validating an email address
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

# Function for logging messages to a file
function logMessage($message) {
    file_put_contents('../logs/email.log', $message . PHP_EOL, FILE_APPEND);
}

# Checking if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    # Decoding the incoming JSON payload into an associative array
    $data = json_decode(file_get_contents('php:#input'), true);

    # Checking if the required parameters are present
    if (isset($data['to'], $data['subject'], $data['message'], $data['apikey'])) {

        # Escaping the API key to prevent SQL injection
        $apikey = mysqli_real_escape_string($conn, $data['apikey']);
        
        # Querying the user associated with the provided API key
        $select = mysqli_query($conn, "SELECT user FROM data WHERE apikey = '$apikey';");
        
        # Verifying if the API key is valid
        if ($select && mysqli_num_rows($select) > 0) {
            # Retrieving the user ID from the query result
            $user_id = mysqli_fetch_array($select)['user'];

            #add a request in the db
            $select_num_req = mysqli_query($conn,"select requests from data where user = '$user_id'");
            $num_req = mysqli_fetch_array($select_num_req)['requests'];
            $num_req++;
            
            # Querying the user's information based on the unique ID
            $select_user = mysqli_query($conn, "SELECT name, email FROM users WHERE unique_id = '$user_id';");
            $user_info = mysqli_fetch_array($select_user);

            $user_name = $user_info['name'];
            $user_email = $user_info['email'];

            # Setting the email fields from the input data
            $to = $data['to'];
            $subject = $data['subject'];
            $message = $data['message'];
            $headers = isset($data['headers']) ? $data['headers'] : "From: $user_name <$user_email>";

            # Validating the recipient's email address
            if (!validateEmail($to)) {
                $response = ['status' => 'error', 'message' => 'Invalid email address.'];
                echo json_encode($response);
                logMessage(json_encode($response));
                exit;
            }

            # Attempting to send the email
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
            # Responding with an error if the API key is invalid
            $response = ['status' => 'error', 'message' => 'Invalid API key. Visit https://relay.ekilie.com to get the correct one.'];
            echo json_encode($response);
        }

    } else {
        # Responding with an error if required parameters are missing
        $response = ['status' => 'error', 'message' => 'Missing parameters (to, subject, message, or apikey).'];
        echo json_encode($response);
        logMessage(json_encode($response));
    }
    
} else {
    # Responding with an error if the request method is not POST
    $response = ['status' => 'error', 'message' => 'Invalid request method. Only POST is allowed.'];
    echo json_encode($response);
    logMessage(json_encode($response));
}
?>
