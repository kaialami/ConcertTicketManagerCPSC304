<!DOCTYPE html>
<html>
    <head>
        <title>Concertgoer Login</title>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
        <link rel="stylesheet" href="css/concertgoer-style.css">
        <link rel="stylesheet" href="css/navbar-style.css">
        <style>
            body {
                margin: 0;
                text-align: left;
                font-family: "Lato", sans-serif;
                color: steelblue;
            }
            
            img {
                width:200px;
                height:200px;
                margin: 15px;
            }
            
            p {
                text-align: left;
                font-size: 18px;
            }

            input {
                width: 70%;
            }

            input[type="date"] {
                margin-top: 5px;
            }


            .main {
                padding: 16px;
                margin-top: 40px;
            }

            .form-container {
                margin: auto;
                padding: 20px;
                display: table;
                /* border: 2px solid red; */
            }

            .form {
                display: table-cell;
                padding: 20px 100px;
                /* border: 2px solid black; */
            }

            .page-title {
                margin: auto;
                width: 50%;
                text-align: center;
            }

            .page-title h1 {
                font-size: 44px;
            }

            .error-message p {
                margin: auto;
                width: 50%;
                text-align: center;
            }


        </style>
    </head>
    <body>
        <?php
        include_once("database-functions.php");
        
        ob_start();
        session_start();        
        $_SESSION['POST'] = $_POST;

        function handleLoginUserRequest() {
            //global $db_conn;

            $user = $_POST['userID'];
            $pass = $_POST['pass'];
            $user = trim($user);
            $pass = trim($pass);

            if (!sanitizeInput($user)) {
                echo "<p>Special characters are not allowed / Input limit reached!</p>";
            } else {
                $retrievedPass = executePlainSQL("SELECT pass from ConcertGoer where userID = '" . $user . "'");

                //$fetchedUserID = oci_fetch_row($retrievedUserID);
                $fetchedPass = oci_fetch_row($retrievedPass);

                $hash = password_hash($pass, PASSWORD_DEFAULT);

                if (!$fetchedPass || !$hash || !password_verify($pass, $fetchedPass[0])) {
                    echo "<p>Cannot find an account with that username and/or password!</p>";
                } else {
                    //https://stackoverflow.com/questions/18140270/how-to-write-html-code-inside-php-block
                    //https://stackoverflow.com/questions/4871942/how-to-redirect-to-another-page-using-php


//                if (password_needs_rehash($fetchedPass, PASSWORD_DEFAULT)) {
//                    $newHash = password_hash($pass, PASSWORD_DEFAULT);
//                    executePlainSQL("UPDATE ConcertGoer SET  pass == $newHash WHERE userID == $user");
//                    oci_commit($db_conn);
//                }
                    // this might mess up, idk if we even need rehashing tbh so commented out. they said simple was fine
                    echo "<p>What</p>";
                    header("Location: concertgoer.php");
                }
            }
        }

        function handleCreateUserRequest() {
            global $db_conn, $success;

            $newUser = $_POST['newUserID'];
            $newPass = $_POST['newPass'];
            $newGoerName = $_POST['newGoerName'];
            $newEmail = $_POST['newEmail'];
            $newDOB = $_POST['newDOB'];
            if ($newDOB == "") {
                $newDOB = "NULL";
            } else {
                $newDOB = "DATE '" . $newDOB . "'";
            }

            $newUser = trim($newUser);
            $newPass = trim($newPass);
            $newGoerName = trim($newGoerName);
            $newEmail = trim($newEmail);
            $newDOB = trim($newDOB);

            $newPassHashed = password_hash($newPass, PASSWORD_DEFAULT);

            if (!sanitizeInput($newUser) || !sanitizeInput($newEmail)) {
                echo "<p>Special characters are not allowed / Input limit reached!</p>";
            } else {
                $retrievedUserID = executePlainSQL("SELECT userID from ConcertGoer where userID = '" . $newUser . "'");
                $fetchedUserID = oci_fetch_row($retrievedUserID);
                $retrievedEmail = executePlainSQL("SELECT email from ConcertGoer where email = '" . $newEmail . "'");
                $fetchedEmail = oci_fetch_row($retrievedEmail);

                if ($fetchedUserID) {
                    echo "<p>An account with that username already exists!</p>";
                } else if ($fetchedEmail)   {
                    echo "<p>An account with that email already exists!</p>";
                } else {
                    executePlainSQL("INSERT INTO ConcertGoer VALUES  ('" . $newUser . "', '" . $newPassHashed . "', '" . $newGoerName . "', '" . $newEmail . "', " . $newDOB . ")");
                    oci_commit($db_conn);
                    if ($success) {
                        $_POST['userID'] = $_POST['newUserID'];
                        $_POST['pass'] = $_POST['newPass'];
                        $_SESSION['POST'] = $_POST;
                        header("Location: concertgoer.php");
                    } else {
                        echo "<p>Please enter a valid username, password, name and email.</p>";
                        $success = true;
                    }
                }
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


        ?>
        
    <div class="navbar">
        <a href="landingpage.php">Home</a>
    </div>

    <div class="main">
        <div class="page-title">
            <h1>Welcome!</h2>
        </div>
        <div class="form-container">
            <div class="form">
                <h1>Login as a ConcertGoer</h1>
                <form method="post" action="concertgoer-login.php">
                    <input type="hidden" id="loginUserRequest" name="loginUserRequest">
                    <input type="text" name="userID" placeholder="Username"> <br /><br />
                    <input type="text" name="pass" placeholder="Password"> <br /><br /> 
                    <button type="submit">Login</button>
                </form>
            </div>
            <div class="form">
                <h1>Need an account?</h1>
                <form method="post" action="concertgoer-login.php">
                    <input type="hidden" id="createUserRequest" name="createUserRequest">
                    <input type="text" name="newUserID" placeholder="Username"> <br /><br />
                    <input type="text" name="newPass" placeholder="Password"> <br /><br />
                    <input type="text" name="newGoerName" placeholder="Name"> <br /><br /> 
                    <input type="email" name="newEmail" placeholder="Email"> <br /><br /> 
                    <label>Date of Birth (optional) </label><input type="date" name="newDOB"> <br /><br /> 
                    <!--// https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/date -->
                    <button type="submit">Create</submit>
                </form>
                <br>
            </div>
        </div>
        <div class="error-message">
            <?php 
                if (isset($_POST['loginUserRequest']) || isset($_POST['createUserRequest'])) {
                    handlePOSTRequest();
                }
            ?>
        </div>
    </div>
           
        
        
    </body>
</html>