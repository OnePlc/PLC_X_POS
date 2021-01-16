INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`, `needs_globaladmin`) VALUES ('touchscreen', 'OnePlace\\POS\\Controller\\BackendController', 'Touchscreen Index', '', '', '0', '0');
INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`, `needs_globaladmin`) VALUES ('view', 'OnePlace\\POS\\Controller\\BackendController', 'Bestellung Details', '', '', '0', '0');

INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`, `needs_globaladmin`) VALUES ('orderlist', 'OnePlace\\POS\\Controller\\ApiController', 'Neuste Bestellungen anzeigen', '', '', '0', '1');
INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`, `needs_globaladmin`) VALUES ('print', 'OnePlace\\POS\\Controller\\ApiController', 'Bestellung drucken', '', '', '0', '1');
INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`, `needs_globaladmin`) VALUES ('confirm', 'OnePlace\\POS\\Controller\\ApiController', 'Bestellung bestätigen', '', '', '0', '1');

INSERT INTO `settings` (`settings_key`, `settings_value`) VALUES ('pos-master-url', 'https://master-pos.1plc.ch');