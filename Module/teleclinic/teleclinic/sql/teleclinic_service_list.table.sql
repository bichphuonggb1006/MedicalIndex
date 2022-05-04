create table teleclinic_service_list
(
    id        bigint unsigned primary key auto_increment,
    `name`    varchar(255),
    `code`    varchar(50),
    dirID     bigint unsigned,
    sort      int,
    siteID    varchar(50),
    price     double,
    img       longtext,
    deleted   tinyint(4) default 0,
    deletedAt datetime
);

create index idx_dirID on teleclinic_service_list (dirID);
create index idx_siteID on teleclinic_service_list (siteID);