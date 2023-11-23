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

    ?>

    <div class="navbar">
        <a href="landingpage.php">Home</a>
        <a href="#add-member">Add Members</a>
        <a href="#update-member">Update Members</a>
        <a href="#book-show">Book a Show</a>
    </div>

    <div class="main">
        <div class="page-title">
            <h1>Hello, <?php echo $bandname ?>!</h1>
        </div>
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
        </div>
        <br>
        <?php?>
        <br>
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
                <label><input type="radio" name="active" value="true" checked=true>Active</label>
                <label><input type="radio" name="active" value="false">Inactive</label>
                <input type="hidden" name="updateMemberRequest"><br>
                <button type="submit">Update</button>
            </form>
        </div>
        <br>
        <?php?>
        <br>
        <hr>
        <div class="section">
            <a class="anchor" id="book-show"></a>
            <h2>Book a Show</h2>
        </div>
    </div>

    <script src="scripts/bandmember.js"></script>
</body>
</html>