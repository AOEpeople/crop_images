#
# Table structure for table 'sys_file_reference'
#
CREATE TABLE sys_file_reference (

	tx_cropimages_aspectratio tinytext,
	tx_cropimages_cropvalues mediumtext,
	tx_cropimages_responsiveimages int(11) DEFAULT '0' NOT NULL,
	tx_cropimages_responsivetype tinyint(4) DEFAULT '0' NOT NULL,
);