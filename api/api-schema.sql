-- DATABASE INFORMATION
-- dbname: courseov_api
-- dbuser: courseov_dbuser
-- dbuser password: courseoverflow

-- Drop old versions
--DROP TABLE Votes;
--DROP TABLE Users;

-- Schema
CREATE TABLE Votes(
    email      varchar(100) not null,
    message_id varchar(100) not null,
    positive   boolean not null default true,
    primary key (email, message_id)
);

CREATE TABLE Users(
    email varchar(100) not null,
    reputation integer not null default 0,
    primary key (email)
);
