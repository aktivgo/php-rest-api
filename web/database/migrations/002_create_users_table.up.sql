use test;

create table if not exists users
(
    id        int(11)      NOT NULL AUTO_INCREMENT,
    firstName varchar(255) NULL,
    lastName  varchar(255) NULL,
    PRIMARY KEY (id)
) ENGINE = InnoDB;