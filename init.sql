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
	venueAddress	VARCHAR(30)	PRIMARY KEY,
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
	venueAddress	VARCHAR(30),
	showDate		DATE,
	showTime		CHAR(5),
	showName		VARCHAR(30),
	bandName		VARCHAR(30)	NOT NULL,
	eventName		VARCHAR(30),
	eventDate		DATE,
	PRIMARY KEY (venueAddress, showDate, showTime),
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
	userID			INTEGER	PRIMARY KEY,
	goerName		VARCHAR(30)	NOT NULL,
	email 			VARCHAR(30) 	UNIQUE	NOT NULL,
	dob				DATE
);

CREATE TABLE TicketID (
	ticketID		INTEGER	PRIMARY KEY,
	seatNum			VARCHAR(30)	NOT NULL,
	userID			INTEGER,
	venueAddress	VARCHAR(30)	NOT NULL,
	showDate		DATE		NOT NULL,
	showTime		CHAR(5)	NOT NULL,
	FOREIGN KEY (userID) REFERENCES ConcertGoer(userID)
		ON DELETE SET NULL,
	UNIQUE (seatNum, venueAddress, showDate, showTime)
);

CREATE TABLE TicketType (
	seatNum 		CHAR(5)	PRIMARY KEY,
	type			VARCHAR(30) 	NOT NULL
);

CREATE TABLE TicketPrice (
	seatNum			CHAR(5),
	venueAddress	VARCHAR(30),
	showDate		DATE,
	showTime		CHAR(5),
	price			REAL		NOT NULL,
	PRIMARY KEY (seatNum, venueAddress, showDate, showTime),
	FOREIGN KEY (venueAddress, showDate, showTime) REFERENCES Show(venueAddress, showDate, showTime)
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
	memberName		VARCHAR(30),
	memberDOB		DATE,
	venueAddress	VARCHAR(30),
	bookingDate		DATE		NOT NULL,
	bookingTime		CHAR(5)		NOT NULL,
	UNIQUE(venueAddress, bookingDate, bookingTime),
	PRIMARY KEY (memberName, memberDOB, venueAddress),
	FOREIGN KEY (memberName, memberDOB) REFERENCES Manager(memberName, memberDOB)
		ON DELETE CASCADE,
	FOREIGN KEY (venueAddress) REFERENCES Venue(venueAddress)
		ON DELETE CASCADE
);

CREATE TABLE PlayedIn (
	songName		VARCHAR(30),
	bandName		VARCHAR(30),
	venueAddress	VARCHAR(30),
	showDate		DATE,
	showTime		CHAR(5),
	PRIMARY KEY (songName, bandName, venueAddress, showDate, showTime),
	FOREIGN KEY (songName, bandName) REFERENCES Song(songName, bandName)
		ON DELETE CASCADE,
	FOREIGN KEY (venueAddress, showDate, showTime) REFERENCES Show(venueAddress, showDate, showTime)
		ON DELETE CASCADE
);
