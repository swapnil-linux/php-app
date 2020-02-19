# php-app
Sample PHP Code which connected to mysqldb to get list of friends, use the below to create table and insert records
```
CREATE TABLE IF NOT EXISTS MyGuests (id INT AUTO_INCREMENT PRIMARY KEY, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL )  ENGINE=INNODB;insert into MyGuests (firstname,lastname) VALUES ("Chandler","Bing");
insert into MyGuests (firstname,lastname) VALUES ("Rachel","Green");
insert into MyGuests (firstname,lastname) VALUES ("Monica","Geller");
insert into MyGuests (firstname,lastname) VALUES ("Dr. Ross","Geller");
insert into MyGuests (firstname,lastname) VALUES ("Joey","Tribbiani Jr.");
insert into MyGuests (firstname,lastname) VALUES ("Phoebe","Buffay");
```
