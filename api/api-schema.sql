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
    netid varchar(100) not null,
    reputation integer not null default 0,
    primary key (netid)
) ENGINE=INNODB;

CREATE TABLE Votes(
    netid      varchar(100) not null,
    message_id varchar(100) not null,
    positive   boolean not null default true,
    primary key (netid, message_id),
    foreign key (netid) references Users(netid)
) ENGINE=INNODB;

CREATE TABLE Subscriptions(
    netid   varchar(100) not null,
    subname varchar(100) not null,
    primary key(netid, subname),
    foreign key (netid) references Users(netid)
) ENGINE=INNODB;
