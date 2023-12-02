<!DOCTYPE html>
<!-- https://stackoverflow.com/questions/871858/php-pass-variable-to-next-page -->
<!-- https://stackoverflow.com/questions/6833914/how-to-prevent-the-confirm-form-resubmission-dialog -->
<html>
<head>
    <title>ConcertGoer - Showtime!</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
    <link rel="stylesheet" href="css/concertgoer-style.css">
    <link rel="stylesheet" href="css/navbar-style.css">
    <link rel="stylesheet" href="css/input-style.css">
    <link rel="stylesheet" href="css/mainpage-style.css">
    <style>
        
        .search-by {
            margin: 20px;
            margin-left: 0px;
        }

        #confirm-delete {
            visibility: hidden;
            color: red;
            border: 2px solid red
        }

        #confirm-delete:hover {
            color: #fff;
            background-color: red;
            border-color: red;
        }

        
    </style>

</head>
<body>
<?php
include_once("database-functions.php");

ob_start();
session_start();
$temp = $_SESSION['POST'];
$userID = $temp['userID'];

$linebreaks = "<br> <br> <br> <br> <br>";

// check if $userID exists as an entry in the DB, go back home if not
checkLogin($userID, "c");


function handleViewTicketsRequest()
{
    global $userID;

    $count = executePlainSQL("SELECT Count(*) FROM ConcertGoer c, TicketID t, TicketPrice p WHERE c.userID = t.userID AND t.seatnum = p.seatnum AND c.userid = '" . $userID . "'");
    $result = executePlainSQL("SELECT t.seatnum, t.venueaddress, t.showdatetime, p.price FROM ConcertGoer c, TicketID t, TicketPrice p WHERE c.userID = t.userID AND t.seatnum = p.seatnum AND c.userid = '" . $userID . "'");

    if ($rowCount = oci_fetch_row($count)) {
        if ($rowCount[0] == "0") {
            echo "<p>You have not purchased any tickets.</p><br> <br> <br>";
        } else {
            echo "<p>You have purchased " . $rowCount[0] . " tickets:</p>";
            echo "<table>";
            echo "<tr>
                    <th>Seat Number</th>
                    <th>Venue Address</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Price</th>
                </tr>\n";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                $datetime = formatDateTime($row['SHOWDATETIME']);
                echo "<tr><td>" . $row['SEATNUM'] . "</td><td>" . $row['VENUEADDRESS'] . "</td><td>" . $datetime[0] . "</td><td>" . $datetime[1] . " " . $datetime[2] . "</td><td>$" . $row['PRICE'] . "</td></tr>\n";
            }

            echo "</table><br><br>";
        }
    }
}

function printShowQueryResults($result)
{
    echo "<table>";
    echo "<tr>
            <th>Venue Address</th>
            <th>Date</th>
            <th>Time</th>
            <th>Performer</th>
            <th>Show Name</th>
            <th>Event</th>
            <th>Event Date</th>
        </tr>\n";

    while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
        $datetime = formatDateTime($row['SHOWDATETIME']);
        echo "<tr><td>" . $row['VENUEADDRESS'] . "</td><td>" . $datetime[0] . "</td><td>" . $datetime[1] . " " . $datetime[2] . "</td><td>" . $row['BANDNAME'] . "</td><td>" . $row['SHOWNAME'] . "</td><td>" . $row['EVENTNAME'] . "</td><td>" . $row['EVENTDATE'] . "</td></tr>\n";
    }
    echo "</table><br><br>";
}

function handleSearchShowsBandRequest()
{
    $bandname = $_POST['searchShowsBandRequest'];
    $bandname = trim($bandname);

    if (sanitizeInput($bandname)) {
        $result = executePlainSQL("SELECT * FROM Show WHERE regexp_like(bandname, '^" . $bandname . "$', 'i')");
        $count = executePlainSQL("SELECT Count(*) FROM Show WHERE regexp_like(bandname, '^" . $bandname . "$', 'i')");

        if ($rowCount = oci_fetch_row($count)) {
            if ($rowCount[0] == "0") {
                echo "<p>No shows for that performer.</p><br> <br> <br>";
            } else {
                printShowQueryResults($result);
            }
        }
    } else {
        echo "<p>Special characters are not allowed / Input limit reached!</p><br> <br> <br>";
    }



}

function handleSearchShowsVenueRequest()
{
    $venue = $_POST['searchShowsVenueRequest'];
    $venue = trim($venue);

    if (sanitizeVenueInput($venue)) {

    $result = executePlainSQL("SELECT * FROM Show WHERE regexp_like(venueaddress, '^" . $venue . "$', 'i')");
    $count = executePlainSQL("SELECT Count(*) FROM Show WHERE regexp_like(venueaddress, '^" . $venue . "$', 'i')");

    if ($rowCount = oci_fetch_row($count)) {
        if ($rowCount[0] == "0") {
            echo "<p>No shows for that venue address. Make sure you typed the address fully and correctly!</p><br> <br> <br>";
        } else {
            printShowQueryResults($result);
        }
    }

    } else {
        echo "<p>Special characters are not allowed / Input limit reached!</p><br> <br> <br>";
    }
}

function handleSearchShowsEventRequest()
{
    $event = $_POST['searchShowsEventRequest'];
    $event = trim($event);

    if (sanitizeInput($event)) {

    $result = executePlainSQL("SELECT * FROM Show WHERE regexp_like(eventname, '^" . $event . "$', 'i')");
    $count = executePlainSQL("SELECT Count(*) FROM Show WHERE regexp_like(eventname, '^" . $event . "$', 'i')");

    if ($rowCount = oci_fetch_row($count)) {
        if ($rowCount[0] == "0") {
            echo "<p>No shows for that event.</p><br> <br> <br>";
        } else {
            printShowQueryResults($result);
        }
    }

    } else {
        echo "<p>Special characters are not allowed / Input limit reached!</p><br> <br> <br>";
    }

}

function printTicketQueryResults($result)
{
    echo "<table>";
    echo "<tr>
            <th>Ticket ID</th>
            <th>Seat Num</th>
            <th>Venue Address</th>
            <th>Date</th>
            <th>Time</th>
            <th>Price</th>
        </tr>\n";

    while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
        $datetime = formatDateTime($row['SHOWDATETIME']);
        echo "<tr><td>" . $row['TICKETID'] . "</td><td>" . $row['SEATNUM'] . "</td><td>" . $row['VENUEADDRESS'] . "</td><td>" . $datetime[0] . "</td><td>" . $datetime[1] . " " . $datetime[2] . "</td><td>$" . $row['PRICE'] . "</td></tr>\n";
    }
    echo "</table><br><br>";
}

function handleSearchTicketsRequest()
{
    $venue = $_POST['searchTicketsVenueRequest'];
    $date = $_POST['searchTicketsDateRequest'];
    $time = $_POST['searchTicketsTimeRequest'];

    $venue = trim($venue);

    if (sanitizeVenueInput($venue)) {
        if (!$venue || !$date || !$time) {
            echo "<p>Please fill out all fields.</p><br><br>";
        } else {
            echo "<p>" . $venue . " @ " . $date . " " . $time . "</p>";
            $result = executePlainSQL("SELECT i.ticketid, i.seatnum, i.venueaddress, i.showdatetime, p.price FROM TicketID i, TicketPrice p WHERE 
                       regexp_like(i.venueaddress, '^" . $venue . "$', 'i') AND i.showDateTime = 
                       TIMESTAMP '" . $date . " " . $time . ":00' AND i.userID is NULL AND 
                       i.seatnum = p.seatnum ORDER BY p.price");
            $count = executePlainSQL("SELECT Count(*) FROM TicketID i, TicketPrice p WHERE 
                       regexp_like(i.venueaddress, '^" . $venue . "$', 'i') AND i.showDateTime = 
                       TIMESTAMP '" . $date . " " . $time . ":00' AND i.userID is NULL AND 
                       i.seatnum = p.seatnum AND i.venueaddress = p.venueaddress AND i.showdatetime = p.showdatetime");

            // $result = executePlainSQL("SELECT ticketid, seatnum, venueaddress, showdatetime FROM TicketID WHERE regexp_like(venueaddress, '^" . $venue . "$', 'i') AND 
            //         showDateTime = TIMESTAMP '" . $date . " " . $time . ":00' AND userID is NULL");
            if ($rowCount = oci_fetch_row($count)) {
                if ($rowCount[0] == "0") {
                    echo "<p>No tickets found. Check your query (or maybe no tickets are available).</p><br> <br> <br>";
                } else {
                    echo "";
                    printTicketQueryResults($result);
                }
            }
        }
    } else {
        echo "<p>Special characters are not allowed / Input limit reached!</p><br> <br> <br>";
    }
}

function handlePurchaseTicketRequest() {
    global $userID, $db_conn, $success;
    $idNum = $_POST['purchaseTicketRequest'];

    if (preg_match("/^\d+$/", $idNum)) {
        $userCheck = executePlainSQL("SELECT userID FROM TicketID WHERE ticketID = $idNum");
        $userCheck = oci_fetch_row($userCheck);
        if (!$userCheck) {
            echo "<p>Invalid ID. Ticket does not exist.</p>";
        
        } else if ($userCheck[0] == 0) {
            executePlainSQL("UPDATE TicketID SET userID = '$userID' WHERE ticketID = $idNum");
            oci_commit($db_conn);
            if ($success) {
                echo "<p>Ticket purchased.</p><br><br>";
            } else {
                echo "<p>Error encountered purchasing.</p><br><br>";
                $success = true;
            }

        } else {
            echo "<p>This ticket is already purchased.</p><br><br>";
        }
    } else {
        echo "<p>Invalid ID.</p>";
    }
}

function handleDeleteAccountRequest() {
    global $userID, $success, $db_conn;
    $usernameDelete = trim($_POST['usernameDelete']);
    $passwordDelete = trim($_POST['passwordDelete']);

    if (!$usernameDelete || !$passwordDelete) {
        echo "<p>Please fill out all the fields</p><br><br>";
    } 
    else if (!sanitizeInput($usernameDelete) || !sanitizeInput($passwordDelete)) {
        echo "<p>Special characters are not allowed / Input length limit reached!</p>";
    }
    else if ($userID != $usernameDelete) {
        echo "<p>Invalid username. Please input your complete username. Remember to check your capitals!</p><br><br>";
    }
    else {
        $retrievedUser = executePlainSQL("SELECT userID, pass FROM ConcertGoer WHERE userID = '" . $usernameDelete . "'");
        $row = OCI_Fetch_Array($retrievedUser, OCI_BOTH);
        if (!$row) {
            echo "<p>Error in attempting account deletion.</p><br><br>";
        } else if (!password_verify($passwordDelete, $row['PASS'])) {
            echo "<p>Invalid password. Please input your complete password. Remember to check your capitals!</p><br><br>";
        } else {
            executePlainSQL("DELETE FROM ConcertGoer WHERE userID = '" . $usernameDelete . "'");
            oci_commit($db_conn);
            if ($success) {
                header("Location: landingpage.php");
            } else {
                echo "<p>Error in attempting account deletion.</p><br><br>";
                $success = true;
            }
        }
    }
}

function handleCalculateAvgRequest() {
    $venue_averages = executePlainSQL("SELECT venueAddress, AVG(price) FROM TicketPrice GROUP BY venueAddress");

    if ($venue_averages[0] == "0") {
        echo "<p>Error encountered - no tickets exist.</p><br> <br> <br>";
    } else {
        echo "<table>";
        echo "<tr>
                    <th>Venue Address</th>
                    <th>Average Price</th>
                </tr>\n";

//        $row = OCI_Fetch_Array($venue_averages, OCI_BOTH);
//        echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>\n";

        while ($row = OCI_Fetch_Array($venue_averages, OCI_BOTH)) {
            echo "<tr><td>" . $row['VENUEADDRESS'] . "</td><td>" . ceil($row['AVG(PRICE)'] * 100)/100 . "</td></tr>\n";
        }

        echo "</table><br><br>";
    }
}

function handlePOSTRequest()
{
    if (connectToDB()) {
        if (array_key_exists("viewTicketsRequest", $_POST)) {
            handleViewTicketsRequest();
        }
        if (array_key_exists("searchShowsBandRequest", $_POST)) {
            handleSearchShowsBandRequest();
        }
        if (array_key_exists("searchShowsVenueRequest", $_POST)) {
            handleSearchShowsVenueRequest();
        }
        if (array_key_exists("searchShowsEventRequest", $_POST)) {
            handleSearchShowsEventRequest();
        }
        if (array_key_exists("searchTicketsVenueRequest", $_POST)) {
            handleSearchTicketsRequest();
        }
        if (array_key_exists("purchaseTicketRequest", $_POST)) {
            handlePurchaseTicketRequest();
        }
        if (array_key_exists("deleteAccountRequest", $_POST)) {
            handleDeleteAccountRequest();
        }
        if (array_key_exists("calculateAvgRequest", $_POST)) {
            handleCalculateAvgRequest();
        }
    } else {
        echo "<p>Error encountered - Please try again.</p>";
    }

    disconnectFromDB();
}



?>


<div class="navbar">
    <a href="landingpage.php">Home</a>
    <a href="#purchased">Purchased Tickets</a>
    <a href="#search">Search for shows</a>
    <a href="#buy">Buy Tickets</a>
    <a href="#venue-pricing">Venue Pricing</a>
    <a href="#delete-user">Delete Account</a>
</div>

<div class="main">
    <div class="page-title">
        <h1>Hello, <?php echo $userID ?>!</h1>
    </div>

    <div class="section">
        <a class="anchor" id="purchased"></a>
        <h2>Purchased Tickets</h2>
        <form action="#purchased" method="post">
            <input type="hidden" name="userID" value=<?php echo $userID ?>>
            <input type="hidden" name="viewTicketsRequest">
            <button type="submit">View Tickets</button>
        </form>

    <br>

    <?php
    if (isset($_POST['viewTicketsRequest'])) {
        handlePOSTRequest();
    } else {
        echo $linebreaks;
    }
    ?>
    
    <br>
    </div>

    <hr>

    <div class="section">
        <a class="anchor" id="search"></a>
        <h2>Search for shows by</h2>

        <form class="search-by" action="#search" method="post">
            <input type="hidden" name="userID" value=<?php echo $userID ?>>
            <input type="text" name="searchShowsBandRequest" placeholder="Performer">
            <button type="submit">Search</button>
        </form>
        <form class="search-by" action="#search" method="post">
            <input type="hidden" name="userID" value=<?php echo $userID ?>>
            <input type="text" name="searchShowsVenueRequest" placeholder="Venue Address">
            <button type="submit">Search</button>
        </form>
        <form class="search-by" action="#search" method="post">
            <input type="hidden" name="userID" value=<?php echo $userID ?>>
            <input type="text" name="searchShowsEventRequest" placeholder="Event Name">
            <button type="submit">Search</button>
        </form>

        <br>

        <?php
        if (isset($_POST['searchShowsBandRequest']) || isset($_POST['searchShowsVenueRequest']) || isset($_POST['searchShowsEventRequest'])) {
            handlePOSTRequest();
        } else {
            echo $linebreaks;
        }
        ?>
        <br>
    </div>

    <hr>

    <div class="section">
        <a class="anchor" id="buy"></a>
        <h2>Buy Tickets</h2>
        <p>Search for available tickets</p>
        <form action="#buy" method="post">
            <input type="hidden" name="userID" value=<?php echo $userID ?>>
            <input type="text" name="searchTicketsVenueRequest" placeholder="Venue Address">
            <input type="date" name="searchTicketsDateRequest">
            <input type="time" name="searchTicketsTimeRequest">
            <button type="submit">Search</button>
        </form>

        <?php
        if (isset($_POST['searchTicketsVenueRequest'])) {
            handlePOSTRequest();
        } else {
            echo $linebreaks;
        }
        ?>

        <br>
        <p>Enter the ticket ID number you would like to purchase</p>
        <form action="#buy" method="post">
            <input type="hidden" name="userID" value=<?php echo $userID ?>>
            <input type="number" min = "0" name="purchaseTicketRequest" placeholder="ID Number">
            <button type="submit">Purchase</button>
        </form>

        <br>

        <?php
        if (isset($_POST['purchaseTicketRequest'])) {
            handlePOSTRequest();
        } else {
            echo $linebreaks;
        }
        ?>
        
        <br>
    </div>
    <hr>
    <div class="section">
        <a class="anchor" id="venue-pricing"></a>
        <h2>Venue Pricing</h2>
        <p>
            Calculate the average ticket price per venue.
        </p>
        <form action="#venue-pricing" method="post">
            <input type="hidden" name="userID" value=<?php echo $userID ?>>
            <input type="hidden" name="calculateAvgRequest">
            <button type="submit">Calculate</button>
        </form>
        <br>
        <?php
        if (isset($_POST['calculateAvgRequest'])) {
            handlePOSTRequest();
        } else {
            echo $linebreaks;
        }
        ?>
        <br>
    </div>
    <div class="section">
        <a class="anchor" id="delete-user"></a>
        <h2>Delete Your Account</h2>
        <p>
            <b style="color: red;">DANGER: </b>Deleting your account is irreversible. You will lose ownership of all of your current tickets.
            <br>Only delete your account if you are absolutely sure!
        </p>
        <p>Enter your username and password to confirm account deletion.<br>Upon successful deletion, you will be redirected to the home page.</p>
        <form action="#delete-user" method="post">
            <input type="hidden" name="userID" value=<?php echo $userID ?>>
            <input type="hidden" name="deleteAccountRequest">
            <input type="text" name="usernameDelete" placeholder="Username"><br>
            <input type="text" name="passwordDelete" placeholder="Password"><br>
            <button type="button" id="delete">Delete</button><br>
            <button type="submit" id="confirm-delete">Are you 100% sure??</button>
        </form>
        <br>
        <?php
            if (isset($_POST['deleteAccountRequest'])) {
                handlePOSTRequest();
            } else {
                echo $linebreaks;
            }
        ?>
        <br>
    </div>
</div>

<script src="scripts/delete-user.js"></script>

</body>
</html>