create table system_dvhc(
    id varchar(50) primary key,
    `name` varchar(255),
    level varchar(50),
    parentID varchar(50) default '0'
);

create index idx_parentID on system_dvhc(parentID);
create index idx_level on system_dvhc(level);