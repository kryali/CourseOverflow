-- DATABASE INFORMATION
-- dbname: courseov_api
-- dbuser: courseov_dbuser
-- dbuser password: courseoverflow

-- Drop old versions
DROP TABLE Votes;
DROP TABLE Users;
DROP TABLE Subscriptions;

-- Schema
CREATE TABLE Users(
    email varchar(100) not null,
    reputation integer not null default 0,
    primary key (email)
) ENGINE=INNODB;

CREATE TABLE Votes(
    email      varchar(100) not null,
    message_id varchar(100) not null,
    positive   boolean not null default true,
    primary key (email, message_id),
    foreign key (email) references Users(email)
) ENGINE=INNODB;

CREATE TABLE Subscriptions(
    email   varchar(100) not null,
    subname varchar(100) not null,
    primary key(email, subname),
    foreign key (email) references Users(email)
) ENGINE=INNODB;
