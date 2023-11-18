<!-- https://stackoverflow.com/questions/871858/php-pass-variable-to-next-page -->
<!-- https://stackoverflow.com/questions/6833914/how-to-prevent-the-confirm-form-resubmission-dialog -->

<html>
    <head>
        <title>Concert Goer</title>

        <style>
            body {
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

            $userID = $_POST['userID'];


            function handleViewTicketsRequest() {
                global $userID;
                
                $count = executePlainSQL("SELECT Count(*) FROM ConcertGoer c, TicketID t, TicketPrice p WHERE c.userID = t.userID AND t.seatnum = p.seatnum AND c.userid = '" . $userID . "'");
                $result = executePlainSQL("SELECT t.seatnum, t.venueaddress, t.showdatetime, p.price FROM ConcertGoer c, TicketID t, TicketPrice p WHERE c.userID = t.userID AND t.seatnum = p.seatnum AND c.userid = '" . $userID . "'");

                if ($rowCount = oci_fetch_row($count)) {
                    if ($rowCount[0] == "0") {
                        echo "<p>You have not purchased any tickets.</p>";
                    } 
                    
                    else {
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
    
                        echo "</table>";
                    }
                } 
            }

            function printShowQueryResults($result) {
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
                echo "</table>";
            }

            function handleSearchShowsBandRequest() {
                $bandname = $_POST['searchShowsBandRequest'];
                $bandname = trim($bandname);
                $result = executePlainSQL("SELECT * FROM Show WHERE bandname = '" . $bandname . "'");
                $count = executePlainSQL("SELECT Count(*) FROM Show WHERE bandname = '" . $bandname . "'");

                if ($rowCount = oci_fetch_row($count)) {
                    if ($rowCount[0] == "0") {
                        echo "<p>No shows for that performer. Make sure the capitalization is correct!</p>";
                    } else {
                        printShowQueryResults($result);
                    }
                }

            }

            function handleSearchShowsVenueRequest() {
                $venue = $_POST['searchShowsVenueRequest'];
                $venue = trim($venue);
                $result = executePlainSQL("SELECT * FROM Show WHERE venueaddress = '" . $venue . "'");
                $count = executePlainSQL("SELECT Count(*) FROM Show WHERE venueaddress = '" . $venue . "'");

                if ($rowCount = oci_fetch_row($count)) {
                    if ($rowCount[0] == "0") {
                        echo "<p>No shows for that venue address. Make sure the capitalization is correct!</p>";
                    } else {
                        printShowQueryResults($result);
                    }
                }
            }

            function handleSearchShowsEventRequest() {
                $event = $_POST['searchShowsEventRequest'];
                $event = trim($event);
                $result = executePlainSQL("SELECT * FROM Show WHERE eventname = '" . $event . "'");
                $count = executePlainSQL("SELECT Count(*) FROM Show WHERE eventname = '" . $event . "'");

                if ($rowCount = oci_fetch_row($count)) {
                    if ($rowCount[0] == "0") {
                        echo "<p>No shows for that event. Make sure the capitalization is correct!</p>";
                    } else {
                        printShowQueryResults($result);
                    }
                }

            }

            function handlePOSTRequest() {
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
                }

                disconnectFromDB();
            }
            


        ?>

        <a href="landingpage.php"><h3>Return to home page</h3></a>
        <h1>Hello, <?php echo $userID ?>!</h1>
        
        <h2>Purchased Tickets</h2>
        <form method="post">
            <input type="hidden" name="userID" value=<?php echo $userID ?>>
            <input type="hidden" name="viewTicketsRequest">
            <input type="submit" name="viewTickets" value="View Tickets">
        </form>

        <?php
            if (isset($_POST['viewTicketsRequest'])) {
                handlePOSTRequest();
            }
        ?>
        
        <br>
        <hr/>
        
        <h2>Search for shows by</h2>
        <p>Performers and bands:</p>
        <form method="post">
            <input type="hidden" name="userID" value=<?php echo $userID ?>>
            <input type="text" name="searchShowsBandRequest"> 
            <input type="submit" value="Search" name="searchShowsBand">
        </form>
        <p>Venue address:</p>
        <form method="post">
            <input type="hidden" name="userID" value=<?php echo $userID ?>>
            <input type="text" name="searchShowsVenueRequest">
            <input type="submit", value="Search" name="searchShowsVenue">
        </form>
        <p>Event name:</p>
        <form method="post">
            <input type="hidden" name="userID" value=<?php echo $userID ?>>
            <input type="text" name="searchShowsEventRequest">
            <input type="submit" value="Search" name="searchEventVenue">
        </form>
        
        <?php 
            if (isset($_POST['searchShowsBandRequest']) || isset($_POST['searchShowsVenueRequest']) || isset($_POST['searchShowsEventRequest'])) {
                handlePOSTRequest();
            }
        ?>

        <br>
        <hr>

        <h2>Purchase Tickets</h2>
        <p>Search for available tickets by venue address, date and time.</p>
        <div>
            <form method="post">
                <input type="hidden" name="userID" value=<?php echo $userID ?>>
                <input type="text" name="searchTicketsVenueRequest">
                <input type="text" name="searchTicketsDateRequest">
                <input type="text" name="searchTicketsTimeRequest">
                <label><input type="radio" name="ampm" value="AM">AM</label>
                <label><input type="radio" name="ampm" value="PM">PM</label>
                <input type="submit" value="Search" name="searchTickets">
            </form>
        </div>
        <p>Enter the ticket ID number you would like to purchase.</p>
        <form method="post">
            <input type="hidden" name="userID" value=<?php echo $userID ?>>
            <input type="text" name="purchaseTicketRequest">
            <input type="submit" value="Purchase" name="purchaseTicket">
        </form>


    </body>
</html>