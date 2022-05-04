CREATE TABLE system_countries
(
    id          VARCHAR(50) PRIMARY KEY,
    `continent` VARCHAR(255),
    `name_en`   VARCHAR(255),
    `name`      VARCHAR(255)
);

CREATE INDEX idx_code ON system_countries (`id`);