<?php
header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: GET, POST');
// header("Access-Control-Allow-Headers: X-Requested-With");
sleep(0.3);
?>

<?php

function emailTokenMatch($email, $token)
{
    if (file_exists("users/" . $email)) {
        if (file_exists("users/$email/token")) {
            $contents = file_get_contents("users/$email/token");
            if (password_verify($token, $contents)) {
                return true;
            }
        }
    }
    return false;
}

function emailPasswordMatch($email, $password)
{
    if (file_exists("users/" . $email)) {
        if (file_exists("users/$email/password")) {
            $contents = file_get_contents("users/$email/password");
            if (password_verify($password, $contents)) {
                return true;
            }
        }
    }
    return false;
}

function unexpiredToken($email, $minutes)
{
    if (file_exists("users/$email")) {
        if (file_exists("users/$email/tokenTimestamp")) {
            $contents = file_get_contents("users/$email/tokenTimestamp");
            $date = date_create();
            $nowTimestamp = date_timestamp_get($date);
            $tokenTimestamp = intval($contents);
            if (($nowTimestamp - $tokenTimestamp) < 60 * $minutes) { //60seconds*minutes
                return true;
            }
        }
    }
    return false;
}

function sendToken($email)
{
    if (!file_exists("users")) {
        mkdir("users", 0777); //should be 0770 when publishing but can be 0777 on locally secured computer
    }
    if (!file_exists("users/$email")) {
        mkdir("users/$email", 0777); //should be 0770 when publishing but can be 0777 on locally secured computer
    }
    $token = bin2hex(random_bytes(16));
    $hashedToken = password_hash($token, PASSWORD_DEFAULT);
    file_put_contents("users/$email/token", $hashedToken);
    $date = date_create();
    $nowTimestamp = date_timestamp_get($date);
    file_put_contents("users/$email/tokenTimestamp", $nowTimestamp);
    mail($email, "requested token", "Here is your requested token: " . $token, "From: AccountManagement");
    return $token;

    //REMOVE for TESTING
    echo($token);
}

?>
<?php
$webPagePart1 = "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta http-equiv='X-UA-Compatible' content='IE=edge'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Account Management Message</title>
    </head>
    <body>";
$webPagePart2 = "</body>
    </html>";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST["do-this"] === "test") {
        $email = strtolower($_POST["email"]);
        if (emailPasswordMatch($email, $_POST["password"])) {
            echo "Credentials verified.";
        } else {
            echo "Credentials are invalid.";
        };
    } else if ($_POST["do-this"] === "update-profile") {
        $email = strtolower($_POST["email"]);
        $token = $_POST["token"];
        if (emailTokenMatch($email, $token) && unexpiredToken($email, 10)) {
            file_put_contents("users/$email/password", password_hash($_POST["password"], PASSWORD_DEFAULT));
            unlink("users/$email/token");
            echo "Request was successful.";
        } else {
            echo "Request was not succuessful.";
        }
    } else if ($_POST["do-this"] === "send-token") { //send a token
        $email = strtolower($_POST["email"]);
        if (unexpiredToken("$email", 10)) {
            //do nothing
        } else {
            $token = sendToken($email);
        }
        echo "A request to send a token to $email has been made.  " .
            "Check your email $email for the token." . // "(testing purposes: $token).  " .
            "Tokens are good for 10 minutes.  " .
            "(Note: if a token has already been issued in the last 10 minutes, another will not be sent.)";
    } else if ($_POST["do-this"] === "send-data-to-client") {
        $email = strtolower($_POST["email"]);
        if (isset($_POST["username"])){ //used for processing spaid application where username key is used instead of email
            $email = $_POST["username"];
        }
        if (emailPasswordMatch($email, $_POST["password"])) {
            $filename = $_POST["filename"];
            if (!file_exists("users/$email/data/$filename")) {
                echo "File not found.";
            } else {
                echo file_get_contents("users/$email/data/$filename");
            }
        } else {
            echo "Request rejected.";
        };
    } else if ($_POST["do-this"] === "save-contents-to-file") {
        $email = strtolower($_POST["email"]);
        if (isset($_POST["username"])){ //used for processing spaid application where username key is used instead of email
            $email = $_POST["username"];
        }
        $filename = $_POST["filename"];
        $contents = $_POST["contents"];
        //if (emailPasswordMatch($email, $_POST["password"])) {
        //limits to only one single allowable file and under 1 MB.
        if ((emailPasswordMatch($email, $_POST["password"])  && ($filename === "data.txt") && (strlen($contents) < 1048576))) {
            if (!file_exists("users/$email/data")) {
                mkdir("users/$email/data", 0777); //should be 0770 when publishing but can be 0777 on locally secured computer
            }
            file_put_contents("users/$email/data/$filename", $contents);
            echo "Request was successful.";
        } else {
            echo "Request was not successful.";
        }
    }
}
?>
