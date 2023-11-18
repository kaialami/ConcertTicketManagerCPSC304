<!-- https://stackoverflow.com/questions/871858/php-pass-variable-to-next-page -->
<!-- https://stackoverflow.com/questions/6833914/how-to-prevent-the-confirm-form-resubmission-dialog -->

<html>
    <head>
        <title>Concert Goer</title>

        <style>
            body {
                font-family: "Lato", sans-serif;
            }
        </style>
    </head>
    <body>
        <h1>Hello, &ltuserID&gt!</h1>
        
        <h2>Purchased Tickets</h2>
        <form method="GET" action="concertgoer.php">
            <input type="hidden" name="viewTicketsRequest">
            <input type="submit" value="View Tickets" name="viewTickets">
        </form>
        <br/>
        <p>(php will query and display results in table that appears here)</p>
        
        <hr/>
        
        <h2>Search for shows by</h2>
        <form method="GET" action="concertgoer.php">
            Performers and bands:  <input type="text" name="searchShowsBandRequest"> 
            <input type="submit" value="Search" name="searchShowsBand">
        </form>
        <p>OR</p>
        <form method="GET" action="concertgoer.php">
            Venue address:  <input type="text" name="searchShowsVenueRequest">
            <input type="submit", value="Search" name="searchShowsVenue">
        </form>
        <p>OR</p>
        <form method="GET" action="concertgoer.php">
            Event name:  <input type="text" name="searchShowsEventRequest">
            <input type="submit", value="Search" name="searchEventVenue">
        </form>
        <br/>
        <p>(php will query and display results in table that appears here)</p>
    </body>
</html>