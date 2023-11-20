<!DOCTYPE html>
<html>
<head>
    <title>Band</title>
    
    <style>
        body {
            font-family: "Lato", sans-serif;
        }    
    </style>
</head>
<body>

<?php
session_start();
$postVars = $_SESSION['POST'];

echo "<h1>Hello, " . $postVars['bandName'] . "!<h1>";

?>

</body>
</html>