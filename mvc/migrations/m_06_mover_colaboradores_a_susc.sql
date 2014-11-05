-- Mover todos los colaboradores a suscriptores.
UPDATE wp_usermeta 
SET meta_value =  'a:1:{s:10:"subscriber";b:1;}' 
WHERE meta_key = 'wp_capabilities' 
AND meta_value LIKE  '%contributor%'
