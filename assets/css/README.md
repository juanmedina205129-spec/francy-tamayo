# Carpeta CSS organizada

Las hojas se enlazan desde `include/templates/header.php` (lista `$hojasEstilo`), en este orden.
Si agregas un archivo nuevo, añádelo a esa lista; la versión para la caché se calcula sola.

- `base/global.css`: reset, fondo global, contenedores, titulos, animaciones.
- `layout/header.css`: barra superior y navegacion.
- `layout/footer.css`: pie de pagina.
- `componentes/ui.css`: tarjetas, botones y utilidades reutilizables.
- `paginas/index.css`: hero, catalogo publico y secciones del inicio.
- `paginas/contacto.css`: formulario de encargo.
- `paginas/admin.css`: dashboard y pedidos del administrador.
- `paginas/crear.css`: formularios de crear/editar producto.
- `paginas/menu.css`: tabla/listado de productos del administrador.
- `paginas/login.css`: login y cambio de contraseña del administrador.
- `paginas/tienda.css`: buscador, carrito y guia "Como funciona".
