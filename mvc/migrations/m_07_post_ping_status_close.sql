-- Cerrar todos los ping_status de los posts
-- para evitar spammers
UPDATE wp_posts SET ping_status="closed";
