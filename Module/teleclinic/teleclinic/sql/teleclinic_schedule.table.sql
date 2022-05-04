drop table if exists teleclinic_schedule;
create table teleclinic_schedule(
    id bigint unsigned primary key auto_increment,
    patientID varchar(50),
    patientName varchar(255),
    patientAttr longtext,
    reqServiceID bigint unsigned,
    reqServiceAttr text,
    reqDate date,
    reqTimes varchar(50),
    reqNote longtext,
    paymentTransaction varchar(255),
    paymentStatus varchar(50),
    status varchar(50),
    scheduledDate datetime,
    scheduledDateIndex datetime,
    vclinicID bigint unsigned,
    vclinicAttr text,
    scheduledNumber int,
    comment text,
    siteID varchar(50),
    doctorID varchar(50),
    doctorAttr text,
    diagDate datetime,
    diagDesc longtext,
    diagConclusion text,
    diagRecommendation text,
    diagPrescription longtext,
    reExamDate text,
    uid varchar(100),
    patientPassword varchar(32),
    phone varchar(15),
    nextSchedule bigint unsigned,
    prevSchedule bigint unsigned
);

create index idx_uid on teleclinic_schedule(uid);
create index idx_site on teleclinic_schedule(siteID);
create index idx_clinic on teleclinic_schedule(vclinicID, reqDate);
create index idx_patient on teleclinic_schedule( patientID, siteID);
create index idx_schedule_date on teleclinic_schedule(scheduledDate, vclinicID, siteID);
create index idx_schedule_dateIndex on teleclinic_schedule(  scheduledDateIndex, vclinicID,`status`);
create index idx_nextSchedule on teleclinic_schedule (nextSchedule);
create index idx_prevSchedule on teleclinic_schedule (prevSchedule);

alter table teleclinic_schedule add column paymentFK varchar (100);

ALTER TABLE teleclinic_schedule ADD COLUMN  treatmentType VARCHAR (50);
ALTER TABLE teleclinic_schedule ADD COLUMN  startTreatmentDdata  longtext;
ALTER TABLE teleclinic_schedule ADD COLUMN  endTreatmentDdata  longtext;