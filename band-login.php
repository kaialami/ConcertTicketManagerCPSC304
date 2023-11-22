<!DOCTYPE html>
<html>
<head>
    <title>Band Login</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
    <link rel="stylesheet" href="css/login-style.css">
    <link rel="stylesheet" href="css/navbar-style.css">
    <link rel="stylesheet" href="css/input-style.css">

    <style>
        body {
            color: #ed7d31;
        }    

        button {
            border: 2px solid #ed7d31;
            color: #ed7d31;
        }

        button:hover {
            background-color: #ed7d31;
            border-color: #e66914;
        }

        h3 {
            font-size: 16px;
        }

        hr {
            border: 1px solid #ed7d31;
            margin: 20px 0px;
        }

        #attribute {
            visibility: hidden;
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

    <div class="navbar">
        <a href="landingpage.php">Home</a>
    </div>

    <div class="main">
        <div class="page-title">
            <h1>Welcome!</h1>
        </div>
        <div class="form-container">
            <div class="form">
                <h2>Login as a Band</h2>
                <form method="post">
                    <input type="hidden" name="loginRequest">
                    <input type="text" name="bandName" placeholder="Band Name"> <br>
                    <input type="text" name="pass" placeholder="Password"> <br>
                    <button type="submit">Login</button>
                </form>
            </div>
            <div class="form">
                <h2>Register your band</h2>
                <form method="post">
                    <input type="hidden" name="createRequest">
                    <input type="text" name="bandName" placeholder="Band Name"> <br>
                    <input type="text" name="pass" placeholder="Password"> <br>
                    <hr>
                    <h3>Every band needs a member!</h3>
                    <input type="text" name="memberName" placeholder="Name"> <br>
                    <label>Date of Birth<br><input type="date" name="memberDOB" placeholder="DOB"></label><br>
                    <label><input id="musician" type="radio" name="role" value="musician">Musician </label><br>
                    <label><input id="manager" type="radio" name="role" value="manager">Manager </label><br>
                    <label><input id="technician" type="radio" name="role" value="technician">Technician </label><br>
                    <input id="attribute" type="text" name="attribute" placeholder="Instrument"> <br>
                    <!-- <input id="specialty" type="text" name="specialty" placeholder="Specialty"> <br> -->
                    <button type="submit">Create</button>
                </form>
            </div>
        </div>
        <br>
        <div class="error-message">
            <?php 
            if (isset($_POST['loginRequest'])) {
                handlePOSTRequest();
            }
            ?>
        </div>
    </div>


    <script>
        // https://stackoverflow.com/questions/50065773/what-is-the-best-solution-to-avoid-inline-onclick-function

        var musicianRadio = document.getElementById("musician");
        var managerRadio = document.getElementById("manager");
        var technicianRadio = document.getElementById("technician");
        var attribute = document.getElementById("attribute");

        musicianRadio.addEventListener("click", showInstrument);
        managerRadio.addEventListener("click", hide);
        technicianRadio.addEventListener("click", showSpecialty);


        function showInstrument() {
            attribute.style.visibility = "visible";
            attribute.placeholder = "Instrument";
        }

        function showSpecialty() {
            attribute.style.visibility = "visible";
            attribute.placeholder = "Specialty";
        }

        function hide() {
            attribute.style.visibility = "hidden";
        }

    </script>

</body>
</html>