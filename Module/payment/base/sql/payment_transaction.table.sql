drop table if exists payment_transaction;
CREATE TABLE `payment_transaction`
(
    `id`             BIGINT(20)   NOT NULL AUTO_INCREMENT,
    `orderType`      VARCHAR(100),
    `orderID`        VARCHAR(255),
    `status`         VARCHAR(50)  NOT NULL DEFAULT 'unpaid',
    `userID`         VARCHAR(50),
    `userName`       VARCHAR(255) NOT NULL,
    `userPhone`      VARCHAR(20),
    `userEmail`      VARCHAR(255),
    `userAddress`    TEXT         NULL,
    `amount`         FLOAT        NOT NULL,
    `payment`        VARCHAR(50)  NOT NULL,
    `paymentInfo`    TEXT,
    `paymentContent` VARCHAR(255) NOT NULL,
    `security`       VARCHAR(50),
    `siteID`         VARCHAR(50)  null,
    `createdTime`    DATETIME     NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = INNODB
  CHARSET = utf8
  COLLATE = utf8_unicode_ci;

create index idx_site on payment_transaction (siteID);