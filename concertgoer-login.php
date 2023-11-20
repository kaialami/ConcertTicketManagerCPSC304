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
                top: 40%;
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
        <?php
        session_start();
        $_SESSION['POST'] = $_POST;
        include_once("database-functions.php");
        
        function handleLoginUserRequest() {
            $user = $_POST['userID'];
            $pass = $_POST['pass'];
            $user = trim($user);
            $pass = trim($pass);
            // NEEDS SANITIZING/HASHING
            //$count = executePlainSQL("SELECT Count(*) from ConcertGoer where userID = '" . $userID . "'");
            //$retrievedUserID = executePlainSQL("SELECT userID from ConcertGoer where userID = '" . $userID . "'");
            $retrievedPass = executePlainSQL("SELECT pass from ConcertGoer where userID = '" . $user . "'");
            
            //$fetchedUserID = oci_fetch_row($retrievedUserID);
            $fetchedPass = oci_fetch_row($retrievedPass);
            
            if ($fetchedPass == false ||
                $fetchedPass[0] != $pass) {
                echo "<p>Cannot find a user with that id and/or password!</p>";
            } else {
                //https://stackoverflow.com/questions/18140270/how-to-write-html-code-inside-php-block
                //https://stackoverflow.com/questions/4871942/how-to-redirect-to-another-page-using-php
                //echo "href=\"concertgoer.php\"";
                header("Location: concertgoer.php");
            }

    
        }
        
        function handleCreateUserRequest() {
            global $db_conn;
            
            $newUser = $_POST['newUserID'];
            $newPass = $_POST['newPass'];
            $newGoerName = $_POST['newGoerName'];
            $newEmail = $_POST['newGoerName'];
            $newDOB = $_POST['newDOB'];
            
            $newUser = trim($newUser);
            $newPass = trim($newPass);
            $newGoerName = trim($newGoerName);
            $newEmail = trim($newEmail);
            $newDOB = trim($newDOB);

            $retrievedUserID = executePlainSQL("SELECT userID from ConcertGoer where userID = '" . $newUser . "'");
            $fetchedUserID = oci_fetch_row($retrievedUserID);

            if ($fetchedUserID != false) {
                echo "<p>Cannot create a new user with that name!</p>";
            } else {
                executePlainSQL("INSERT INTO ConcertGoer VALUES  ('" . $newUser . "', '" . $newPass . "', '" . $newGoerName . "', '" . $newEmail . "', DATE '" . $newDOB . "')");
                oci_commit($db_conn);
                $_POST['userID'] = $_POST['newUserID'];
                $_POST['pass'] = $_POST['newPass'];
                $_SESSION['POST'] = $_POST;
                header("Location: concertgoer.php");
            }
            
            
        }


        function handlePOSTRequest()
        {
            if (connectToDB()) {
                if (array_key_exists("loginUserRequest", $_POST)) {
                    handleLoginUserRequest();
                }
                if (array_key_exists("createUserRequest", $_POST)) {
                    handleCreateUserRequest();
                }
            }
        
            disconnectFromDB();
        }

        if (isset($_POST['loginUserRequest']) || isset($_POST['createUserRequest'])) {
            handlePOSTRequest();
        }
        
        ?>
        
        
        <h1>Login as a concertgoer:</h1>
        <img src="images/avatar.png"/>
        <form method="post" action="concertgoer-login.php">
            <input type="hidden" id="loginUserRequest" name="loginUserRequest">
            Username: <input type="text" name="userID"> <br /><br />
            Password: <input type="text" name="pass"> <br /><br /> 
            <input type="submit" value="Login" name="loginUserSubmit">
        </form>
        <h1>Need an account?</h1>
        <form method="post" action="concertgoer-login.php">
            <input type="hidden" id="createUserRequest" name="createUserRequest">
            Username: <input type="text" name="newUserID"> <br /><br />
            Password: <input type="text" name="newPass"> <br /><br />
            Name:     <input type="text" name="newGoerName"> <br /><br /> 
            Email:    <input type="text" name="newEmail"> <br /><br /> 
            Date of Birth: <input type="date" name="newDOB"> <br /><br /> 
            <!--// https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/date -->
            <input type="submit" value="Create" name="createUserSubmit">
        </form>
        <br>
        <a href="landingpage.php"><h3>Return to home page</h3></a>
           
        
        
    </body>
</html>