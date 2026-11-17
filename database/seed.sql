-- Optional demo data for a NEW/EMPTY installation only.
USE myradio;

INSERT INTO `genres` (`id`,`name`) VALUES (5,'Jazz');
INSERT INTO `genres` (`id`,`name`) VALUES (1,'Klassik');
INSERT INTO `genres` (`id`,`name`) VALUES (7,'Pop');
INSERT INTO `genres` (`id`,`name`) VALUES (4,'Rock');
INSERT INTO `stations` (`id`,`name`,`genre_id`,`stream_url`,`web_url`,`logo`) VALUES (7,'JazzRadio Berlin',5,'https://streaming.radio.co/s774887f7b/listen','https://jazzradio.net/','http://www.tolle-produktion.de/wp-content/uploads/jazz-radio-1.jpg');
INSERT INTO `stations` (`id`,`name`,`genre_id`,`stream_url`,`web_url`,`logo`) VALUES (8,'Klassik Radio',1,'https://stream.klassikradio.de/live/mp3-192/','','https://upload.wikimedia.org/wikipedia/commons/1/1d/Logo_klassikradio.svg');
INSERT INTO `stations` (`id`,`name`,`genre_id`,`stream_url`,`web_url`,`logo`) VALUES (5,'NDR 2',1,'https://icecast.ndr.de/ndr/ndr2/hamburg/mp3/128/stream.mp3','','https://thfvnext.bing.com/th/id/R.85ef007a77d5ec4c02b93d72e59d0127?rik=3Z2Y8plwDheJdQ&pid=ImgRaw&r=0');
INSERT INTO `stations` (`id`,`name`,`genre_id`,`stream_url`,`web_url`,`logo`) VALUES (6,'ROCK ANTENNE Hamburg',4,'https://stream.rockantenne.hamburg/rockantenne-hamburg/stream/mp3','','https://www.rockantenne.de/media/cache/3/version/13/rahh-v1.jpg/c512e751fb8a59a0.webp');
INSERT INTO `stations` (`id`,`name`,`genre_id`,`stream_url`,`web_url`,`logo`) VALUES (4,'Radio Hamburg',7,'https://stream.radiohamburg.de/live/mp3-192/','','https://static.radiohamburg.de/img/1521/1434602/678000/Img_1_1/800/simulcast2');
INSERT INTO `tv_stations` (`id`,`name`,`stream_url`,`stream_type`,`web_url`,`logo`) VALUES (1,'HR Fernsehen','https://hrhls.akamaized.net/hls/live/2024525/hrhls/master.m3u8','hls','https://www.ardmediathek.de/hr/live','/radio_projekt/tv-player/assets/images/signal-tv.svg');
INSERT INTO `tv_stations` (`id`,`name`,`stream_url`,`stream_type`,`web_url`,`logo`) VALUES (2,'ZDF','https://zdf-hls-15.akamaized.net/hls/live/2016498/de/high/master.m3u8','hls','https://www.zdf.de/live-tv','/radio_projekt/tv-player/assets/images/signal-tv.svg');
INSERT INTO `tv_stations` (`id`,`name`,`stream_url`,`stream_type`,`web_url`,`logo`) VALUES (3,'BR Fernsehen Süd','https://mcdn.br.de/br/fs/bfs_sued/hls/de/master.m3u8','hls','https://www.br.de/mediathek/live','/radio_projekt/tv-player/assets/images/signal-tv.svg');
INSERT INTO `tv_stations` (`id`,`name`,`stream_url`,`stream_type`,`web_url`,`logo`) VALUES (4,'ARTE','https://artesimulcast.akamaized.net/hls/live/2030993/artelive_de/master_v720.m3u8','hls','https://www.arte.tv/de/live/','/radio_projekt/tv-player/assets/images/signal-tv.svg');
INSERT INTO `tv_stations` (`id`,`name`,`stream_url`,`stream_type`,`web_url`,`logo`) VALUES (5,'phoenix','https://zdf-hls-19.akamaized.net/hls/live/2016502/de/high/master.m3u8','hls','https://www.phoenix.de/livestream.html','/radio_projekt/tv-player/assets/images/signal-tv.svg');
INSERT INTO `tv_stations` (`id`,`name`,`stream_url`,`stream_type`,`web_url`,`logo`) VALUES (6,'WDR Fernsehen','https://wdrfs247.akamaized.net/hls/live/681509/wdr_msl4_fs247/index.m3u8','hls','https://www1.wdr.de/fernsehen/index.html','/radio_projekt/tv-player/assets/images/signal-tv.svg');
