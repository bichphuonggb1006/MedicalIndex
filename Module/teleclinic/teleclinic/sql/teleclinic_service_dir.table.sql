CREATE TABLE teleclinic_service_dir
(
    id       BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `name`   VARCHAR(255),
    `level`  TINYINT,
    parentID BIGINT,
    siteID   VARCHAR(50),
    sort     int,
    path     text,
    deleted TINYINT(4) DEFAULT 0,
    deletedAt datetime
);

CREATE INDEX idx_parentID ON teleclinic_service_dir (parentID, siteID);
CREATE INDEX idx_site ON teleclinic_service_dir (siteID);