-- Corrige la ortografía (tildes) de los productos de ejemplo cargados con schema.sql.
-- Solo cambia los textos que siguen exactamente como venían de fábrica, así que no pisa
-- lo que se haya editado desde el panel. Es segura de ejecutar más de una vez.
USE francytamayo;

UPDATE productos SET tipo = 'Pintura al óleo' WHERE tipo = 'Pintura al oleo';
UPDATE productos SET nombre = 'Dos gatitos - óleo' WHERE nombre = 'Dos gatitos - oleo';
UPDATE productos SET nombre = 'Perro blanco - óleo' WHERE nombre = 'Perro blanco - oleo';
UPDATE productos SET nombre = 'Martín pescador' WHERE nombre = 'Martin pescador';
UPDATE productos SET nombre = 'Retrato canino clásico' WHERE nombre = 'Retrato canino clasico';
UPDATE productos SET descripcion = 'Pintura al óleo de mascotas.' WHERE descripcion = 'Pintura al oleo de mascotas.';
UPDATE productos SET descripcion = 'Ilustración de ave en acuarela.' WHERE descripcion = 'Ilustracion de ave en acuarela.';
UPDATE productos SET descripcion = 'Ilustración de ave martín pescador.' WHERE descripcion = 'Ilustracion de ave martin pescador.';
UPDATE productos SET descripcion = 'Retrato de perro al óleo.' WHERE descripcion = 'Retrato de perro al oleo.';
UPDATE productos SET descripcion = 'Retrato clásico de mascota.' WHERE descripcion = 'Retrato clasico de mascota.';
