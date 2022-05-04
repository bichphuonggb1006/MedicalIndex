drop table if exists teleclinic_patient;
CREATE TABLE teleclinic_patient(
   id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
   patientID VARCHAR(50),
   patientName VARCHAR(255),
   patientPassword VARCHAR(32),
   patientAttr LONGTEXT,
   actived INT,
   ddata LONGTEXT
);

