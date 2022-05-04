create table teleclinic_vclinic_service(
    clinicID bigint unsigned,
    serviceID bigint unsigned,
    primary key (serviceID, clinicID)
);

create index idx_clinic on teleclinic_vclinic_service(clinicID);