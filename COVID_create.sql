Create table if not exists CUSTOMER (
    Fname varchar(15) not null,
	Minit varchar(1),
	Lname varchar(15) not null,
    Age int not null,
    PhoneNumber char(10) primary key)engine = innodb;

Create table if not exists BATCH (
    BatchNum varchar(15) not null,
    Manufacturer varchar(15) not null,
    DoseQty int not null,
    ExpirationDate date,
    primary key(BatchNum)) engine = innodb;

Create table if not exists DOSE (
    BatchNr varchar(15) not null,
    DoseTrackingNum varchar(15) not null,
    Availability char(1) not null,
    primary key(DoseTrackingNum)) engine = innodb;

Create table if not exists VACCINATION (
    CPhone char(10) not null,
    DoseTrackingNum varchar(15) not null,
    CurrentDose int,
    DateReceived date) engine = innodb;
    
Create table if not exists WAITLIST (
    CPhoneNr char(10) not null,
    Manufacturer varchar(15) not null) engine = innodb;