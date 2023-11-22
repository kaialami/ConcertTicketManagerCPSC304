<html>
<head>
    <title>Showtime!</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato">
    <style>
        
        
        body {
            text-align: center;
            vertical-align: middle;
            /* background-color: slategray; */
            font-family: "Lato", sans-serif;
        }
        
        img {
            width: 600px;
            padding: 30px;
        }

        p {
            text-align: left;
        }

        .page-title {
            margin: auto;
            display: table;
            padding: 20px;
        }

        .image-container {
            margin: auto;
            display: table;
        }

        @media (max-width: 1300px) {
            img {
                width: 400px;
                padding: 5px 20px;
            }
        }

    </style>
</head>
<body>
    <div class="page-title">
        <h1>Welcome to Showtime!</h1>
        <h3>Choose your mode</h3>
    </div>
    <br><br>
    <div class="image-container">
        <a href="band-login.php">
            <img src="images/Band.png"/>
        </a>
        <a href="concertgoer-login.php">
            <img src="images/concertgoer.png"/>
        </a>
    </div>
</body>
</html>