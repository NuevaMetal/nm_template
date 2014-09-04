-- 1º Utilizamos el pluging para mover todas las cat a tags

-- ---------------------------------------------
-- 2º 
-- Eliminar todas las categorías innecesarias
DELETE tt, tr
FROM wp_terms t, wp_term_taxonomy tt, wp_term_relationships tr
WHERE t.term_id = tt.term_id
and tt.term_taxonomy_id = tr.term_taxonomy_id 
and tt.taxonomy = 'Category'
and t.slug not in ('bandas','videos','entrevistas','criticas','cronicas','noticias', 'conciertos','sin-categoria');
-- ---------------------------------------------

-- ---------------------------------------------
-- 3º 
-- Borrar todas las tags ee-uu (están duplicadas, el plugin las pasaría a usa)
DELETE t, tt, tr
FROM wp_terms t, wp_term_taxonomy tt, wp_term_relationships tr
WHERE t.term_id = tt.term_id
and tt.term_taxonomy_id = tr.term_taxonomy_id 
and tt.taxonomy = 'post_tag'
and t.slug = 'ee-uu';


