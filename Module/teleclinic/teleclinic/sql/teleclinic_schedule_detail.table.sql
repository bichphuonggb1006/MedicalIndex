drop table if exists teleclinic_schedule_detail;
CREATE TABLE teleclinic_schedule_detail(
       id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
       patientID VARCHAR(50),
       scheduleID  BIGINT,
       treatmentData LONGTEXT,
       createdTime DATETIME
);