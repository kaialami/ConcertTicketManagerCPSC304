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
                            </tr>";
                        
                        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                            $datetime = formatDateTime($row['SHOWDATETIME']);
                            echo "<tr><td>" . $row['SEATNUM'] . "</td><td>" . $row['VENUEADDRESS'] . "</td><td>" . $datetime[0] . "</td><td>" . $datetime[1] . " " . $datetime[2] . "</td><td>" . $row['PRICE'] . "</td></tr>";
                        }
    
                        echo "</table>";
                    }
                } 
            }

            function handlePOSTRequest() {
                if (connectToDB()) {
                    if (array_key_exists("viewTicketsRequest", $_POST)) {
                        handleViewTicketsRequest();
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
        <form method="post">
            <input type="hidden" name="userID" value=<?php echo $userID ?>>
            Performers and bands:  <input type="text" name="searchShowsBandRequest"> 
            <input type="submit" value="Search" name="searchShowsBand">
        </form>
        <p>OR</p>
        <form method="post">
            <input type="hidden" name="userID" value=<?php echo $userID ?>>
            Venue address:  <input type="text" name="searchShowsVenueRequest">
            <input type="submit", value="Search" name="searchShowsVenue">
        </form>
        <p>OR</p>
        <form method="post">
            <input type="hidden" name="userID" value=<?php echo $userID ?>>
            Event name:  <input type="text" name="searchShowsEventRequest">
            <input type="submit" value="Search" name="searchEventVenue">
        </form>
        <br/>
        <p>(php will query and display results in table that appears here)</p>
    </body>
</html>