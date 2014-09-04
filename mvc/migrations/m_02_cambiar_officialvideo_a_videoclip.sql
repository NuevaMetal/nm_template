-- ---------------------------------------------
-- Cambiamos en todos los títulos donde pone 'official video' por 'Videoclip'
-- ---------------------------------------------

UPDATE wp_posts 
SET post_title = REPLACE( post_title,  'official video',  'Videoclip' ) 
WHERE post_title LIKE  '%official video%';

UPDATE wp_posts 
SET post_title = REPLACE( post_title,  'Official video',  'Videoclip' ) 
WHERE post_title LIKE  '%official video%';

UPDATE wp_posts 
SET post_title = REPLACE( post_title,  'Official Video',  'Videoclip' ) 
WHERE post_title LIKE  '%official video%';

UPDATE wp_posts 
SET post_title = REPLACE( post_title,  'Official Vídeo',  'Videoclip' ) 
WHERE post_title LIKE  '%official video%';

UPDATE wp_posts 
SET post_title = REPLACE( post_title,  'OFFICIAL VIDEO',  'Videoclip' ) 
WHERE post_title LIKE  '%official video%';

-- ---------------------------------------------

