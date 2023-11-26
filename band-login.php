<!DOCTYPE html>
<html>
<head>
    <title>Band Login - Showtime!</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
    <link rel="stylesheet" href="css/login-style.css">
    <link rel="stylesheet" href="css/navbar-style.css">
    <link rel="stylesheet" href="css/input-style.css">
    <link rel="stylesheet" href="css/band-style.css">

    <style>
        input {
            width: 70%;
        }

        input[type="date"] {
            margin-top: 5px;
        }

        h3 {
            font-size: 16px;
        }

        hr {
            margin: 20px 0px;
        }

        #attribute {
            visibility: visible;
        }

        #login {
            padding-right: 50px;
        }


    </style>
</head>
<body>
    <?php
    include_once("database-functions.php");

    ob_start();
    session_start();
    $_SESSION['POST'] = $_POST;


    // https://stackoverflow.com/questions/2447211/php-pass-post-variables-with-header
    function handleLoginRequest() {
        $bandname = $_POST['bandName'];
        $pass = $_POST['pass'];
        $bandname = trim($bandname);
        $pass = trim($pass);

        if (!sanitizeInput($bandname)) {
            echo "<p>Special characters are not allowed / Input limit reached!</p>";
        } else {
            $retrievedPass = executePlainSQL("SELECT pass from Band where bandname = '" . $bandname . "'");
            $fetchedPass = oci_fetch_row($retrievedPass);
            $hash = password_hash($pass, PASSWORD_DEFAULT);

            if (!$fetchedPass || !$hash || !password_verify($pass, $fetchedPass[0])) {
                echo "<p>Cannot find an account with that username and/or password!<br>Remember to use the correct capitalization!</p>";
            } else {
                $_POST['pass'] = "";
                $_SESSION['POST'] = $_POST;
                header("Location: band.php");
            }
        }
    }

    function handleCreateRequest() {
        global $db_conn, $success;

        $bandname = trim($_POST['bandName']);
        $pass = trim($_POST['pass']);
        $membername = trim($_POST['memberName']);
        $memberDOB = $_POST['memberDOB'];
        $role = $_POST['role'];
        $attribute = "na";
        if ($role != "manager") {
            $attribute = $_POST['attribute'];
        }
        

        if (!$bandname || !$pass || !$membername || !$memberDOB || !$attribute) {
            echo "<p>Please fill out all of the fields.</p>";
        }
        else if (!sanitizeInput($bandname) || !sanitizeInput($membername) || !sanitizeInput($attribute)) {
            echo "<p>Special characters are not allowed / Input length limit reached!</p>";
        }
        else {
            $retrievedBandname = executePlainSQL("SELECT bandname FROM Band WHERE bandname = '" . $bandname . "'");
            $fetchedBandname = oci_fetch_row($retrievedBandname);

            $retrievedMember = executePlainSQL("SELECT membername, memberDOB FROM BandMember WHERE membername = '" . $membername . "' AND memberDOB = DATE '" . $memberDOB . "'");
            $fetchedMember = oci_fetch_row($retrievedMember);

            if ($fetchedBandname) {
                echo "<p>A band with that name is already registered!</p>";
            } else {
                $passHashed = password_hash($pass, PASSWORD_DEFAULT);
                executePlainSQL("INSERT INTO Band VALUES('" . $bandname . "', NULL, NULL, '" . $passHashed . "')");
                oci_commit($db_conn);

                if (!$fetchedMember) {
                    executePlainSQL("INSERT INTO BandMember VALUES('" . $membername . "', DATE '" . $memberDOB . "', NULL)");
                    oci_commit($db_conn);
                    if ($role == "manager") {
                        executePlainSQL("INSERT INTO Manager VALUES ('" . $membername . "', DATE '" . $memberDOB . "')");
                        oci_commit($db_conn);
                    } else {
                        executePlainSQL("INSERT INTO " . $role . " VALUES ('" . $membername . "', DATE '" . $memberDOB . "', '" . $attribute . "')");
                        oci_commit($db_conn);
                    }
                }

                executePlainSQL("INSERT INTO WorksFor VALUES('" . $membername . "', DATE '" . $memberDOB . "', '" . $bandname . "', 'y')");
                oci_commit($db_conn);

                if ($success) {
                    $_POST['pass'] = "";
                    $_SESSION['POST'] = $_POST;
                    header("Location: band.php");
                } else {
                    echo "<p>Please enter a valid band name, password and member information.</p>";
                    $success = true;
                }
            }
        }
    }

    function handlePOSTRequest() {
        if (connectToDB()) {
            if (array_key_exists("loginBandRequest", $_POST)) {
                handleLoginRequest();
            }
            if (array_key_exists("createBandRequest", $_POST)) {
                handleCreateRequest();
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
            <h1>Welcome!</h1>
        </div>
        <div class="error-message">
            <?php 
            if (isset($_POST['loginBandRequest']) || isset($_POST['createBandRequest'])) {
                handlePOSTRequest();
            }
            ?>
        </div>
        <div class="form-container">
            <div class="form">
                <h1 id="login">Login as a Band</h1>
                <form method="post">
                    <input type="hidden" name="loginBandRequest">
                    <input type="text" name="bandName" placeholder="Band Name"> <br>
                    <input type="text" name="pass" placeholder="Password"> <br>
                    <button type="submit">Login</button>
                </form>
            </div>
            <div class="form">
                <h1>Register your band</h1>
                <form method="post">
                    <input type="hidden" name="createBandRequest">
                    <input type="text" name="bandName" placeholder="Band Name"> <br>
                    <input type="text" name="pass" placeholder="Password"> <br>
                    <hr>
                    <h3>Every band needs a member!</h3>
                    <input type="text" name="memberName" placeholder="Name"> <br>
                    <label>Date of Birth<br><input type="date" name="memberDOB" placeholder="DOB"></label><br>
                    <label><input id="musician" type="radio" name="role" value="musician" checked=true>Musician </label><br>
                    <label><input id="manager" type="radio" name="role" value="manager">Manager </label><br>
                    <label><input id="technician" type="radio" name="role" value="technician">Technician </label><br>
                    <input id="attribute" type="text" name="attribute" placeholder="Instrument"> <br>
                    <button type="submit">Create</button>
                </form>
            </div>
        </div>
        <br>
    </div>

    <script src="scripts/bandmember.js"></script>

</body>
</html>