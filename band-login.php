<!DOCTYPE html>
<html>
<head>
    <title>Band Login</title>
    
    <style>
        body {
            font-family: "Lato", sans-serif;
        }    
    </style>
</head>
<body>
<?php
include_once("database-functions.php");

session_start();
$_SESSION['POST'] = $_POST;


// https://stackoverflow.com/questions/2447211/php-pass-post-variables-with-header
function handleLoginRequest()
{
    header("Location: band.php");
}

function handlePOSTRequest() 
{
    if (connectToDB()) {
        if (array_key_exists("loginRequest", $_POST)) {
            handleLoginRequest();
        }
    }

    disconnectFromDB();
}


?>

<a href="landingpage.php">Home</a>

<h1>Login as a Band</h1>

<h2>Login</h2>
<div>
    <form method="post">
        <input type="hidden" name="loginRequest">
        <input type="text" name="bandName">
        <input type="submit" value="Login">
    </form>
</div>
<br>
<?php 
if (isset($_POST['loginRequest'])) {
    handlePOSTRequest();
}
?>

<br><hr>

<h2>Create Band Account</h2>
<div>
    <form method="post">
        <input type="hidden" name="createRequest">
        <input type="text" name="bandName">
        <input type="submit" value="Create Account">
    </form>
</div>


</body>
</html>