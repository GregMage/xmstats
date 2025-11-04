CREATE TABLE `xmstats_export` (
  `export_id`             smallint(5)  unsigned   NOT NULL AUTO_INCREMENT,
  `export_type`           varchar(5)        	    NOT NULL DEFAULT '',
  `export_fid`            smallint(5) unsigned    NOT NULL DEFAULT '0',
  `export_sid`            smallint(5) unsigned    NOT NULL DEFAULT '0',
  `export_status`         tinyint(1)  unsigned    NOT NULL DEFAULT '1',

  PRIMARY KEY (`export_id`),
  KEY `export_fid` (`export_fid`)
) ENGINE=MyISAM;