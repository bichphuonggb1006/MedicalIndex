drop table if exists teleclinic_vclinic;

create table teleclinic_vclinic(
    id bigint unsigned primary key auto_increment,
    `name` varchar(255),
    siteID varchar(50),
    depID varchar(50),
    patientPerHour int default 0,
    sort int,
    videoCall text,
    deletedAt datetime,
    schedule text
);

create index idx_site on teleclinic_vclinic(siteID);
