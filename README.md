# 🍕 CRM TPV FastFood

Sistema completo de **CRM y TPV (Punto de Venta)** para negocios de comida rápida (pizzerías, hamburgueserías, bocadillerías, kebabs, sushi, etc.) desarrollado con **Laravel 11 + MySQL** y **AdminLTE 3 + Bootstrap 5**.

## ✨ Características incluidas

### 📊 Dashboard
- KPIs en tiempo real (ventas hoy, mes, ticket medio, clientes)
- Gráfica de ventas últimos 7 días (línea + nº pedidos)
- Donut de ventas por tipo (mesa/domicilio/recogida/teléfono)
- Histograma de ventas por hora del día
- Top 10 productos del mes
- Pedidos en curso en tiempo real
- Ventas por categoría con barras de progreso
- Alertas de stock bajo

### ⚙️ Configuración (módulo completo con pestañas)
- **Empresa**: nombre, CIF/NIF, dirección, contacto, logo, favicon
- **Moneda e impuestos**: símbolo, posición, decimales, separadores, IVA general/reducido (con vista previa)
- **TPV / Tickets**: series, numeración automática, ancho papel (58/80mm), texto cabecera/pie, logo en ticket
- **Módulos**: activar/desactivar mesas, domicilio, recogida, teléfono. Coste envío, pedido mínimo, horarios
- **Apariencia**: color primario/secundario, tema claro/oscuro
- **Fidelización**: programa de puntos configurable

### 🛒 TPV (Punto de Venta) táctil
- Pantalla táctil optimizada para tablets
- Filtrado por categorías con colores
- Búsqueda rápida por nombre o código de barras
- Carrito (ticket) en tiempo real con cantidades editables
- 5 modalidades: Mostrador / Mesa / Domicilio / Recogida / Telefónico
- Selector de mesa con zonas
- Búsqueda autocomplete de cliente CRM
- Modal de cobro con cálculo de cambio
- Métodos de pago: efectivo, tarjeta, Bizum, transferencia
- Impresión automática de ticket (formato 80mm)

### 📦 Catálogo (Productos + Categorías)
- Categorías con icono FontAwesome y color
- Productos con foto, descripción, ingredientes, alérgenos
- Precios con oferta temporal
- Etiquetas: vegetariano, vegano, sin gluten, picante (0-5)
- Calorías, tiempo de preparación
- Control de stock con alertas
- Disponibilidad por canal (local/domicilio/recogida)

### 👥 CRM Clientes
- Ficha completa con datos personales y dirección
- Tipos: particular / empresa / VIP
- Historial de pedidos y total gastado
- Programa de puntos de fidelización
- Detección automática de cumpleaños 🎂
- Descuento fijo por cliente
- Búsqueda multicampo

### 📋 Gestión de pedidos
- Listado completo con filtros (estado, tipo, fechas)
- Detalle del pedido con productos y pagos
- Cambio de estado (pendiente → preparación → preparado → entregado → cobrado)
- Reimpresión de ticket

### 🍽️ Mesas y zonas
- Múltiples zonas (salón, terraza, barra)
- Estados: libre / ocupada / reservada / cobrando
- Pedido vinculado a mesa

### 💵 Caja
- Apertura/cierre de caja por turno
- Movimientos: ingresos, gastos, ajustes
- Cuadre automático con descuadre
- Total por método de pago

---

## 🚀 Instalación

### Requisitos

- PHP 8.2 o superior
- MySQL 5.7 / MariaDB 10.3 o superior
- Composer 2.x
- Servidor web (Apache, Nginx, o `php artisan serve`)
- Extensiones PHP: `pdo_mysql`, `mbstring`, `xml`, `gd` o `imagick`, `bcmath`, `fileinfo`

### Pasos de instalación

```bash
# 1. Clonar o descomprimir el proyecto
cd C:\TVP\crm-tpv-fastfood

# 2. Instalar dependencias de PHP
composer install

# 3. Copiar el archivo de entorno
copy .env.example .env

# 4. Generar APP_KEY
php artisan key:generate

# 5. Crear la base de datos en MySQL
# Conéctate a MySQL y ejecuta:
#   CREATE DATABASE tpv_fastfood_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 6. Ejecutar migraciones
php artisan migrate

# 7. Cargar datos de ejemplo (opcional pero recomendado)
php artisan db:seed

# 8. Crear enlace simbólico para subida de archivos
php artisan storage:link

# 9. Levantar servidor de desarrollo
php artisan serve
```

Luego abre el navegador en: **http://localhost:8000**

### 🔑 Credenciales por defecto

| Usuario | Email | Contraseña | Rol |
|---------|-------|------------|-----|
| Administrador | `admin@tpv.local` | `admin123` | admin |
| Cajero | `cajero@tpv.local` | `cajero123` | cajero |

> ⚠️ **Importante**: Cambia las contraseñas tras la primera entrada.

---

## ⚙️ Configuración de MySQL

El archivo `.env` viene preconfigurado para los siguientes parámetros (ajusta si tu MySQL es distinto):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tpv_fastfood_crm
DB_USERNAME=root
DB_PASSWORD=
```

### XAMPP/WAMP/Laragon

1. Inicia Apache + MySQL
2. Abre phpMyAdmin (http://localhost/phpmyadmin)
3. Crea la base de datos `tpv_fastfood_crm` con cotejamiento `utf8mb4_unicode_ci`
4. Ejecuta los pasos 6-8 de instalación

---

## 📁 Estructura del proyecto

```
crm-tpv-fastfood/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # Controladores
│   │   └── Middleware/         # ShareConfiguracion (inyecta config global)
│   ├── Models/                 # Modelos Eloquent
│   └── Providers/
├── bootstrap/
├── config/                     # app.php, database.php, auth.php, session.php, filesystems.php
├── database/
│   ├── migrations/             # 9 migraciones (configuración, productos, clientes, pedidos…)
│   └── seeders/                # Datos demo: pizzería con 8 categorías y 38 productos
├── public/                     # index.php, .htaccess
├── resources/
│   └── views/
│       ├── layouts/app.blade.php       # Layout principal con sidebar
│       ├── auth/login.blade.php        # Login con diseño profesional
│       ├── dashboard/index.blade.php   # Dashboard con 4 gráficos Chart.js
│       ├── configuracion/edit.blade.php # 6 pestañas
│       ├── tpv/index.blade.php         # Punto de venta táctil
│       ├── tpv/ticket.blade.php        # Ticket imprimible 80mm
│       ├── productos/{index,form}.blade.php
│       ├── categorias/{index,form}.blade.php
│       ├── clientes/{index,form,show}.blade.php
│       └── pedidos/{index,show}.blade.php
└── routes/
    ├── web.php                 # Todas las rutas web
    └── api.php
```

---

## 💡 Sugerencias de funcionalidades para negocio real

He pensado en estas funcionalidades extra que aportan valor a un negocio real (pueden añadirse en futuras iteraciones):

### 📈 Inteligencia de negocio
- **Predicción de demanda**: con histórico de meses anteriores y festivos, predecir productos a preparar
- **Análisis de rentabilidad por producto**: precio venta vs precio costo, margen real
- **Comparativa de cajeros**: ranking de ventas, ticket medio por usuario
- **Dashboard de cocina**: pantalla independiente con pedidos pendientes y tiempos

### 🚚 Reparto a domicilio
- **Asignación de repartidores** con app móvil (geolocalización)
- **Cálculo de zonas de reparto** con coste de envío variable según código postal
- **Integración con Google Maps** para rutas optimizadas
- **Estimación de tiempo de llegada** automática

### 💳 Integraciones
- **Pasarelas de pago online** (Stripe, Redsys, PayPal) para pedidos web
- **Verifactu / TicketBAI / SII** según normativa fiscal española
- **Glovo, Uber Eats, Just Eat** vía API para recibir pedidos externos
- **Impresoras térmicas**: integración nativa USB/red (ESC/POS) para tickets y comandas en cocina
- **WhatsApp Business** para enviar notificaciones y confirmaciones
- **TPV físico tarjeta** (lectores Stripe Reader, SumUp, Verifone)

### 🎁 Marketing y fidelización
- **Cupones y vales descuento** con código y caducidad
- **Newsletter por email** automático (clientes que cumplen años, inactivos > 30 días)
- **Programa "trae a un amigo"** con código de referido
- **Encuestas de satisfacción** post-pedido
- **Sistema de reviews** internas

### 🍽️ Operativa avanzada
- **Reservas online** con confirmación
- **Carta digital QR** para mesas (sin app, móvil del cliente)
- **Pedido desde la mesa** con QR (sin camarero)
- **Comanda inteligente**: separación automática por estaciones (frío/caliente/postres)
- **Control de mermas** y rotura de stock
- **Recetas y escandallos** para calcular consumos automáticamente
- **Inventario con compras a proveedores** (albaranes y facturas)

### 📊 Reportes (módulo "LISTADOS" de la imagen)
- Reporte de ventas (por día/semana/mes/año)
- Reporte de IVA
- Cuenta de resultados (ingresos vs gastos)
- Listado de productos más/menos vendidos
- ABC de productos
- Listado de clientes inactivos
- Listado de cumpleaños del mes
- Exportación a Excel y PDF

### 💾 Backup y seguridad (módulo "PROTEGIDO" de la imagen)
- **Backup automático** programado (BD + imágenes) a Drive/Dropbox/S3
- **Auditoría**: log de accesos, anulaciones, descuentos
- **Roles y permisos granulares** (admin, gerente, cajero, cocinero, repartidor)
- **PIN de cajero** para abrir caja
- **Modo offline** del TPV con sincronización al recuperar conexión

### 📱 Apps móviles
- **App de cliente** (iOS/Android): catálogo, pedido, seguimiento en tiempo real, fidelización
- **App de repartidor**: pedidos asignados, navegación, marcar entregado
- **App de cocina** (Kitchen Display System): tablet en cocina con comandas

### 🌐 Multi-tienda / multi-empresa
- **Franquicias**: panel central con varias tiendas, cada una con su configuración
- **Almacén central** y stock por tienda
- **Comparativa entre locales**

### 🤖 Otras ideas
- **Chatbot Telegram/WhatsApp** para tomar pedidos
- **Integración con voz (Alexa)** para pedidos rápidos
- **IA**: recomendador de productos basado en historial del cliente

---

## 🎨 Personalización

### Cambiar logo y colores

1. Inicia sesión
2. Ve a **Configuración → Empresa** (sube logo)
3. Ve a **Configuración → Apariencia** (cambia colores primario/secundario)
4. Los cambios se aplican a toda la web automáticamente

### Cambiar moneda

1. **Configuración → Moneda e impuestos**
2. Selecciona moneda ISO (EUR, USD, MXN, ARS, COP, etc.)
3. Ajusta símbolo, posición (€ 100 vs 100 €), decimales y separadores
4. Configura IVA general y reducido

### Activar/desactivar módulos

1. **Configuración → Módulos**
2. Toggle on/off para: Mesas, Domicilio, Recogida, Telefónico
3. Sólo aparecerán en el TPV los que estén activos

---

## 🛠️ Comandos útiles

```bash
# Limpiar cachés
php artisan optimize:clear

# Resetear BD y volver a sembrar (CUIDADO: borra datos)
php artisan migrate:fresh --seed

# Ejecutar sólo seeders
php artisan db:seed

# Crear nuevo usuario admin desde tinker
php artisan tinker
>>> App\Models\User::create(['name'=>'Otro','email'=>'otro@tpv.local','password'=>bcrypt('xxx'),'rol'=>'admin'])
```

---

## 📝 Licencia

MIT — uso libre para tu negocio.

---

## 📞 Soporte

Para dudas o ampliaciones: este proyecto está hecho como base sólida y está pensado para ser ampliable. Puedes pedirme nuevas funcionalidades de la lista de sugerencias en cualquier momento.

¡Buen provecho! 🍕🍔🌭
