<?php
$thisComment=$_POST["comments"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments Here</title>
</head>
<body>
    <pre id='comments' name='comments'>Comments here.</pre>
<form action="comments.php" method="post">
    <input type="text" id="comments">
    <button>Submit</button>
</form>
</body>
</html>
