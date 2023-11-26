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
        
    </style>
</head>
<body>

    <?php
    include_once("database-functions.php");

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

        echo "<th>Active?</th></tr>";

        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<tr><td>" . $row['MEMBERNAME'] . "</td><td>" . $row['MEMBERDOB'] . "</td>";
            if ($type == "musician") {
                echo "<td>" . $row['INSTRUMENT'] . "</td>\n";
            } else if ($type == "technician") {
                echo "<td>" . $row['SPECIALTY'] . "</td>\n";
            }
            echo "<td>" . $row['ACTIVE'] . "</td></tr>\n";
        }

        echo "</table><br>";

    }

    function handleViewMembersRequest() {
        global $bandname;

        $musicians = executePlainSQL("SELECT m.memberName, m.memberDOB, m.instrument, w.active FROM Musician m, WorksFor w WHERE m.membername = w.membername AND m.memberDOB = w.memberDOB AND w.bandname = '" . $bandname ."'");
        $managers = executePlainSQL("SELECT m.memberName, m.memberDOB, w.active FROM Manager m, WorksFor w WHERE m.membername = w.membername AND m.memberDOB = w.memberDOB AND w.bandname = '" . $bandname ."'");
        $technicians = executePlainSQL("SELECT t.memberName, t.memberDOB, t.specialty, w.active FROM Technician t, WorksFor w WHERE t.membername = w.membername AND t.memberDOB = w.memberDOB AND w.bandname = '" . $bandname ."'");
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
        }

        disconnectFromDB();
    }

    ?>

    <div class="navbar">
        <a href="landingpage.php">Home</a>
        <a href="#view-members">View Members</a>
        <a href="#add-member">Add Members</a>
        <a href="#update-member">Update Members</a>
        <a href="#book-show">Book a Show</a>
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
            <a class="anchor" id="book-show"></a>
            <h2>Book a Show</h2>
        </div>
    </div>

    <script src="scripts/bandmember.js"></script>
</body>
</html>