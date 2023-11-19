<!DOCTYPE html>
<html>
<title>Music Manager: Concertgoer Login</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
    <head>
        <style>
            html {
                display: table;
                margin: auto;
            }

            body {
                text-align: center;
                height: 200px;
                width: 400px;
                margin-top: -250px;
                margin-left: -200px;
                position: fixed;
                top: 50%;
                left: 50%;
                background-color: steelblue;
                font-family: "Lato", sans-serif;
            }

            img {
                width:200px;
                height:200px;
                margin: 8px;
            }

            p {
                text-align: left;
            }

        </style>

    </head>



    <body>
        <h1>Login as a concertgoer:</h1>
        <img src="images/avatar.png"/>

        <form method="post" action="concertgoer.php">
            <input type="hidden" name="userID" value="kahnsert123">
            <input type="submit" value="Placeholder: login as kahnsert123">
        </form>

        <br>
        <br>
        <a href="landingpage.php"><h3>Return to home page</h3></a>

    </body>

</html>