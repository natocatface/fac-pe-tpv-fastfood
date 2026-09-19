# ⚡ Instalación Rápida (5 minutos)

## Paso 1 - Requisitos previos

Asegúrate de tener instalado:

- ✅ **PHP 8.2 o superior** ([descargar](https://www.php.net/downloads.php) o XAMPP)
- ✅ **MySQL** (incluido en XAMPP/WAMP/Laragon)
- ✅ **Composer** ([descargar](https://getcomposer.org/download/))

> Comprueba versiones:
> ```bash
> php -v
> composer -V
> ```

## Paso 2 - Crear base de datos

Abre **phpMyAdmin** (http://localhost/phpmyadmin) o cliente MySQL y ejecuta:

```sql
CREATE DATABASE tpv_fastfood_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## Paso 3 - Instalar el proyecto

Abre una terminal en `C:\TVP\crm-tpv-fastfood` y ejecuta:

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

> Si tu MySQL tiene contraseña, edita `.env` y cambia `DB_PASSWORD=` antes de migrar.

## Paso 4 - Iniciar el servidor

```bash
php artisan serve
```

## Paso 5 - Entrar

Abre el navegador en: **http://localhost:8000**

Credenciales:
- **Email:** `admin@tpv.local`
- **Contraseña:** `admin123`

---

## ✅ Qué encontrarás (datos demo precargados)

| Módulo | Datos demo |
|---|---|
| ⚙️ Configuración | Pizzería La Italiana, moneda EUR, IVA 10%/4% |
| 🏷️ Categorías | 8 (Pizzas, Hamburguesas, Bocadillos, Pastas, Ensaladas, Bebidas, Postres, Acompañamientos) |
| 🍕 Productos | ~38 productos con precios, ingredientes, alérgenos |
| 👥 Clientes | 12 clientes (incluidos 2 con cumpleaños hoy 🎂 y empresas) |
| 🍽️ Mesas | 18 mesas en 3 zonas (Salón, Terraza, Barra) |
| 💵 Cajas | 11 cajas (10 cerradas con histórico + 1 abierta hoy) |
| 🧾 Pedidos | **~900 pedidos** en los últimos 60 días, repartidos en horas pico (mediodía y noche), con todos los tipos (mostrador/mesa/domicilio/recogida/teléfono) y estados |
| 💳 Pagos | Pagos asociados con métodos variados (efectivo 50%, tarjeta 35%, bizum 10%, transferencia 5%) |

### 📊 El dashboard mostrará desde el primer momento:
- Ventas hoy con variación vs ayer ✅
- Ventas mes vs mes anterior ✅
- Ticket medio real ✅
- Gráfico de ventas últimos 7 días con curva ✅
- Donut con distribución de tipos de pedido ✅
- Histograma de ventas por hora (picos en comida y cena) ✅
- Top 10 productos más vendidos del mes ✅
- Pedidos en curso (algunos en distintos estados) ✅
- Ventas por categoría con barras de progreso ✅

¡Empieza a vender! 🚀
