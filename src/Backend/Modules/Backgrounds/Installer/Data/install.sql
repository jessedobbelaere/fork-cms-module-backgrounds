CREATE TABLE IF NOT EXISTS `backgrounds` (
 `id` int(11) NOT NULL auto_increment,
 `meta_id` int(11) NOT NULL,
 `language` varchar(5),
 `title` varchar(255) NOT NULL,
 `image` varchar(255) NOT NULL,
 `hidden` ENUM('Y','N') DEFAULT 'N',
 `extra_id` int(11) NOT NULL,
 `background_size` varchar(255) NOT NULL,
 `background_position_horizontal` varchar(255) NOT NULL,
 `background_position_vertical` varchar(255) NOT NULL,
 `background_repeat` varchar(255) NOT NULL,
 `created_on` datetime NOT NULL,
 `edited_on` datetime NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;