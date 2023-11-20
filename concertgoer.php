<!DOCTYPE html>
<!-- https://stackoverflow.com/questions/871858/php-pass-variable-to-next-page -->
<!-- https://stackoverflow.com/questions/6833914/how-to-prevent-the-confirm-form-resubmission-dialog -->
<html>
<title>Music Manager - Concertgoer</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
<head>
    <title>Concert Goer</title>
    <style>
        h1 {
            text-align: center;
        }

        .navbar {
            background-color: #333;
            overflow: hidden;
            width: 100%;
            position: fixed;
            top: 0;
        }

        .navbar a {
            color: #fff;
            text-decoration: none;
            float: left;
            display: block;
            text-align: center;
            font-size: 20px;
            padding: 15px 15px;
        }

        .navbar a:hover {
            background: #ddd;
            color: black;
        }

        a.anchor {
            top: -100px;
            display: block;
            position: relative;
            visibility: hidden;
        }

        .main {
            padding: 16px;
            margin-top: 30px;
        }

        body {
            margin: 0;
            background-color: steelblue;
            font-family: "Lato", sans-serif;
        }

        table {
            text-align: left;
        }

        th, td {
            padding-right: 25px;
        }

    </style>

</head>
<body>
<?php
include_once("database-functions.php");

session_start();
$temp = $_SESSION['POST'];
$userID = $temp['userID'];



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
    $result = executePlainSQL("SELECT * FROM Show WHERE bandname = '" . $bandname . "'");
    $count = executePlainSQL("SELECT Count(*) FROM Show WHERE bandname = '" . $bandname . "'");

    if ($rowCount = oci_fetch_row($count)) {
        if ($rowCount[0] == "0") {
            echo "<p>No shows for that performer. Make sure the capitalization is correct!</p><br> <br> <br>";
        } else {
            printShowQueryResults($result);
        }
    }

}

function handleSearchShowsVenueRequest()
{
    $venue = $_POST['searchShowsVenueRequest'];
    $venue = trim($venue);
    $result = executePlainSQL("SELECT * FROM Show WHERE venueaddress = '" . $venue . "'");
    $count = executePlainSQL("SELECT Count(*) FROM Show WHERE venueaddress = '" . $venue . "'");

    if ($rowCount = oci_fetch_row($count)) {
        if ($rowCount[0] == "0") {
            echo "<p>No shows for that venue address. Make sure the capitalization is correct!</p><br> <br> <br>";
        } else {
            printShowQueryResults($result);
        }
    }
}

function handleSearchShowsEventRequest()
{
    $event = $_POST['searchShowsEventRequest'];
    $event = trim($event);
    $result = executePlainSQL("SELECT * FROM Show WHERE eventname = '" . $event . "'");
    $count = executePlainSQL("SELECT Count(*) FROM Show WHERE eventname = '" . $event . "'");

    if ($rowCount = oci_fetch_row($count)) {
        if ($rowCount[0] == "0") {
            echo "<p>No shows for that event. Make sure the capitalization is correct!</p><br> <br> <br>";
        } else {
            printShowQueryResults($result);
        }
    }

}

function handleSearchTicketsRequest()
{
    $venue = $_POST['searchTicketsVenueRequest'];
    $date = $_POST['searchTicketsDateRequest'];
    $time = $_POST['searchTicketsTimeRequest'];
    echo "<p>" . $venue . "@" . $date . " " . $time . "</p>";
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
        if (array_key_exists("searchTickets", $_POST)) {
            handleSearchTicketsRequest();
        }
    }

    disconnectFromDB();
}


?>


<div class="navbar">
    <a href="landingpage.php">Home</a>
    <a href="#">Purchased Tickets</a>
    <a href="#search">Search for shows</a>
    <a href="#buy">Buy Tickets</a>
</div>

<div class="main">
    <h1>Hello, <?php echo $userID ?>!</h1>


    <h2 id="Purchased Tickets">Purchased Tickets</h2>
    <form action="#" method="post">
        <input type="hidden" name="userID" value=<?php echo $userID ?>>
        <input type="hidden" name="viewTicketsRequest">
        <input type="submit" name="viewTickets" value="View Tickets">
    </form>

    <br>

    <?php
    if (isset($_POST['viewTicketsRequest'])) {
        handlePOSTRequest();
    } else {
        echo "<br> <br> <br> <br> <br> <br> <br> <br> <br> <br>";
    }
    ?>

    <br>
    <hr>

    
    <a class="anchor" id="search"></a>
    <h2>Search for shows by</h2>

    <p>Performers and bands:</p>
    <form action="#search" method="post">
        <input type="hidden" name="userID" value=<?php echo $userID ?>>
        <input type="text" name="searchShowsBandRequest">
        <input type="submit" value="Search" name="searchShowsBand">
    </form>
    <p>Venue address:</p>
    <form action="#search" method="post">
        <input type="hidden" name="userID" value=<?php echo $userID ?>>
        <input type="text" name="searchShowsVenueRequest">
        <input type="submit" , value="Search" name="searchShowsVenue">
    </form>
    <p>Event name:</p>
    <form action="#search" method="post">
        <input type="hidden" name="userID" value=<?php echo $userID ?>>
        <input type="text" name="searchShowsEventRequest">
        <input type="submit" value="Search" name="searchEventVenue">
    </form>

    <br>

    <?php
    if (isset($_POST['searchShowsBandRequest']) || isset($_POST['searchShowsVenueRequest']) || isset($_POST['searchShowsEventRequest'])) {
        handlePOSTRequest();
    } else {
        echo "<br> <br> <br> <br> <br> <br> <br> <br> <br> <br>";
    }
    ?>
    
    <br>
    <hr>

    <a class="anchor" id="buy"></a>
    <h2>Purchase Tickets</h2>
    <p>Search for available tickets by venue address, date and time.</p>
    <div>
        <form action="#buy" method="post">
            <input type="hidden" name="userID" value=<?php echo $userID ?>>
            <input type="text" name="searchTicketsVenueRequest">
            <input type="date" name="searchTicketsDateRequest">
            <input type="time" name="searchTicketsTimeRequest">
            <input type="submit" value="Search" name="searchTickets">
        </form>
    </div>
    <p>Enter the ticket ID number you would like to purchase.</p>
    <form action="#buy" method="post">
        <input type="hidden" name="userID" value=<?php echo $userID ?>>
        <input type="text" name="purchaseTicketRequest">
        <input type="submit" value="Purchase" name="purchaseTicket">
    </form>

    <br>

    <?php
    if (isset($_POST['searchTickets'])) {
        handlePOSTRequest();
    } else {
        echo "<br> <br> <br> <br> <br> <br> <br> <br> <br> <br>";
    }
    ?>
    
    <br>
    <hr>

</div>


</body>
</html>