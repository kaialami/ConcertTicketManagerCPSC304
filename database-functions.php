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

    // takes $time = "xx:xx" in 12-hour format and adds 12 hours to make it PM in 24-hour format
    function to24($time) {
        $split = explode(":", $time);
        $hourInt = intval($split[0]);
        $hourInt = $hourInt + 12;
        if ($hourInt == 24) {
            $hourInt = "00";
        }
        return $hourInt . ":" . $split[1];
    }

    // converts date DD-MON-YY to YYYY-MM-DD
    // e.g. toYYYYMMDD(30-DEC-11) --> 2011-12-30
    function toYYYYMMDD($date) {
        $split = explode("-", $date);
        $month = $split[1];
        $mm = "";

        switch($month) {
            case "JAN":
                $mm = "01";
                break;
            case "FEB":
                $mm = "02";
                break;
            case "MAR":
                $mm = "03";
                break;
            case "APR":
                $mm = "04";
                break;
            case "MAY":
                $mm = "05";
                break;
            case "JUN":
                $mm = "06";
                break;
            case "JUL":
                $mm = "07";
                break;
            case "AUG":
                $mm = "08";
                break;
            case "SEP":
                $mm = "09";
                break;
            case "OCT":
                $mm = "10";
                break;
            case "NOV":
                $mm = "11";
                break;
            case "DEC":
                $mm = "12";
                break;
        }

        return "20" . $split[2] . "-" . $mm . "-" . $split[0];
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

// $cb is either "c" or "b" for concertgoer or band, respectively
// if somehow user got to concergoer.php or band.php without a valid login, redirect to home page
function checkLogin($login, $cb) {
    $query;
    if ($cb == "c") {
        $query = "SELECT userID FROM ConcertGoer WHERE userID = '" . $login . "'";
    } else if ($cb == "b") {
        $query = "SELECT bandname FROM Band WHERE bandname = '" . $login ."'";
    } else {
        return;
    }

    if (connectToDB()) {
        $getLogin = executePlainSQL($query);
        $row = oci_fetch_row($getLogin);
        if (!$row) {
            disconnectFromDB();
            header("Location: landingpage.php");
        }
    } 

    disconnectFromDB();
}

?>