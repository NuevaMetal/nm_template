-- Borrar todos los usuarios inactivos y bots, así como sus metadatos

-- Obtener todos los usuarios que no tienen metadatos
-- SELECT * FROM wp_users WHERE ID NOT IN (SELECT DISTINCT user_id FROM  wp_usermeta );

-- Borrar totos los usuarios que no guardaron nunca su perfil (no lo modificaron) 
-- por eso tienen con menos de 15 tuplas de meta-información
delete FROM wp_users
WHERE ID IN (
		SELECT user_id
		FROM wp_usermeta
		GROUP BY user_id
		HAVING COUNT( user_id ) <= 15
	)
AND LENGTH( user_activation_key ) = 0

-- Borrar toda la info meta de los user_id inexistentes
DELETE FROM wp_usermeta WHERE user_id NOT IN (SELECT ID FROM wp_users);


