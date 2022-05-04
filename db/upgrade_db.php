-- Thêm địa chỉ Tỉnh/Thành phố - Quận/Huyện - Phường/Xã cho bảng system_site
ALTER TABLE system_site ADD province VARCHAR(50);
ALTER TABLE system_site ADD district VARCHAR(50);
ALTER TABLE system_site ADD ward VARCHAR(50);
ALTER TABLE system_site ADD address VARCHAR(50);
ALTER TABLE system_site ADD location VARCHAR(255);

ALTER TABLE `teleclinic_otp` ADD COLUMN `referenceID` VARCHAR(255) NOT NULL AFTER `id`;
ALTER TABLE `teleclinic_service_list` ADD COLUMN `description` LONGTEXT NULL AFTER `name`;
ALTER TABLE `teleclinic_patient_account` ADD COLUMN `token_reset_password` VARCHAR(255) NULL AFTER `status`;
