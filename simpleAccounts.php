<?php
header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: GET, POST');
// header("Access-Control-Allow-Headers: X-Requested-With");
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
    echo "minimal response for any POST here<br>";
    var_dump($_POST);
    if ($_POST["do-this"] === "login") {
        echo $webPagePart1;
        echo "<h2>Login Test:</h2>";
        $email = strtolower($_POST["email"]);
        if (emailPasswordMatch($email, $_POST["password"])) {
            echo "<p>The email and password match.</p>";
        } else {
            echo "<p>The email and password do not match.</p>";
        };
        echo $webPagePart2;
        echo "<button onclick='window.history.back()'>OK</button>";
    } else if ($_POST["do-this"] === "update-profile") {
        echo $webPagePart1;
        echo "<h2>Request Result:</h2>";
        $email = strtolower($_POST["email"]);
        $token = $_POST["token"];
        if (emailTokenMatch($email, $token) && unexpiredToken($email, 10)) {
            file_put_contents("users/$email/password", password_hash($_POST["password"], PASSWORD_DEFAULT));
            unlink("users/$email/token");
            echo "<p>Request was successful.</p>";
        } else {
            echo "<p>Request was not succuessful.</p>";
        }
        echo $webPagePart2;
        echo "<button onclick='window.history.back()'>OK</button>";
    } else if ($_POST["do-this"] === "send-token") { //send a token
        echo $webPagePart1;
        echo "<h2>Token Requested.</h2>";
        $email = strtolower($_POST["email"]);
        if (unexpiredToken("$email", 10)) {
            //do nothing
        } else {
            $token = sendToken($email);
        }
        echo "<p>A request to send a token to $email has been made.</p>" .
            "<p>Check your email $email for the token.</p>" . "(testing purposes: $token)</p>" .
            "<p>Tokens are good for 10 minutes.</p>" .
            "<p>(Note: if a token has already been issued in the last 10 minutes, another will not be sent.)</p>";
        echo $webPagePart2;
        echo "<button onclick='window.history.back()'>OK</button>";
    }
}
?>
