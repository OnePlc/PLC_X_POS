INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`, `needs_globaladmin`) VALUES ('touchscreen', 'OnePlace\\POS\\Controller\\BackendController', 'Touchscreen Index', '', '', '0', '0');
INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`, `needs_globaladmin`) VALUES ('view', 'OnePlace\\POS\\Controller\\BackendController', 'Bestellung Details', '', '', '0', '0');


INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`, `needs_globaladmin`) VALUES ('index', 'OnePlace\\POS\\Controller\\WorktimeController', 'Arbeitszeit Übersicht', '', '', '0', '0');
INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`, `needs_globaladmin`) VALUES ('saldo', 'OnePlace\\POS\\Controller\\WorktimeController', 'Mitarbeiter Monatssaldo', '', '', '0', '0');
INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`, `needs_globaladmin`) VALUES ('current', 'OnePlace\\POS\\Controller\\WorktimeController', 'Neueste Arbeitszeiten', '', '', '0', '0');
INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`, `needs_globaladmin`) VALUES ('month', 'OnePlace\\POS\\Controller\\WorktimeController', 'Arbeitszeiten auflisten', '', '', '0', '0');

INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`, `needs_globaladmin`) VALUES ('index', 'OnePlace\\POS\\Controller\\CheckoutController', 'Kasse sehen', '', '', '0', '0');
INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`, `needs_globaladmin`) VALUES ('list', 'OnePlace\\POS\\Controller\\CheckoutController', 'Kasse bedienen', '', '', '0', '0');
INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`, `needs_globaladmin`) VALUES ('done', 'OnePlace\\POS\\Controller\\CheckoutController', 'Bestellung aufnehmen', '', '', '0', '0');

INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`, `needs_globaladmin`) VALUES ('orderlist', 'OnePlace\\POS\\Controller\\ApiController', 'Neuste Bestellungen anzeigen', '', '', '0', '1');
INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`, `needs_globaladmin`) VALUES ('print', 'OnePlace\\POS\\Controller\\ApiController', 'Bestellung drucken', '', '', '0', '1');
INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`, `needs_globaladmin`) VALUES ('confirm', 'OnePlace\\POS\\Controller\\ApiController', 'Bestellung bestätigen', '', '', '0', '1');

INSERT INTO `settings` (`settings_key`, `settings_value`) VALUES ('pos-master-url', 'https://master-pos.1plc.ch');
INSERT INTO `settings` (`settings_key`, `settings_value`) VALUES ('pos-master-authkey', '[GENERATE YOUR KEY ON YOUR MASTER PLC]');
INSERT INTO `settings` (`settings_key`, `settings_value`) VALUES ('pos-master-authtoken', '[GENERATE YOUR TOKEN ON YOUR MASTER PLC]');

INSERT INTO `settings` (`settings_key`, `settings_value`) VALUES ('pos-login', 'pos@localhost');