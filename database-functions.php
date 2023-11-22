<?php 
    // Taken from CPSC304 oracle-test.php tutorial
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
            echo "<br><p>Cannot parse the following command: " . $cmdstr . "</p><br>";
            $e = OCI_Error($db_conn); // For OCIParse errors pass the connection handle
            echo "<p>" . htmlentities($e['message'])  . "</p><br>";
            $success = False;
        }

        $r = OCIExecute($statement, OCI_DEFAULT);
        if (!$r) {
            echo "<br><p>Cannot execute the following command: " . $cmdstr . "</p><br>";
            $e = oci_error($statement); // For OCIExecute errors pass the statementhandle
            echo "<p>" . htmlentities($e['message']) . "</p><br>";
            $success = False;
        }

        return $statement;
    }

    function executeBoundSQL($cmdstr, $list) {
        /* Sometimes the same statement will be executed several times with different values for the variables involved in the query.
    In this case you don't need to create the statement several times. Bound variables cause a statement to only be
    parsed once and you can reuse the statement. This is also very useful in protecting against SQL injection.
    See the sample code below for how this function is used */

        global $db_conn, $success;
        $statement = OCIParse($db_conn, $cmdstr);

        if (!$statement) {
            echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
            $e = OCI_Error($db_conn);
            echo htmlentities($e['message']);
            $success = False;
        }

        foreach ($list as $tuple) {
            foreach ($tuple as $bind => $val) {
                //echo $val;
                //echo "<br>".$bind."<br>";
                OCIBindByName($statement, $bind, $val);
                unset ($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
            }

            $r = OCIExecute($statement, OCI_DEFAULT);
            if (!$r) {
                echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
                $e = OCI_Error($statement); // For OCIExecute errors, pass the statementhandle
                echo htmlentities($e['message']);
                echo "<br>";
                $success = False;
            }
        }
    }


    // returns array where indexes 0, 1, 2 are date, time, AM/PM, respectively
    function formatDateTime($datetime) {
        $split = explode(" ", $datetime);
        $temp = $split[1];
        $split[1] = substr($split[1], 0, 5);
        $split[1] = str_replace(".", ":", $split[1]);
        return $split;
    }

    function sanitizeInput($input) {
        if(preg_match("-([/!%$#*^&'\"()\[\]{}\-_=<>+~`]+)-", $input) || strchr($input, '\\') || strchr($input, '/')) {
            return false;
        } else if (mb_strlen($input, 'utf8') > 30){
            return false;
        } else {
            return true;
        }
    }

function sanitizeVenueInput($input) {
    if(preg_match("-([/!%$#*^&'\"()\[\]{}\-_=<>+~`]+)-", $input) || strchr($input, '\\') || strchr($input, '/')) {
        return false;
    } else if (mb_strlen($input, 'utf8') > 50){
        return false;
    } else {
        return true;
    }
}

?>