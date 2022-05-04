drop table if exists  teleclinic_vclinic_user;
create table teleclinic_vclinic_user(
    clinicID bigint unsigned,
    userID varchar(50),
    primary key(clinicID, userID)
);

create index idx_user on teleclinic_vclinic_user(userID);