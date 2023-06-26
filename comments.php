<?php
include_once("simpleAccounts.php");
$now = date("F j, Y, g:i a");
//echo($now."<br>");
$thisComment = $_POST["comments"];
$email=$_POST["email"];
$hashedEmail=substr($email, 0,2).crc32($email);
$password=$_POST["password"];
$userIsVerified=emailPasswordMatch($email,$password);
//this file needs permissions 640

$pageName = basename(__FILE__, ".php");
$commentsFileName = $pageName . "-comments.txt";
$comments = file_get_contents($commentsFileName);

if (($userIsVerified)&&(isset($_POST["comments"]))){
    $comments = $comments."-------".$now."-------\n".$hashedEmail." writes :\n".$_POST["comments"]."\n\n";
    file_put_contents($commentsFileName, $comments);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments Here</title>
    <style>
        #comment-history {
            height: auto;
            max-height: 200px;
            overflow: auto;
            background-color: #eeeeee;
            word-break: normal !important;
            word-wrap: normal !important;
            white-space: pre !important;
        }
        form{
            display: flex;
            flex-direction: column;
        }
    </style>
</head>

<body>
    <pre id="comment-history">
        
<?php echo (htmlspecialchars($comments)); ?>
</pre>
    <form action="comments.php" method="post">
        <label for="comments">Comments</label>
        <input type="text" id="comments" name="comments" required>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        <button>Submit</button>
    </form>
    <?php
    if (isset($_POST['comments'])){
        if($userIsVerified){
        echo("<p>Comments below were added:</p>");
        }
        else{
        echo("<p>Comments below were not added due to invalid credientials:</p>");
        }
        echo("<pre>".htmlspecialchars($thisComment)."</pre>");
    }
    ?>
</body>

</html>
<?php
?>