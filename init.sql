drop table worksfor;
drop table books;
drop table playedin;
drop table ticketID;
drop table tickettype;
drop table ticketprice;
drop table musician;
drop table manager;
drop table technician;
drop table song;
drop table show;
drop table band;
drop table recordlabel;
drop table venue;
drop table eventtable;
drop table concertgoer;
drop table bandmember;


CREATE TABLE RecordLabel (
	recordLabelName		VARCHAR(30)	PRIMARY KEY,
	yearFounded			INTEGER
);

CREATE TABLE Band (
	bandName		VARCHAR(30)	PRIMARY KEY,
	dateStarted		DATE,
	recordLabelName	VARCHAR(30),
    pass			VARCHAR(255) 	NOT NULL,
    FOREIGN KEY (recordLabelName) 
		REFERENCES RecordLabel(recordLabelName)
	    ON DELETE SET NULL
);

CREATE TABLE Song (
	songName		VARCHAR(30),
	bandName		VARCHAR(30),
	songLength		VARCHAR(30)	NOT NULL,
	genre			VARCHAR(30),
	producer 		VARCHAR(30),
	songDate 		DATE,
	PRIMARY KEY (songName, bandName),
	FOREIGN KEY (bandName) REFERENCES Band(bandName)
		ON DELETE CASCADE
);

CREATE TABLE Venue (
	venueAddress	VARCHAR(50)	PRIMARY KEY,
	venueName		VARCHAR(30) 	NOT NULL,
	capacity		INTEGER			NOT NULL,
	ageReq			INTEGER
);

CREATE TABLE EventTable (
	eventName		VARCHAR(30),
	eventDate		DATE,
	city 			VARCHAR(30) 	NOT NULL,
	sponsor 		VARCHAR(30),
	PRIMARY KEY (eventName, eventDate)
);

CREATE TABLE Show (
	venueAddress	VARCHAR(50),
	showDateTime	TIMESTAMP,
	showName		VARCHAR(30),
	bandName		VARCHAR(30)	NOT NULL,
	eventName		VARCHAR(30),
	eventDate		DATE,
	PRIMARY KEY (venueAddress, showDateTime),
	FOREIGN KEY (venueAddress) REFERENCES Venue(venueAddress)
		ON DELETE CASCADE,
	FOREIGN KEY (bandName) REFERENCES Band(bandName)
		ON DELETE CASCADE,
	FOREIGN KEY (eventName, eventDate) REFERENCES EventTable(eventName, eventDate)
		ON DELETE SET NULL
);

CREATE TABLE BandMember (
	memberName			VARCHAR(30),
	memberDOB			DATE,
	startDate			DATE,
	PRIMARY KEY (memberName, memberDOB)
);

CREATE TABLE Musician (
	memberName			VARCHAR(30),
	memberDOB			DATE,
	instrument 			VARCHAR(30)	NOT NULL,
	PRIMARY KEY (memberName, memberDOB),
	FOREIGN KEY (memberName, memberDOB) REFERENCES BandMember(memberName, memberDOB)
		ON DELETE CASCADE
);

CREATE TABLE Manager (
	memberName			VARCHAR(30),
	memberDOB			DATE,
	PRIMARY KEY (memberName, memberDOB),
	FOREIGN KEY (memberName, memberDOB) REFERENCES BandMember(memberName, memberDOB)
		ON DELETE CASCADE
);

CREATE TABLE Technician (
	memberName			VARCHAR(30),
	memberDOB			DATE,
	specialty			VARCHAR(30) 	NOT NULL,
	PRIMARY KEY (memberName, memberDOB),
	FOREIGN KEY (memberName, memberDOB) REFERENCES BandMember(memberName, memberDOB)
		ON DELETE CASCADE
);


CREATE TABLE ConcertGoer (
	userID			VARCHAR(30)	PRIMARY KEY,
	pass			VARCHAR(255) 	NOT NULL,
	goerName		VARCHAR(30)		NOT NULL,
	email 			VARCHAR(30) 	UNIQUE	NOT NULL,
	dob				DATE
);

CREATE TABLE TicketID (
	ticketID		INTEGER			PRIMARY KEY,
	seatNum			CHAR(5)			NOT NULL,
	userID			VARCHAR(30),
	venueAddress	VARCHAR(50)		NOT NULL,
	showDateTime		TIMESTAMP		NOT NULL,
	FOREIGN KEY (userID) REFERENCES ConcertGoer(userID)
		ON DELETE SET NULL,
	FOREIGN KEY (venueAddress, showDateTime) REFERENCES Show(venueAddress, showDateTime)
		ON DELETE CASCADE,
	UNIQUE (seatNum, venueAddress, showDateTime)
);

CREATE TABLE TicketType (
	seatNum 		CHAR(5)	PRIMARY KEY,
	type			VARCHAR(30) 	NOT NULL
);

CREATE TABLE TicketPrice (
	seatNum			CHAR(5),
	venueAddress	VARCHAR(50),
	showDateTime	TIMESTAMP,
	price			DECIMAL(18, 2)		NOT NULL,
	PRIMARY KEY (seatNum, venueAddress, showDateTime),
	FOREIGN KEY (venueAddress, showDateTime) REFERENCES Show(venueAddress, showDateTime)
		ON DELETE CASCADE
);

CREATE TABLE WorksFor (
	memberName		VARCHAR(30),
	memberDOB		DATE,
	bandName		VARCHAR(30),
	active			CHAR(1)	NOT NULL,
	PRIMARY KEY (memberName, memberDOB, bandName),
	FOREIGN KEY (memberName, memberDOB) REFERENCES BandMember(memberName, memberDOB)
		ON DELETE CASCADE,
	FOREIGN KEY (bandName) REFERENCES Band(bandName)
		ON DELETE CASCADE
);

CREATE TABLE Books (
	memberName			VARCHAR(30),
	memberDOB			DATE,
	venueAddress		VARCHAR(50),
	bookingDateTime		TIMESTAMP		NOT NULL,
	UNIQUE(venueAddress, bookingDateTime),
	PRIMARY KEY (memberName, memberDOB, venueAddress, bookingDateTime),
	FOREIGN KEY (memberName, memberDOB) REFERENCES Manager(memberName, memberDOB)
		ON DELETE CASCADE,
	FOREIGN KEY (venueAddress) REFERENCES Venue(venueAddress)
		ON DELETE CASCADE
);

CREATE TABLE PlayedIn (
	songName		VARCHAR(30),
	bandName		VARCHAR(30),
	venueAddress	VARCHAR(50),
	showDateTime		TIMESTAMP,
	PRIMARY KEY (songName, bandName, venueAddress, showDateTime),
	FOREIGN KEY (songName, bandName) REFERENCES Song(songName, bandName)
		ON DELETE CASCADE,
	FOREIGN KEY (venueAddress, showDateTime) REFERENCES Show(venueAddress, showDateTime)
		ON DELETE CASCADE
);




INSERT INTO RecordLabel
VALUES 	('RCA Records', 1900);
INSERT INTO RecordLabel
VALUES 	('Universal Music', 1940);
INSERT INTO RecordLabel
VALUES 	('Warner Records', 1950);
INSERT INTO RecordLabel
VALUES 	('Sony Music Entertainment', 1920);
INSERT INTO RecordLabel
VALUES 	('Atlantic Records', 2011);

INSERT INTO Band
VALUES ('Arctic Monkeys', DATE '2006-02-10', 'RCA Records', '$2y$10$ygrphXkl85lC6b9q.jndYup.K/FqM/5IwVyTvVdmH1tkdBMDd/RUO');
INSERT INTO Band
VALUES ('Radiohead', DATE '1996-02-11', 'RCA Records', '$2y$10$ipSmT78TYgLCA9r8rBlR4uUaiq4tqeNazb5v/5XLFDlxQMTgeeSRG');
INSERT INTO Band
VALUES ('Joji', DATE '2005-07-02', 'Universal Music', '$2y$10$ATUUg7kneIkIPgHd9mhO5eakyrdUpnTQ9xkAVcg2B5W4a2ve9bDzq');
INSERT INTO Band
VALUES ('Kiss', DATE '1975-01-01', 'Warner Records', '$2y$10$.lyiiKlo1NuX66ltS0/0LurSQ6H4B2RaJwZNWcrxauNk0lJYI2iF.');
INSERT INTO Band
VALUES ('Super Cool Indie Band', DATE '1920-04-05', NULL, '$2y$10$UEivhvm/su9coQSCWVt0pOH5h3gfOZJtSPZrr0APSTJG4.9E3jpD2');

INSERT INTO Song
VALUES ('Do I Wanna Know?', 'Arctic Monkeys', '4:34', 'Indie Rock', 'James Ford', DATE  '2013-06-19');
INSERT INTO Song
VALUES ('505', 'Arctic Monkeys', '4:13', 'Indie Rock', NULL, DATE '2007-04-23');
INSERT INTO Song
VALUES ('Glimpse of Us', 'Joji', '3:53', 'Lo-fi', 'Connor McDonough', DATE '2022-06-10');
INSERT INTO Song
VALUES ('Weird Fishes/Arpeggi', 'Radiohead', '5:19', 'Alternative Rock', 'Nigel Godrich', DATE '2007-10-10');
INSERT INTO Song
VALUES ('Super Epic Song', 'Super Cool Indie Band', '7:59', 'Jazz', 'Steve Steves', DATE '1977-01-10');

INSERT INTO BandMember
VALUES ('Alex Turner', DATE '1991-02-03', DATE '2010-03-03');
INSERT INTO BandMember
VALUES ('Matt Helders', DATE '1971-04-03', DATE '1993-01-03');
INSERT INTO BandMember
VALUES ('Jamie Cook', DATE '1952-03-03', DATE '1981-02-03');
INSERT INTO BandMember
VALUES ('Nick O''Malley', DATE '1933-02-03', DATE '1961-03-03');
INSERT INTO BandMember
VALUES ('George Kusunoki', DATE '1994-01-03', DATE '2021-02-03');
INSERT INTO BandMember
VALUES ('Thom Yorke', DATE '1955-03-03', DATE '1980-10-03');
INSERT INTO BandMember
VALUES ('Jonny Greenwood', DATE '1937-02-03', DATE '1971-05-03');
INSERT INTO BandMember
VALUES ('Colin Greenwood', DATE '1991-01-03', NULL);
INSERT INTO BandMember
VALUES ('Ed O''Brien', DATE '1990-02-03', DATE '2023-04-03');
INSERT INTO BandMember
VALUES ('Philip Selway', DATE '1970-04-03', DATE '2022-02-03');
INSERT INTO BandMember
VALUES ('John Manager', DATE '1950-03-03', DATE '1981-02-03');
INSERT INTO BandMember
VALUES ('Tom Manager', DATE '1930-02-03', DATE '1991-02-03');
INSERT INTO BandMember
VALUES ('Jim Manager', DATE '1999-04-30', NULL);
INSERT INTO BandMember
VALUES ('Tim Manager', DATE '1888-12-19', NULL);
INSERT INTO BandMember
VALUES ('Jeff Manager', DATE '2003-02-01', NULL);
INSERT INTO BandMember
VALUES ('Super Man', DATE '1990-01-03', NULL);
INSERT INTO BandMember
VALUES ('John Technician', DATE '1971-03-02', DATE '1991-02-03');
INSERT INTO BandMember
VALUES ('John Technician', DATE '1972-04-03', NULL);
INSERT INTO BandMember
VALUES ('Jim Technician', DATE '1998-11-11', NULL);
INSERT INTO BandMember
VALUES ('Tom Technician', DATE '1997-12-31', NULL);
INSERT INTO BandMember
VALUES ('Tim Technician', DATE '2001-09-11', DATE '2018-02-02');

INSERT INTO Musician
VALUES ('Alex Turner', DATE '1991-02-03', 'Guitar');
INSERT INTO Musician
VALUES ('Jonny Greenwood', DATE '1937-02-03', 'Keyboard');
INSERT INTO Musician
VALUES ('Nick O''Malley', DATE '1933-02-03', 'Bass Guitar');
INSERT INTO Musician
VALUES ('George Kusunoki', DATE '1994-01-03', 'Vocals');
INSERT INTO Musician
VALUES ('Matt Helders', DATE '1971-04-03', 'Drums');
INSERT INTO Musician
VALUES ('Super Man', DATE '1990-01-03', 'Alto Saxophone');

INSERT INTO Manager
VALUES ('John Manager', DATE '1950-03-03');
INSERT INTO Manager
VALUES ('Tom Manager', DATE '1930-02-03');
INSERT INTO Manager
VALUES ('Jim Manager', DATE '1999-04-30');
INSERT INTO Manager
VALUES ('Tim Manager', DATE '1888-12-19');
INSERT INTO Manager
VALUES ('Jeff Manager', DATE '2003-02-01');

INSERT INTO Technician
VALUES ('John Technician', DATE '1971-03-02', 'Lighting');
INSERT INTO Technician
VALUES ('John Technician', DATE '1972-04-03', 'Audio');
INSERT INTO Technician
VALUES ('Jim Technician', DATE '1998-11-11', 'Audio');
INSERT INTO Technician
VALUES ('Tom Technician', DATE '1997-12-31', 'Camera');
INSERT INTO Technician
VALUES ('Tim Technician', DATE '2001-09-11', 'Audio');

INSERT INTO EventTable
VALUES ('Coachella', DATE '2011-12-30', 'Indio', 'Coca-Cola');
INSERT INTO EventTable
VALUES ('Calgary Stampede', DATE '2024-07-5', 'Calgary', 'Honda');
INSERT INTO EventTable
VALUES ('Lollapalooza', DATE '2010-10-01', 'Chicago', NULL);
INSERT INTO EventTable
VALUES ('Winnipeg Folk Festival', DATE '2015-12-04', 'Winnipeg', 'Red Bull');
INSERT INTO EventTable
VALUES ('Big Dog Festival', DATE '2043-10-03', 'Gotham City', 'AWS');

INSERT INTO Venue
VALUES ('231 Oak Rd, Vancouver, BC, Canada', 'Smooth Grooves', 600, 21);
INSERT INTO Venue
VALUES ('5054 Rat Ave, New York City, NY, USA', 'Rat Jam', 100, 1);
INSERT INTO Venue
VALUES ('12 Slop St, Calgary, AB, Canada', 'The Big Cheese', 200,  NULL);
INSERT INTO Venue
VALUES ('44 44th St, Toronto, ON, Canada', 'Black Hole Guys', 60, 18);
INSERT INTO Venue
VALUES ('101 Real Rd, Chicago, IL, USA', 'Real Ones', 40, 21);

INSERT INTO ConcertGoer
VALUES ('kahnsert123', '$2y$10$wIp.TV.C7OWC5c9iu6XgbeUGbkFIg2.hvzUx46KztP9gCrcTzyrrW', 'Kahn Sert', 'iloveconcerts@gmail.com', DATE '1990-01-03');
INSERT INTO ConcertGoer
VALUES ('ILOVEMUSIC', '$2y$10$AeZbAaCWbMOd5KUkttYhBuhWozMphK7UnD6zlg27wQdiUegjJhqTa', 'Micheal Joaquin', 'mikejoaq@gmail.com', NULL);
INSERT INTO ConcertGoer
VALUES ('mjisalive', '$2y$10$8pHCEwXLvrOuEbRU9l6oyuP1zrCVzh8ohg4dzYIb9FGPHj..UhyPC', 'Micheal Jackson', '123mjalive@gmail.com', DATE '1995-04-30');
INSERT INTO ConcertGoer
VALUES ('kfln354', '$2y$10$ewmx1RkUutWfDX5nWsSgCe5uwO2eJxF.Od/VWDOz9POl0myB58QD6', 'Kai Fakelastname', 'kaikaifakelastname@gmail.com', NULL);
INSERT INTO ConcertGoer
VALUES ('namefive', '$2y$10$Dhx9eALVaBZq4oUtPxI8SeMW9dJMA.IX0K30sf54Qyrs5Es934/6q', 'Name Five', 'name5@gmail.com', DATE '2000-10-03');

INSERT INTO Show
VALUES ('231 Oak Rd, Vancouver, BC, Canada', TIMESTAMP '2011-12-30 18:00:00', NULL, 'Arctic Monkeys', 'Coachella', DATE '2011-12-30');
INSERT INTO Show
VALUES ('5054 Rat Ave, New York City, NY, USA', TIMESTAMP '2011-12-30 18:30:00', 'The Rat Show', 'Super Cool Indie Band', NULL, NULL);
INSERT INTO Show
VALUES ('231 Oak Rd, Vancouver, BC, Canada', TIMESTAMP '2015-02-13 20:00:00', NULL, 'Radiohead', NULL, NULL);
INSERT INTO Show
VALUES ('44 44th St, Toronto, ON, Canada', TIMESTAMP '2018-03-31 19:30:00', NULL, 'Joji', NULL, NULL);
INSERT INTO Show
VALUES ('101 Real Rd, Chicago, IL, USA', TIMESTAMP '2023-12-30 00:00:00', 'Midnight Music', 'Arctic Monkeys', NULL, NULL);



INSERT INTO TicketID
VALUES (1, 'F1399', 'kahnsert123', '231 Oak Rd, Vancouver, BC, Canada', TIMESTAMP '2011-12-30 18:00:00');
INSERT INTO TicketID
VALUES (2, 'B1298', 'kahnsert123', '5054 Rat Ave, New York City, NY, USA', TIMESTAMP '2011-12-30 18:30:00');
INSERT INTO TicketID
VALUES (3, 'U3044', 'ILOVEMUSIC', '231 Oak Rd, Vancouver, BC, Canada', TIMESTAMP '2015-02-13 20:00:00');
INSERT INTO TicketID
VALUES (4, 'L3401', 'mjisalive', '44 44th St, Toronto, ON, Canada', TIMESTAMP '2018-03-31 19:30:00');
INSERT INTO TicketID
VALUES (5, 'L1328', 'namefive', '101 Real Rd, Chicago, IL, USA', TIMESTAMP '2023-12-30 00:00:00');
INSERT INTO TicketID
VALUES (6, 'F1400', NULL, '231 Oak Rd, Vancouver, BC, Canada', TIMESTAMP '2011-12-30 18:00:00');
INSERT INTO TicketID
VALUES (7, 'F1401', NULL, '231 Oak Rd, Vancouver, BC, Canada', TIMESTAMP '2011-12-30 18:00:00');
INSERT INTO TicketID
VALUES (8, 'F1402', 'ILOVEMUSIC', '231 Oak Rd, Vancouver, BC, Canada', TIMESTAMP '2011-12-30 18:00:00');
INSERT INTO TicketID
VALUES (9, 'B2033', NULL, '231 Oak Rd, Vancouver, BC, Canada', TIMESTAMP '2011-12-30 18:00:00');
INSERT INTO TicketID
VALUES (10, 'F1404', NULL, '231 Oak Rd, Vancouver, BC, Canada', TIMESTAMP '2011-12-30 18:00:00');

INSERT INTO TicketType 
VALUES ('F1399', 'Floor');
INSERT INTO TicketType 
VALUES ('B1298', 'Balcony');
INSERT INTO TicketType 
VALUES ('U3044', 'Upper');
INSERT INTO TicketType 
VALUES ('L3401', 'Lower');
INSERT INTO TicketType 
VALUES ('L1328', 'Lower');
INSERT INTO TicketType
VALUES ('F1400', 'Floor');
INSERT INTO TicketType
VALUES ('F1401', 'Floor');
INSERT INTO TicketType
VALUES ('F1402', 'Floor');
INSERT INTO TicketType
VALUES ('F1404', 'Floor');
INSERT INTO TicketType
VALUES ('B2033', 'Balcony');

INSERT INTO TicketPrice
VALUES ('F1399', '231 Oak Rd, Vancouver, BC, Canada', TIMESTAMP '2011-12-30 18:00:00', 100.00);
INSERT INTO TicketPrice
VALUES ('B1298', '5054 Rat Ave, New York City, NY, USA', TIMESTAMP '2011-12-30 18:30:00', 50.99);
INSERT INTO TicketPrice
VALUES ('U3044', '231 Oak Rd, Vancouver, BC, Canada', TIMESTAMP '2015-02-13 20:00:00', 1200.53);
INSERT INTO TicketPrice
VALUES ('L3401', '44 44th St, Toronto, ON, Canada', TIMESTAMP '2018-03-31 19:30:00', 7.98);
INSERT INTO TicketPrice
VALUES ('L1328', '101 Real Rd, Chicago, IL, USA', TIMESTAMP '2023-12-30 00:00:00', 154.22);
INSERT INTO TicketPrice
VALUES ('F1400', '231 Oak Rd, Vancouver, BC, Canada', TIMESTAMP '2011-12-30 18:00:00', 100.00);
INSERT INTO TicketPrice
VALUES ('F1401', '231 Oak Rd, Vancouver, BC, Canada', TIMESTAMP '2011-12-30 18:00:00', 100.00);
INSERT INTO TicketPrice
VALUES ('F1402', '231 Oak Rd, Vancouver, BC, Canada', TIMESTAMP '2011-12-30 18:00:00', 100.00);
INSERT INTO TicketPrice
VALUES ('F1404', '231 Oak Rd, Vancouver, BC, Canada', TIMESTAMP '2011-12-30 18:00:00', 100.00);
INSERT INTO TicketPrice
VALUES ('B2033', '231 Oak Rd, Vancouver, BC, Canada', TIMESTAMP '2011-12-30 18:00:00', 75.00);

INSERT INTO WorksFor 
VALUES ('Alex Turner', DATE '1991-02-03', 'Arctic Monkeys', 'y'); 
INSERT INTO WorksFor 
VALUES ('Matt Helders', DATE '1971-04-03', 'Arctic Monkeys', 'y');
INSERT INTO WorksFor 
VALUES ('Jamie Cook', DATE '1952-03-03', 'Arctic Monkeys', 'y');
INSERT INTO WorksFor 
VALUES ('Nick O''Malley', DATE '1933-02-03', 'Arctic Monkeys', 'y');
INSERT INTO WorksFor 
VALUES ('George Kusunoki', DATE '1994-01-03', 'Joji', 'y');
INSERT INTO WorksFor 
VALUES ('Thom Yorke', DATE '1955-03-03', 'Radiohead', 'y');
INSERT INTO WorksFor 
VALUES ('Jonny Greenwood', DATE '1937-02-03', 'Radiohead', 'y');
INSERT INTO WorksFor 
VALUES ('Colin Greenwood', DATE '1991-01-03', 'Radiohead', 'y');
INSERT INTO WorksFor 
VALUES ('Ed O''Brien', DATE '1990-02-03', 'Radiohead', 'y');
INSERT INTO WorksFor 
VALUES ('Philip Selway', DATE '1970-04-03', 'Radiohead', 'y');
INSERT INTO WorksFor 
VALUES ('John Manager', DATE '1950-03-03', 'Kiss', 'n');
INSERT INTO WorksFor 
VALUES ('John Manager', DATE '1950-03-03', 'Arctic Monkeys', 'y');
INSERT INTO WorksFor 
VALUES ('John Manager', DATE '1950-03-03', 'Joji', 'y');
INSERT INTO WorksFor 
VALUES ('Tom Manager', DATE '1930-02-03', 'Radiohead', 'y');
INSERT INTO WorksFor 
VALUES ('Jim Manager', DATE '1999-04-30', 'Super Cool Indie Band', 'y');
INSERT INTO WorksFor 
VALUES ('Tim Manager', DATE '1888-12-19', 'Super Cool Indie Band', 'y');
INSERT INTO WorksFor 
VALUES ('Jeff Manager', DATE '2003-02-01', 'Super Cool Indie Band', 'y');
INSERT INTO WorksFor 
VALUES ('Super Man', DATE '1990-01-03', 'Super Cool Indie Band', 'y');
INSERT INTO WorksFor 
VALUES ('John Technician', DATE '1971-03-02', 'Radiohead', 'y');
INSERT INTO WorksFor 
VALUES ('John Technician', DATE '1972-04-03', 'Radiohead', 'n');
INSERT INTO WorksFor 
VALUES ('Jim Technician', DATE '1998-11-11', 'Joji', 'n');
INSERT INTO WorksFor 
VALUES ('Tom Technician', DATE '1997-12-31', 'Joji', 'y');
INSERT INTO WorksFor 
VALUES ('Tim Technician', DATE '2001-09-11', 'Radiohead', 'y');

INSERT INTO Books 
VALUES ('John Manager', DATE '1950-03-03', '231 Oak Rd, Vancouver, BC, Canada', TIMESTAMP '2011-12-30 18:00:00');
INSERT INTO Books 
VALUES ('Jim Manager', DATE '1999-04-30', '5054 Rat Ave, New York City, NY, USA', TIMESTAMP '2011-12-30 18:30:00');
INSERT INTO Books 
VALUES ('Tom Manager', DATE '1930-02-03', '231 Oak Rd, Vancouver, BC, Canada', TIMESTAMP '2015-02-13 20:00:00');
INSERT INTO Books 
VALUES ('John Manager', DATE '1950-03-03', '44 44th St, Toronto, ON, Canada', TIMESTAMP '2018-03-31 19:30:00');
INSERT INTO Books 
VALUES ('John Manager', DATE '1950-03-03', '101 Real Rd, Chicago, IL, USA', TIMESTAMP '2023-12-30 00:00:00');

INSERT INTO PlayedIn 
VALUES ('Do I Wanna Know?', 'Arctic Monkeys', '231 Oak Rd, Vancouver, BC, Canada', TIMESTAMP '2011-12-30 18:00:00');
INSERT INTO PlayedIn 
VALUES ('Do I Wanna Know?', 'Arctic Monkeys', '101 Real Rd, Chicago, IL, USA', TIMESTAMP '2023-12-30 00:00:00');
INSERT INTO PlayedIn 
VALUES ('Weird Fishes/Arpeggi', 'Radiohead', '231 Oak Rd, Vancouver, BC, Canada', TIMESTAMP '2015-02-13 20:00:00');
INSERT INTO PlayedIn 
VALUES ('Super Epic Song', 'Super Cool Indie Band', '5054 Rat Ave, New York City, NY, USA', TIMESTAMP '2011-12-30 18:30:00');
INSERT INTO PlayedIn 
VALUES ('Glimpse of Us', 'Joji', '44 44th St, Toronto, ON, Canada', TIMESTAMP '2018-03-31 19:30:00');
INSERT INTO PlayedIn 
VALUES ('505', 'Arctic Monkeys', '231 Oak Rd, Vancouver, BC, Canada', TIMESTAMP '2011-12-30 18:00:00');
