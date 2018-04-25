DROP TABLE IF EXISTS `{PREFIX}evoshop_carts`;
CREATE TABLE `{PREFIX}evoshop_carts` (
  `id` int(11) NOT NULL,
  `hash` varchar(255) NOT NULL,
  `userid` int(11) DEFAULT NULL,
  `cart` text NOT NULL,
  `ip` varchar(255) NOT NULL,
  `last_change_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `{PREFIX}evoshop_carts`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `{PREFIX}evoshop_carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;