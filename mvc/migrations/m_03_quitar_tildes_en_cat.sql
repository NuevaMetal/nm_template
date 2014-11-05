-- Quito las tildes a las categorías que tienen tilde
UPDATE wp_terms SET name =  'Videos' WHERE slug =  'videos';
UPDATE wp_terms SET name =  'Criticas' WHERE slug =  'criticas';
UPDATE wp_terms SET name =  'Cronicas' WHERE slug =  'cronicas';

