<!DOCTYPE html>
<!-- https://stackoverflow.com/questions/871858/php-pass-variable-to-next-page -->
<!-- https://stackoverflow.com/questions/6833914/how-to-prevent-the-confirm-form-resubmission-dialog -->
<html>
<head>
    <title>Concert Goer</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
    <link rel="stylesheet" href="concertgoer-style.css">
    <link rel="stylesheet" href="navbar-style.css">
    <style>
        body {
            margin: 0;
            font-family: "Lato", sans-serif;
            color: steelblue;
        }

        hr {
            border: 1px solid steelblue;
        }
        
        table {
            text-align: left;
        }

        th, td {
            padding-right: 25px;
            color: black;
            font-size: 20px;
        }

        input[type="text"] {
            width: 20%;
        }


        a.anchor {
            top: -50px;
            display: block;
            position: relative;
            visibility: hidden;
        }

        .main {
            padding: 16px;
            margin-top: 30px;
        }

        .page-title {
            text-align: center;
            padding: 20px;
            font-size: 24px;
        }

        .section {
            padding: 10px;
        }

        .section h2 {
            font-size: 30px;
        }

        .section p {
            font-size: 20px;
        }

        .search-by {
            margin: 20px;
            margin-left: 0px;
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
    $result = executePlainSQL("SELECT * FROM Show WHERE regexp_like(bandname, '^" . $bandname . "$', 'i')");
    $count = executePlainSQL("SELECT Count(*) FROM Show WHERE regexp_like(bandname, '^" . $bandname . "$', 'i')");

    if ($rowCount = oci_fetch_row($count)) {
        if ($rowCount[0] == "0") {
            echo "<p>No shows for that performer.</p><br> <br> <br>";
        } else {
            printShowQueryResults($result);
        }
    }

}

function handleSearchShowsVenueRequest()
{
    $venue = $_POST['searchShowsVenueRequest'];
    $venue = trim($venue);
    $result = executePlainSQL("SELECT * FROM Show WHERE regexp_like(venueaddress, '^" . $venue . "$', 'i')");
    $count = executePlainSQL("SELECT Count(*) FROM Show WHERE regexp_like(venueaddress, '^" . $venue . "$', 'i')");

    if ($rowCount = oci_fetch_row($count)) {
        if ($rowCount[0] == "0") {
            echo "<p>No shows for that venue address. Make sure you typed the address fully and correctly!</p><br> <br> <br>";
        } else {
            printShowQueryResults($result);
        }
    }
}

function handleSearchShowsEventRequest()
{
    $event = $_POST['searchShowsEventRequest'];
    $event = trim($event);
    $result = executePlainSQL("SELECT * FROM Show WHERE regexp_like(eventname, '^" . $event . "$', 'i')");
    $count = executePlainSQL("SELECT Count(*) FROM Show WHERE regexp_like(eventname, '^" . $event . "$', 'i')");

    if ($rowCount = oci_fetch_row($count)) {
        if ($rowCount[0] == "0") {
            echo "<p>No shows for that event.</p><br> <br> <br>";
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
    <div class="page-title">
        <h1>Hello, <?php echo $userID ?>!</h1>
    </div>

    <div class="section">
        <h2 id="Purchased Tickets">Purchased Tickets</h2>
        <form action="#" method="post">
            <input type="hidden" name="userID" value=<?php echo $userID ?>>
            <input type="hidden" name="viewTicketsRequest">
            <button type="submit">View Tickets</button>
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
            echo "<br> <br> <br> <br> <br> <br> <br> <br> <br> <br>";
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
        <br>
        <p>Enter the ticket ID number you would like to purchase</p>
        <form action="#buy" method="post">
            <input type="hidden" name="userID" value=<?php echo $userID ?>>
            <input type="text" name="purchaseTicketRequest" style="width: 5%; text-align: right;" placeholder="ID Number">
            <button type="submit">Purchase</button>
        </form>

        <br>

        <?php
        if (isset($_POST['searchTicketsVenueRequest'])) {
            handlePOSTRequest();
        } else {
            echo "<br> <br> <br> <br> <br> <br> <br> <br> <br> <br>";
        }
        ?>
        
        <br>
    </div>
</div>


</body>
</html>