<!DOCTYPE html>
<html>
<head>
    <title>Band - Showtime!</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
    <link rel="stylesheet" href="css/band-style.css">
    <link rel="stylesheet" href="css/navbar-style.css">
    <link rel="stylesheet" href="css/input-style.css">
    <link rel="stylesheet" href="css/mainpage-style.css">
    
    <style>
        input[type="date"] {
            margin-top: 5px;
        }

        #attribute {
            visibility: visible;
        }


        .form-container {
            display: table;
        }

        .inline-form {
            display: table-cell;
        }

        
        
    </style>
</head>
<body>

    <?php
    include_once("database-functions.php");

    ob_start();
    session_start();
    $temp = $_SESSION['POST'];
    $bandname = $temp['bandName'];

    $linebreaks = "<br> <br> <br> <br> <br>";

    // check if $bandname exists as an entry in the DB, go back home if not
    checkLogin($bandname, "b");


    function printMemberTable($type, $result) {
        echo "<table>";
        echo "<tr>
                <th>Name</th>
                <th>Date of Birth</th>\n";

        if ($type == "musician") {
            echo "<th>Instrument</th>\n";
        } else if ($type == "technician") {
            echo "<th>Specialty</th>\n";
        } 

        echo "<th>Start Date</th><th>Active?</th></tr>";

        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<tr><td>" . $row['MEMBERNAME'] . "</td><td>" . $row['MEMBERDOB'] . "</td>";
            if ($type == "musician") {
                echo "<td>" . $row['INSTRUMENT'] . "</td>\n";
            } else if ($type == "technician") {
                echo "<td>" . $row['SPECIALTY'] . "</td>\n";
            }
            echo "<td>" . $row['STARTDATE'] . "</td><td>" . $row['ACTIVE'] . "</td></tr>\n";
        }

        echo "</table><br>";

    }

    function handleViewMembersRequest() {
        global $bandname;

        $musicians = executePlainSQL("SELECT m.memberName, m.memberDOB, m.instrument, w.active, x.startdate FROM Musician m, WorksFor w, BandMember x
                                    WHERE m.membername = w.membername AND m.memberDOB = w.memberDOB 
                                    AND m.membername = x.membername AND m.memberDOB = x.memberDOB 
                                    AND w.bandname = '" . $bandname ."'");
        $managers = executePlainSQL("SELECT m.memberName, m.memberDOB, w.active, x.startdate FROM Manager m, WorksFor w, BandMember x 
                                    WHERE m.membername = w.membername AND m.memberDOB = w.memberDOB 
                                    AND m.membername = x.membername AND m.memberDOB = x.memberDOB 
                                    AND w.bandname = '" . $bandname ."'");
        $technicians = executePlainSQL("SELECT t.memberName, t.memberDOB, t.specialty, w.active, x.startdate FROM Technician t, WorksFor w, BandMember x 
                                    WHERE t.membername = w.membername AND t.memberDOB = w.memberDOB 
                                    AND t.membername = x.membername AND t.memberDOB = x.memberDOB 
                                    AND w.bandname = '" . $bandname ."'");

        echo "<p>Musicians</p>";
        printMemberTable("musician", $musicians);
        echo "<p>Managers</p>";
        printMemberTable("manager", $managers);
        echo "<p>Technicians</p>";
        printMemberTable("technician", $technicians);
        
    }

    function handleAddMemberRequest() {
        global $bandname;
        global $db_conn, $success;

        $membername = trim($_POST['memberName']);
        $memberDOB = $_POST['memberDOB'];
        $role = $_POST['role'];
        $attribute = "na";
        if ($role != "manager") {
            $attribute = $_POST['attribute'];
        }

        if (!$membername || !$memberDOB || !$attribute) {
            echo "<p>Please fill out all of the fields.</p>";
        }
        else if (!sanitizeInput($membername) || !sanitizeInput($attribute)) {
            echo "<p>Special characters are not allowed / Input length limit reached!</p>";
        }
        else {
            $retrievedMember = executePlainSQL("SELECT memberName, memberDOB FROM BandMember WHERE memberName = '" . $membername . "' AND memberDOB = DATE '" . $memberDOB . "'");
            $fetchedMember = oci_fetch_row($retrievedMember);
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

            $retrievedWorksFor = executePlainSQL("SELECT memberName, memberDOB FROM WorksFor WHERE memberName = '" . $membername . "' AND memberDOB = DATE '" . $memberDOB . "' AND bandname = '" . $bandname . "'");
            $fetchedWorksFor = oci_fetch_row($retrievedWorksFor);
            if (!$fetchedWorksFor) {
                executePlainSQL("INSERT INTO WorksFor VALUES('" . $membername . "', DATE '" . $memberDOB . "', '" . $bandname . "', 'y')");
                oci_commit($db_conn);
                echo "<p>The " . $role . " <b>" . $membername . " (" . $memberDOB .")</b> is now a member of <b>" . $bandname . "</b>!</p>";
            } else {
                echo "<p><b>" . $membername . " (" . $memberDOB . ")</b> is already a member of <b>" . $bandname . "<b>!</p>";
            }
        }

        echo "<br><br>";
    }

    function handleUpdateMemberRequest() {
        global $bandname;
        global $db_conn, $success;

        $membername = trim($_POST['memberName']);
        $memberDOB = $_POST['memberDOB'];
        $startDate = $_POST['startDate'];
        $active = $_POST['active'];

        if (!$membername || !$memberDOB || !$startDate) {
            echo "<p>Please fill out all of the fields.</p>";
        }
        else if (!sanitizeInput($membername)) {
            echo "<p>Special characters are not allowed / Input length limit reached!</p>";
        }
        else {
            $retrievedMember = executePlainSQL("SELECT memberName, memberDOB FROM BandMember WHERE memberName = '" . $membername . "' AND memberDOB = DATE '" . $memberDOB . "'");
            $fetchedMember = oci_fetch_row($retrievedMember);
            $retrievedWorksFor = executePlainSQL("SELECT memberName, memberDOB FROM WorksFor WHERE memberName = '" . $membername . "' AND memberDOB = DATE '" . $memberDOB . "' AND bandname = '" . $bandname . "'");
            $fetchedWorksFor = oci_fetch_row($retrievedWorksFor);
            
            if (!$fetchedMember || !$fetchedWorksFor) {
                echo "<p>Please enter the information of a real band member of <b>" . $bandname . "</b>.</p>";
            } else {
                executePlainSQL("UPDATE BandMember SET startDate = DATE '" . $startDate . "' WHERE memberName = '" . $membername . "' AND memberDOB = DATE '" . $memberDOB . "'");
                oci_commit($db_conn);
                executePlainSQL("UPDATE WorksFor SET active = '" . $active . "' WHERE memberName = '" . $membername . "' AND memberDOB = DATE '" . $memberDOB . "' AND bandname = '" . $bandname . "'");
                oci_commit($db_conn);
                echo "<p>Updated!</p>";
            }
        }
    }
    
    function listRecordLabels() {
        if (connectToDB()) {
            $recordLabels = executePlainSQL("SELECT recordLabelName FROM RecordLabel");
            while ($row = oci_fetch_row($recordLabels)) {
                echo "<option value=\"" . $row[0] ."\">" . $row[0] . "</option>\n";
            }
        }
        
        disconnectFromDB();
    }

    function handleRecordLabelRequest() {
        global $bandname;
        global $db_conn;

        $recordlabel = $_POST['recordlabel'];
        if ($recordlabel == "NULL") {
            executePlainSQL("UPDATE Band SET recordLabelName = NULL WHERE bandname = '" . $bandname . "'");
            oci_commit($db_conn);
            echo "<p>Updated! <b>" . $bandname . "</b> is not partnered with a Record Label.</p>";
        } else {
            executePlainSQL("UPDATE Band SET recordLabelName = '" . $recordlabel . "' WHERE bandname = '" . $bandname . "'");
            oci_commit($db_conn);
            echo "<p>Updated! <b>" . $bandname . "</b> is partnered with <i>" . $recordlabel . ".</i></p>";
        }

        echo "<br><br>";
    }

    function handleViewSongsRequest() {
        // query for songs by band
        global $bandname;
    
        echo "<table>\n";
        echo "<tr>
                <th>Song</th>
                <th>Length</th>
                <th>Genre</th>
                <th>Producer</th>
                <th>Release Date [dd-MMM-yy]</th>
              </tr>\n";

        $songs = executePlainSQL("SELECT songname, songlength, genre, producer, songdate FROM Song WHERE bandName = '" . $bandname . "'");
        while ($row = OCI_Fetch_Array($songs, OCI_BOTH)) {
            echo "<tr>
                    <td>" . $row['SONGNAME'] . "</td>
                    <td>" . $row['SONGLENGTH'] . "</td>
                    <td>" . $row['GENRE'] . "</td>
                    <td>" . $row['PRODUCER'] . "</td>
                    <td>" . $row['SONGDATE'] . "</td>
                </tr>\n";
        }

        echo "</table><br>\n";

    }

    function handleViewSongsByGenreRequest() {
        global $bandname;
    
        echo "<table>\n";
        echo "<tr>
                <th>Genre</th>
                <th>Count</th>
              </tr>\n";    
    
        $songs = executePlainSQL("SELECT genre, count(*) FROM Song WHERE bandname = '" . $bandname . "' GROUP BY genre");
        while ($row = OCI_Fetch_Array($songs, OCI_BOTH)) {
            echo "<tr>
                    <td>" . $row['GENRE'] . "</td>
                    <td>" . $row['COUNT(*)'] . "</td>
                </tr>\n";
        }

        echo "</table><br>\n";
    }

    function validSongLength($length) {
        $split = explode(":", $length);
        $count = count($split);

        if ($count != 2 || strlen($split[1]) != 2) {
            return false;
        }

        if (intval($split[1]) == 0) {
            if ($split[1] != "00") {
                return false;
            }
        }

        if (intval($split[0]) == 0) {
            if ($split[0] != "0") {
                return false;
            }
        }

        if (substr($split[0], 0, 1) == "0" && strlen($split[0]) != 1) {
            return false;
        }

        return true;
    }

    function handleAddSongRequest() {
        // add new song tuple if possible
        global $bandname;
        global $db_conn;

        $songname = $_POST['songName'];
        $length = $_POST['length'];
        $genre = $_POST['genre'];
        $producer = $_POST['producer'];
        $date = $_POST['releaseDate'];
        if ($date) {
            $date = "DATE '" . $date . "'";
        } else {
            $date = "NULL";
        }

        if (!$songname || !$length) {
            echo "<p>Please fill out all of the required fields.</p>";
        }
        else if (!sanitizeInput($songname) || !sanitizeInput($length) || !sanitizeInput($genre) || !sanitizeInput($producer)) {
            echo "<p>Special characters are not allowed / Input length limit reached!</p>";
        }
        else {
            if (!validSongLength($length)) {
                echo "<p>Enter a song length of the format X:XX, with no leading zeroes.</p>";
                return;
            }

            $retrievedSong = executePlainSQL("SELECT songname FROM Song WHERE songname = '" . $songname . "' AND bandname = '" . $bandname . "'");
            $fetchedSong = oci_fetch_row($retrievedSong);
            
            if ($fetchedSong) {
                echo "<p>You already released a song under the name <i>" . $songname . "</i>!</p>";
            } else {
                executePlainSQL("INSERT INTO Song VALUES('" . $songname . "', '" . $bandname . "', '" . $length . "', '" . $genre . "', '" . $producer . "', " . $date . ")");
                oci_commit($db_conn);
                handleViewSongsRequest();
            }
            
        }

    }


    function listVenues() {
        // list all venues in Venue table
        // can also look at listShows() below
        // important: connectToDB() and disconnectToDB() must be used
        //echo "<option value=\"placeholder\">Placeholder address - Look at recordlabel to see how to do it</option>";

        if (connectToDB()) {
            $venues = executePlainSQL("SELECT venueAddress, venueName FROM Venue");
            while ($row = oci_fetch_row($venues)) {
                echo "<option value=\"" . $row[0] ."\">" . $row[0] . "</option>\n";
            }
        }
        // Callie todo, change to also show venue name?
        
        disconnectFromDB();
    }

    function listSongs() {
        // same idea as listVenues()
        // list all of this band's songs
        //echo "<option value=\"bbbbb\">Song</option>";

        global $bandname;
        if (connectToDB()) {
            $songs = executePlainSQL("SELECT songName FROM Song WHERE bandname = '" . $bandname . "'");
            while ($row = oci_fetch_row($songs)) {
                echo "<option value=\"" . $row[0] ."\">" . $row[0] . "</option>\n";
            }
        }
        
        disconnectFromDB();
    }

    function listEvents() {
        // same idea again
        // list all events in EventTable
        
        if (connectToDB()) {
            $events = executePlainSQL("SELECT eventName FROM EventTable");
            while ($row = oci_fetch_row($events)) {
                echo "<option value=\"" . $row[0] ."\">" . $row[0] . "</option>\n";
            }
        }
        
        //Callie todo: maybe revise for city/date

        disconnectFromDB();
    }

    function listShows() {
        global $bandname;
        if (connectToDB()) {
            $shows = executePlainSQL("SELECT venueAddress, showDateTime FROM Show WHERE bandname = '" . $bandname . "'");
            while ($row = OCI_Fetch_Array($shows, OCI_BOTH)) {
                $datetime = formatDateTime($row['SHOWDATETIME']);
                
                $date = $datetime[0];
                //https://www.php.net/manual/en/function.date-create.php
                //https://www.php.net/manual/en/datetime.format.php
                $datetest = date_create($datetime[0]);
                $dateteststring = date_format($datetest, "Y-m-d");
                $time = $datetime[1];
                $time24 = $time;
                if ((substr($time, 0, 2) == "12" && $datetime[2] == "AM") 
                        || (substr($time, 0, 2) != "12" && $datetime[2] == "PM")) {
                    $time24 = to24($time);
                } 
                echo "<option value=\"" . $row['VENUEADDRESS'] . "@" . $dateteststring . "@" . $time24  . "\">" . $row['VENUEADDRESS'] . "@" . $dateteststring . "@" . $time . " " . $datetime[2] .  "</option>\n";
            }
        }

        disconnectFromDB();
    }

    function handleBookShowRequest() {
        // big function here
        // if all inputs are good (sanitize, required are not null), add to
        //    - Show
        //    - Books
        //    - PlayedIn
        // handle insert errors
        // look at handleUpdateMemberRequest() for general flow of handling errors
        

        global $bandname;
        global $db_conn, $success;

        $venue = $_POST['venue'];
        
        $showDate = $_POST['showDate'];
        $showTime = $_POST['showTime'];

        

        $managerName = trim($_POST['managerName']);
        $managerDOB = $_POST['managerDOB'];

        $song = $_POST['song'];
       
        //optional stuff
        $showName = trim($_POST['showName']);

        $eventName = $_POST['eventName'];
        $eventDate = $_POST['eventDate'];

        $eventvalid = true;
        $eventnull = false;
        if ($eventName == "None") {
            $eventName = NULL;
            $eventnull = true;
        }

        if (!$eventDate) {
            $eventDate = "1900-11-30";
            $eventnull = true;
        }

        if (!$venue || !$showDate || !$showTime || !$managerName || !$managerDOB || !$song) {
            echo "<p>Please fill out all of the required fields.</p>";
        }
        else if (!sanitizeInput($showName) || !sanitizeInput($managerName)) {
            echo "<p>Special characters are not allowed / Input length limit reached!</p>";
        }
        else {
            //make sure event exists if not blank
            //make sure manager exists/ is a manager
            //make sure manager is a manager
            //make sure manager works for band
            //make sure show doesn't already exist
            //otherwise book show
            //insert into  playedin
            //insert into books

            if (!$eventnull) {
                $retrievedEvent = executePlainSQL("SELECT eventName FROM EventTable WHERE eventName = '" . $eventName . "' AND eventDate = DATE '" . $eventDate . "'");
                $fetchedEvent = oci_fetch_row($retrievedEvent);

                if (!$fetchedEvent) {
                    $eventvalid = false;
                }
            }

            
            
            $showTimestamp = $showDate . " " . $showTime . ":00";
            
            $retrievedManager = executePlainSQL("SELECT memberName, memberDOB FROM Manager WHERE memberName = '" . $managerName . "' AND memberDOB = DATE '" . $managerDOB . "'");
            $fetchedManager = oci_fetch_row($retrievedManager);

            $retrievedWorksFor = executePlainSQL("SELECT memberName, memberDOB FROM WorksFor WHERE memberName = '" . $managerName . "' AND memberDOB = DATE '" . $managerDOB . "' AND bandname = '" . $bandname . "'");
            $fetchedWorksFor = oci_fetch_row($retrievedWorksFor);

            $retrievedShow = executePlainSQL("SELECT venueAddress, showDateTime FROM Show WHERE venueAddress = '" . $venue . "' AND showDateTime = TIMESTAMP '" . $showTimestamp . "'");
            $fetchedShow = oci_fetch_row($retrievedShow);
            
            if (!$fetchedManager || !$fetchedWorksFor) {
                echo "<p>Please enter the information of a real manager of <b>" . $bandname . "</b>.</p>";
            } else if (!$eventvalid) {
                echo "<p>Please enter a valid event or leave the field blank.</p>";
            } else if ($fetchedShow) {
                echo "<p>A show at that time and place already exists!<p>";
            } else {
                //insert into show
                executePlainSQL("INSERT INTO Show VALUES('" . $venue . "', TIMESTAMP '" . $showTimestamp . "', '" . $showName . "', '" . $bandname . "', '" . $eventName . "', DATE '" . $eventDate . "')");
                oci_commit($db_conn);

                //insert into playedin
                executePlainSQL("INSERT INTO PlayedIn VALUES ('" . $song . "', '" . $bandname . "', '" . $venue . "', TIMESTAMP '" . $showTimestamp . "')");
                oci_commit($db_conn);

                //insert into books
                executePlainSQL("INSERT INTO Books VALUES ('" . $managerName . "', DATE '" . $managerDOB . "', '" . $venue . "', TIMESTAMP '" . $showTimestamp . "')");
                oci_commit($db_conn);
                
                echo "<p>Show created!</p>";
            }
        }        
    }

    function handleViewSetlistRequest() {
        global $bandname;
        $show = $_POST['showVenueDateTime'];
        $split = explode("@", $show);
        $venue = $split[0];
        $date = $split[1];
        $time = $split[2];
        
        echo "<p>Setlist for <b>" . $venue . "@" . $date . " " . $time . "</b>:</p>";

        echo "<table>\n";
        echo "<tr>
                <th>Song</th>
                <th>Length</th>
                <th>Genre</th>
                <th>Producer</th>
                <th>Release Date [dd-MMM-yy]</th>
              </tr>\n";
        

        $songs = executePlainSQL("SELECT s.songname, songlength, genre, producer, songdate FROM Song s, PlayedIn p
                    WHERE venueaddress = '" . $venue . "' AND showdatetime = TIMESTAMP '" . $date . " " . $time . ":00' AND s.songname = p.songname AND s.bandname = p.bandname AND s.bandName = '" . $bandname . "'");
        while ($row = OCI_Fetch_Array($songs, OCI_BOTH)) {
            echo "<tr>
                    <td>" . $row['SONGNAME'] . "</td>
                    <td>" . $row['SONGLENGTH'] . "</td>
                    <td>" . $row['GENRE'] . "</td>
                    <td>" . $row['PRODUCER'] . "</td>
                    <td>" . $row['SONGDATE'] . "</td>
                </tr>\n";
        }

        echo "</table><br>\n";
    }

    function handleAddToSetlistRequest() {
        global $bandname, $db_conn;

        $show = $_POST['showVenueDateTime'];
        $split = explode("@", $show);
        $venue = $split[0];
        $date = $split[1];
        $time = $split[2];        
        
        $song = $_POST['song'];

        $checkSetlist = executePlainSQL("SELECT songname from PlayedIn WHERE songname = '" . $song . "' AND bandname = '" . $bandname . "' 
                        AND venueaddress = '" . $venue . "' AND showdatetime = TIMESTAMP '" . $date . " " . $time . ":00'");
        $row = oci_fetch_row($checkSetlist);
        if ($row) {
            echo "<p><i>" . $song . "</i> is already part of the show's setlist!</p>";
        } else {
            echo "<p><i>" . $song . "</i> added to the show's setlist!</p>";
            executePlainSQL("INSERT INTO PlayedIn VALUES('" . $song . "', '" . $bandname . "', '" . $venue . "', TIMESTAMP '" . $date . " " . $time . ":00')");
            oci_commit($db_conn);
        }
        
        handleViewSetlistRequest();

    }

    function handleViewTicketsRequest() {
        global $bandname, $db_conn, $success;

        executePlainSQL("CREATE VIEW Available(venueAddress, showDateTime, countAvailable) AS
                    SELECT s.venueAddress, s.showDateTime, Count(*) FROM Show s, TicketID t WHERE 
                    s.bandname = '" . $bandname . "' AND s.venueaddress = t.venueaddress AND s.showdatetime = t.showdatetime AND userID is NULL
                    GROUP BY s.venueAddress, s.showDateTime");
                    
        executePlainSQL("CREATE VIEW AllTickets(venueAddress, showDateTime, countAll) AS
                    SELECT s.venueAddress, s.showDateTime, Count(*) FROM Show s, TicketID t WHERE 
                    s.bandname = '" . $bandname . "' AND s.venueaddress = t.venueaddress AND s.showdatetime = t.showdatetime 
                    GROUP BY s.venueAddress, s.showDateTime");
            
        $result = executePlainSQL("SELECT venueaddress, showdatetime, countAvailable, countAll 
                    FROM AllTickets a NATURAL LEFT OUTER JOIN Available b");
        
        echo "<table>\n";
        echo "<tr>
                <th>Venue Address</th>
                <th>Date</th>
                <th>Time</th>
                <th>Unpurchased</th>
                <th>Total</th>
              </tr>\n";
        
        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            $datetime = formatDateTime($row['SHOWDATETIME']);
            $available = $row['COUNTAVAILABLE'];
            if (!$available) {
                $available = 0;
            }
            echo "<tr>
                    <td>" . $row['VENUEADDRESS'] . "</td>
                    <td>" . $datetime[0] . "</td>
                    <td>" . $datetime[1] . " " . $datetime[2] . "</td>
                    <td>" . $available . "</td>
                    <td>" . $row['COUNTALL'] . "</td>
                  </tr>\n";
        }

        echo "</table><br><br>";
        
        executePlainSQL("DROP VIEW Available");
        executePlainSQL("DROP VIEW AllTickets");
    }

    function handleCreateTicketsRequest() {
        // dont forget to split $_POST['showVenueDateTime'] into the 2 parts to identify Show
        //      - prob use explode("@", ...)
        // concertgoer.php has example on how to query using TIMESTAMP in handleSearchTicketsRequest()
        // also remember to add to all 3 Ticket tables
        //

        global $db_conn;

        $price = $_POST['price'];
        $number = $_POST['number'];
        $type = $_POST['type'];

        $showVenueDateTime = $_POST['showVenueDateTime'];
        if(!$showVenueDateTime || !$price || !$number) {
            echo "<p>Please fill out all of the required fields.</p>";
        } else if ($number < 0) {
            echo "<p>Price cannot be less than $0.</p>";
        }
        else {
            //https://stackoverflow.com/questions/885241/php-string-indexing
            $letter = $type[0];

            // https://www.php.net/manual/en/function.explode.php
            $explodedShowVenueDateTime = explode("@", $showVenueDateTime);
            $venue = $explodedShowVenueDateTime[0];

            $retrievedCapacity = executePlainSQL("SELECT capacity FROM Venue WHERE venueAddress = '" . $venue . "'");
            $capacity = intval(oci_fetch_row($retrievedCapacity)[0]);
            
            $showDate = $explodedShowVenueDateTime[1];
            $showTime = $explodedShowVenueDateTime[2];

            $showTimestamp = $showDate . " " . $showTime . ":00";
            //echo "<p>" . $showVenueDateTime . $showTime . $showTimestamp . "</p>";
            

            $nextSeatNum = 1;
            
            // https://www.w3schools.com/php/php_looping_for.asp
            for ($i = 0; $i < $number; $i++) {
                $retrievedTicketID = executePlainSQL("SELECT count(ticketID) FROM TicketID");
                
                $ticketID = oci_fetch_row($retrievedTicketID);
                // https://www.php.net/manual/en/function.intval.php
                $newTicketID = intval($ticketID[0]) + 1;
                
                $seatNum = $letter . $nextSeatNum;
                //echo "<p> ". $newTicketID . $seatNum . $capacity . $type ."</p>";
                $retrievedTaken = executePlainSQL("SELECT * FROM TicketID WHERE seatNum = '" . $seatNum . "' AND venueAddress = '" . $venue . "' AND showDateTime = TIMESTAMP '" . $showTimestamp . "'");
                $taken = oci_fetch_row($retrievedTaken);

                if ($nextSeatNum > $capacity) {
                    echo "<p>Ran out of space while issuing tickets</p>";
                    break;
                }
                
                while($taken) {
                    
                    if ($nextSeatNum > $capacity) {
                        //https://www.php.net/manual/en/control-structures.break.php
                        echo "<p>Ran out of space while issuing tickets</p>";
                        break 2;
                    }
                    
                    $nextSeatNum++;
                    $seatNum = $letter . $nextSeatNum;

                    $retrievedTaken = executePlainSQL("SELECT * FROM TicketID WHERE seatNum = '" . $seatNum . "' AND venueAddress = '" . $venue . "' AND showDateTime = TIMESTAMP '" . $showTimestamp . "'");
                    $taken = oci_fetch_row($retrievedTaken);
                }
                $retrievedTicketType = executePlainSQL("SELECT * FROM TicketType WHERE seatNum = '" . $seatNum . "'");
                $fetchedTicketType = oci_fetch_row($retrievedTicketType);
                if (!$fetchedTicketType) {
                    //echo "<p> test </p>";
                    //insert into TicketType
                    executePlainSQL("INSERT INTO TicketType VALUES ('" . $seatNum . "', '" . $type . "')");
                    //echo "<p> test2 </p>";
                    oci_commit($db_conn);
                    //echo "<p> test3 </p>";
                }
                
                
                //Insert into TicketPrice
                executePlainSQL("INSERT INTO TicketPrice VALUES ('" . $seatNum . "', '" . $venue . "', TIMESTAMP '" . $showTimestamp . "', " . $price . ")");
                oci_commit($db_conn);

                //Insert into TicketID
                executePlainSQL("INSERT INTO TicketID VALUES ('" . $newTicketID . "', '" . $seatNum . "', NULL, '" . $venue . "', TIMESTAMP '" . $showTimestamp . "')");
                oci_commit($db_conn);
                
            }
            echo "<p>Tickets created!</p>";
        }
        

        
    }

    function handlePOSTRequest() {

        if (connectToDB()) {
            if (array_key_exists("viewMembersRequest", $_POST)) {
                handleViewMembersRequest();
            }
            if (array_key_exists("addMemberRequest", $_POST)) {
                handleAddMemberRequest();
            }
            if (array_key_exists("updateMemberRequest", $_POST)) {
                handleUpdateMemberRequest();
            }
            if (array_key_exists("recordLabelRequest", $_POST)) {
                handleRecordLabelRequest();
            }
            if (array_key_exists("viewSongsRequest", $_POST)) {
                handleViewSongsRequest();
            }
            if (array_key_exists("viewSongsByGenreRequest", $_POST)) {
                handleViewSongsByGenreRequest();
            }
            if (array_key_exists("addSongRequest", $_POST)) {
                handleAddSongRequest();
            }
            if (array_key_exists("bookShowRequest", $_POST)) {
                handleBookShowRequest();
            }
            if (array_key_exists("viewSetlistRequest", $_POST)) {
                handleViewSetlistRequest();
            } 
            if (array_key_exists("addToSetlistRequest", $_POST)) {
                handleAddToSetlistRequest();
            }
            if (array_key_exists("viewTicketsRequest", $_POST)) {
                handleViewTicketsRequest();
            }
            if (array_key_exists("createTicketsRequest", $_POST)) {
                handleCreateTicketsRequest();
            }
        }

        disconnectFromDB();
    }

    ?>

    <div class="navbar">
        <a href="landingpage.php">Home</a>
        <a href="#view-members">View Members</a>
        <a href="#add-member">Add Members</a>
        <a href="#update-member">Update Members</a>
        <a href="#record-label">Record Label</a>
        <a href="#songs">Songs</a>
        <a href="#book-show">Book Show</a>
        <a href="#setlist">Setlists</a>
        <a href="#tickets">Tickets</a>
    </div>

    <div class="main">
        <div class="page-title">
            <h1>Hello, <?php echo $bandname ?>!</h1>
        </div>
        <div class="section">
            <a class="anchor" id="view-members"></a>
            <h2>View Band Members</h2>
            <form action="#view-members" method="post">
                <input type="hidden" name="viewMembersRequest">
                <button type="submit">View</button>
            </form>
            <br>

            <?php
                if (isset($_POST['viewMembersRequest'])) {
                    handlePOSTRequest();
                } else {
                    echo $linebreaks;
                }
            ?>

            <br>
        </div>
        <hr>
        <div class="section">
            <a class="anchor" id="add-member"></a>
            <h2>Add Band Members to Band</h2>
            <form action="#add-member" method="post">
                <input type="text" name="memberName" placeholder="Name"> <br>
                <label>Date of Birth<br><input type="date" name="memberDOB" placeholder="DOB"></label><br>
                <label><input id="musician" type="radio" name="role" value="musician" checked=true>Musician </label><br>
                <label><input id="manager" type="radio" name="role" value="manager">Manager </label><br>
                <label><input id="technician" type="radio" name="role" value="technician">Technician </label><br>
                <input id="attribute" type="text" name="attribute" placeholder="Instrument"> <br>
                <input type="hidden" name="addMemberRequest">
                <button type="submit" style="width: 60px;">Add</button>
            </form>
            <br>

            <?php
                if (isset($_POST['addMemberRequest'])) {
                    handlePOSTRequest();
                } else {
                    echo $linebreaks;
                }
            ?>

            <br>
        </div>
        <hr>
        <div class="section">
            <a class="anchor" id="update-member"></a>
            <h2>Update Band Member Information</h2>
            <form action="#update-member" method="post">
                <p>Whose information would you like to update? Enter their name and date of birth.</p>
                <input type="text" name="memberName" placeholder="Name">
                <input type="date" name="memberDOB" placeholder="DOB"><br>
                <p>Enter the person's starting date and status.</p>
                <input type="date" name="startDate">
                <label><input type="radio" name="active" value="y" checked=true>Active</label>
                <label><input type="radio" name="active" value="n">Inactive</label>
                <input type="hidden" name="updateMemberRequest"><br>
                <button type="submit">Update</button>
            </form>
            <br>

            <?php
                if (isset($_POST['updateMemberRequest'])) {
                    handlePOSTRequest();
                } else {
                    echo $linebreaks;
                }
            ?>

            <br>
        </div>
        <hr>
        <div class="section">
            <a class="anchor" id="record-label"></a>
            <h2>Record Label</h2>
            <form action="#record-label" method="post">
                <p>Select from the list of available Record Labels that your band is partnered with.</p>
                <select name="recordlabel">
                    <option value="NULL">None</option>
                    <?php listRecordLabels() ?>
                </select>
                <input type="hidden" name="recordLabelRequest">
                <button type="submit">Update</button>
            </form>
        <br>

        <?php
            if (isset($_POST['recordLabelRequest'])) {
                handlePOSTRequest();
            } else {
                echo $linebreaks;
            }
        ?>

        <br>
        </div>
        <hr>
        <div class="section">
            <a class="anchor" id="songs"></a>
            <h2>Manage Songs</h2>
            <p>View your band's songs.</p>
            <div class="form-container">
                <form class="inline-form" action="#songs" method="post">
                    <input type="hidden" name="viewSongsRequest">
                    <button type="submit">View All Songs</button>
                </form>
                <form class="inline-form" action="#songs" method="post">
                    <input type="hidden" name="viewSongsByGenreRequest">
                    <button type="submit">View By Genre</button>
                </form>   
            </div>        
            <br>
            <p>Add a new song to your discography.</p>
            <form action="#songs" method="post">
                <input type="text" name="songName" placeholder="Song Name">
                <input type="text" name="length" placeholder="Length (e.g. 3:24)" style="width: 10%;"><br>
                <p>Optional information:</p>
                <input type="text" name="genre" placeholder="Genre">
                <input type="text" name="producer" placeholder="Producer Name"><br>
                <label>Release Date<br><input type="date" name="releaseDate"></label>
                <input type="hidden" name="addSongRequest">
                <button type="submit">Add Song</button>
            </form>
            <br>

            <?php
                if (isset($_POST['viewSongsRequest']) || isset($_POST['viewSongsByGenreRequest']) || isset($_POST['addSongRequest'])) {
                    handlePOSTRequest();
                } else {
                    echo $linebreaks;
                }
            ?>

            <br>
        </div>
        <hr>
        <div class="section">
            <a class="anchor" id="book-show"></a>
            <h2>Book a Show</h2>
            <p>
                Select a venue and a date and time for your show. <br>
                Book under one of your band's manager's name and date of birth.
            </p>
            <form action="#book-show" method="post">
                <p>Venue, Date and Time:</p>
                <select name="venue">
                    <?php listVenues() ?>
                </select>
                <input type="date" name="showDate" min="2000-01-01">
                <input type="time" name="showTime">
                <br>
                <p>Manager Information:</p>
                <input type="text" name="managerName" placeholder="Manager Name">
                <input type="date" name="managerDOB">
                <br>
                <p>Select a song to perform at this show.</p>
                <select name="song">
                    <?php listSongs() ?>
                </select>
                <br>
                <p>Optional information:</p>
                <input type="text" name="showName" placeholder="Show Name">
                <br>
                <label>
                    Event name and date<br>
                    <select name="eventName">
                        <option value="">None</option>
                        <?php listEvents() ?>
                    </select>
                    <input type="date" name="eventDate">
                </label>
                <br>
                <input type="hidden" name="bookShowRequest">
                <button type="submit">Book Show</button>
            </form>
            <br>

            <?php
                if (isset($_POST['bookShowRequest'])) {
                    handlePOSTRequest();
                } else {
                    echo $linebreaks;
                }
            ?>

            <br>
        </div>
        <hr>
        <div class="section">
            <a class="anchor" id="setlist"></a>
            <h2>Setlists</h2>
            <p>View the setlist for one of your shows.</p>
            <form action="#setlist" method="post">
                <select name="showVenueDateTime">
                    <?php listShows() ?>
                </select>
                <input type="hidden" name="viewSetlistRequest">
                <button type="submit">View Setlist</button>
            </form>
            <br>
            <p>Add a song to a show's setlist.</p>
            <form action="#setlist" method="post">
                <select name="showVenueDateTime">
                    <?php listShows() ?>
                </select>
                <br>
                <select name="song">
                    <?php listSongs() ?>
                </select>    
                <input type="hidden" name="addToSetlistRequest">
                <button type="submit">Add to Setlist</button>
            </form>

            <?php
                if (isset($_POST['viewSetlistRequest']) || isset($_POST['addToSetlistRequest'])) {
                    handlePOSTRequest();
                } else {
                    echo $linebreaks;
                }
            ?>

            <br>
        </div>
        <hr>
        <div class="section">
            <a class="anchor" id="tickets"></a>
            <h2>Manage Tickets</h2>
            <p>View how many tickets have been sold for your shows.</p>
            <form action="#tickets" method="post">
                <input type="hidden" name="viewTicketsRequest">
                <button type="submit">View</button>
            </form>
            <br>
            <p>
                Create tickets for a show.<br>
                Select a number of tickets to make, the type of ticket, and the price of each.
            </p>
            <form action="#tickets" method="post">
                <select name="showVenueDateTime">
                    <?php listShows() ?>
                </select>
                <br>
                <input type="number" name="number" placeholder="How many?">
                <select name="type">
                    <option value="Floor">Floor</option>
                    <option value="Balcony">Balcony</option>
                    <option value="Upper">Upper</option>
                    <option value="Lower">Lower</option>
                </select>
                <input type="number" name="price" placeholder="Price ($)">
                <br>
                <input type="hidden" name="createTicketsRequest">
                <button type="submit">Create Tickets</button>
            </form>
            <br>

            <?php
                if (isset($_POST['viewTicketsRequest']) || isset($_POST['createTicketsRequest'])) {
                    handlePOSTRequest();
                } else {
                    echo $linebreaks;
                }
            ?>

            <br>
        </div>
        <hr>
    </div>

    <script src="scripts/bandmember.js"></script>
</body>
</html>