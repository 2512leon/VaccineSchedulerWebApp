drop database if exists covid;
create database if not exists covid;
use covid;
source C:/php/COVID/Group4_Project/COVID_create.sql;
LOAD DATA INFILE 'C:/php/COVID/Group4_Project/Data/CUSTOMER.txt' INTO TABLE customer;
LOAD DATA INFILE 'C:/php/COVID/Group4_Project/Data/BATCH.txt' INTO TABLE batch;
LOAD DATA INFILE 'C:/php/COVID/Group4_Project/Data/DOSE.txt' INTO TABLE dose;
LOAD DATA INFILE 'C:/php/COVID/Group4_Project/Data/VACCINATION.txt' INTO TABLE vaccination;
LOAD DATA INFILE 'C:/php/COVID/Group4_Project/Data/WAITLIST.txt' INTO TABLE waitlist LINES TERMINATED BY '\r\n';