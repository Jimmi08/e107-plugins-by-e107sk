 
CREATE TABLE `team_members` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `title` varchar(200) NOT NULL DEFAULT '',
  `sef` varchar(200) NOT NULL DEFAULT '',
  `position` varchar(200) NOT NULL DEFAULT '',
  `phone` varchar(200) NOT NULL DEFAULT '',
  `email` varchar(200) NOT NULL DEFAULT '',
  `website` varchar(200) NOT NULL DEFAULT '',
  `summary` varchar(200) NOT NULL DEFAULT '',
  `bio` text NOT NULL,
  `image` varchar(200) NOT NULL DEFAULT '',
  `date` int(11) NOT NULL DEFAULT 0,
  `last_updated` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL DEFAULT 0,
  `item_order` int(11) NOT NULL DEFAULT 0,
  `links_multi` text NOT NULL,
  `facts_multi` text NOT NULL,
  `awards_multi` text NOT NULL, 
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

 