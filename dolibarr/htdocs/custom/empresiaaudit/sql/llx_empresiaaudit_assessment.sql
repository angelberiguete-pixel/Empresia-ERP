-- Empresia Audit. Dolibarr installs this table on module activation.
CREATE TABLE llx_empresiaaudit_assessment (
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  entity integer NOT NULL DEFAULT 1,
  name varchar(190) NOT NULL,
  source varchar(64) NOT NULL DEFAULT 'direct',
  utm_campaign varchar(120) DEFAULT NULL,
  strategy tinyint NOT NULL,
  sales tinyint NOT NULL,
  processes tinyint NOT NULL,
  technology tinyint NOT NULL,
  people tinyint NOT NULL,
  score smallint NOT NULL,
  fk_user_create integer NOT NULL,
  date_creation datetime NOT NULL,
  INDEX idx_empresiaaudit_entity_date (entity, date_creation),
  INDEX idx_empresiaaudit_entity_source (entity, source)
) ENGINE=innodb;
