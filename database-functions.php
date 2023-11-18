<!-- Taken from CPSC304 oracle-test.php tutorial -->

<?php 
    $success = true;
    $db_conn = NULL;

    function connectToDB() {
        global $db_conn;

        // Your username is ora_(CWL_ID) and the password is a(student number). For example,
        // ora_platypus is the username and a12345678 is the password.
        $db_conn = OCILogon("ora_kaialami", "a25764333", "dbhost.students.cs.ubc.ca:1522/stu");

        if ($db_conn) {
            return true;
        } else {
            $e = OCI_Error(); // For OCILogon errors pass no handle
            echo htmlentities($e['message']);
            return false;
        }
    }

    function disconnectFromDB() {
        global $db_conn;
        OCILogoff($db_conn);
    }

    function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
        //echo "<br>running ".$cmdstr."<br>";
        global $db_conn, $success;

        $statement = OCIParse($db_conn, $cmdstr);

        if (!$statement) {
            echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
            $e = OCI_Error($db_conn); // For OCIParse errors pass the connection handle
            echo htmlentities($e['message']);
            $success = False;
        }

        $r = OCIExecute($statement, OCI_DEFAULT);
        if (!$r) {
            echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
            $e = oci_error($statement); // For OCIExecute errors pass the statementhandle
            echo htmlentities($e['message']);
            $success = False;
        }

        return $statement;
    }

    function formatDateTime($datetime) {
        $split = explode(" ", $datetime);
        $temp = $split[1];
        $split[1] = substr($split[1], 0, 5);
        $split[1] = str_replace(".", ":", $split[1]);
        return $split;
    }

?>