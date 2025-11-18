# Guía de Configuración para Visualización de Archivos e Imágenes

## Problema Resuelto
No se podían visualizar las imágenes y documentos cargados en el sistema desde el frontend.

## Solución Implementada

### 1. Enlace Simbólico Creado ✅

Se ejecutó el comando:
```bash
php artisan storage:link
```

Esto creó un enlace simbólico entre:
- `public/storage` → `storage/app/public`

Esto permite que los archivos guardados en `storage/app/public` sean accesibles públicamente a través de la carpeta `public/storage`.

### 2. URLs Automáticas en los Modelos ✅

Se agregaron **accessors** automáticos en los modelos para devolver URLs completas:

#### Modelo Maintenance
Ahora devuelve automáticamente el campo `physical_format_url`:
```php
// Antes (solo ruta):
"physical_format_path": "maintenances/formats/abc123.pdf"

// Ahora (con URL completa):
"physical_format_path": "maintenances/formats/abc123.pdf",
"physical_format_url": "http://localhost/storage/maintenances/formats/abc123.pdf"
```

#### Modelo MaintenanceImage
Ahora devuelve automáticamente el campo `image_url`:
```php
// Antes (solo ruta):
"image_path": "maintenances/images/img001.jpg"

// Ahora (con URL completa):
"image_path": "maintenances/images/img001.jpg",
"image_url": "http://localhost/storage/maintenances/images/img001.jpg"
```

---

## Configuración Necesaria

### Paso 1: Verificar APP_URL en .env

**IMPORTANTE:** Debes configurar correctamente la URL de tu aplicación.

Abre el archivo `.env` y actualiza la variable `APP_URL`:

```env
# Si tu backend está en localhost con Laragon
APP_URL=http://localhost

# O si usas un dominio virtual de Laragon
APP_URL=http://computermanagement-backend.test

# O si usas una IP específica
APP_URL=http://192.168.1.100

# O si usas un puerto específico
APP_URL=http://localhost:8000
```

**Ejemplo para Laragon con dominio virtual:**
```env
APP_URL=http://computermanagement-backend.test
```

Después de cambiar el `.env`, ejecuta:
```bash
php artisan config:clear
php artisan cache:clear
```

---

### Paso 2: Verificar CORS para Archivos

Si tu frontend está en un dominio diferente al backend, asegúrate de que CORS esté configurado correctamente.

Archivo: `config/cors.php`

```php
return [
    'paths' => ['api/*', 'storage/*'],  // Agregar 'storage/*'
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'], // O especifica tu dominio del frontend
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
```

---

## Cómo Usar las URLs en el Frontend

### Ejemplo de Respuesta de API Ahora

Cuando obtienes un mantenimiento, recibirás:

```json
{
  "id": 1,
  "maintenance_date": "2025-11-18",
  "physical_format_path": "maintenances/formats/documento123.pdf",
  "physical_format_url": "http://localhost/storage/maintenances/formats/documento123.pdf",
  "images": [
    {
      "id": 1,
      "image_path": "maintenances/images/foto001.jpg",
      "image_url": "http://localhost/storage/maintenances/images/foto001.jpg",
      "image_type": "antes",
      "description": "Estado antes del mantenimiento"
    },
    {
      "id": 2,
      "image_path": "maintenances/images/foto002.jpg",
      "image_url": "http://localhost/storage/maintenances/images/foto002.jpg",
      "image_type": "despues",
      "description": "Estado después del mantenimiento"
    }
  ]
}
```

### Uso en Frontend (React/Vue/Angular)

#### Mostrar Imagen:
```javascript
// React
<img
  src={maintenance.images[0].image_url}
  alt={maintenance.images[0].description}
/>

// Vue
<img
  :src="maintenance.images[0].image_url"
  :alt="maintenance.images[0].description"
/>

// Angular
<img
  [src]="maintenance.images[0].image_url"
  [alt]="maintenance.images[0].description"
/>
```

#### Enlace a PDF:
```javascript
// React
<a
  href={maintenance.physical_format_url}
  target="_blank"
  rel="noopener noreferrer"
>
  Ver Formato Físico (PDF)
</a>

// Vue
<a
  :href="maintenance.physical_format_url"
  target="_blank"
  rel="noopener noreferrer"
>
  Ver Formato Físico (PDF)
</a>

// Angular
<a
  [href]="maintenance.physical_format_url"
  target="_blank"
  rel="noopener noreferrer"
>
  Ver Formato Físico (PDF)
</a>
```

#### Visualizador de PDF embebido:
```javascript
// React
<iframe
  src={maintenance.physical_format_url}
  width="100%"
  height="600px"
  title="Formato de Mantenimiento"
/>

// Vue
<iframe
  :src="maintenance.physical_format_url"
  width="100%"
  height="600px"
  title="Formato de Mantenimiento"
/>
```

---

## Estructura de Carpetas

```
storage/
└── app/
    └── public/              ← Archivos guardados aquí
        └── maintenances/
            ├── formats/     ← PDFs y formatos físicos
            │   ├── abc123.pdf
            │   └── xyz456.pdf
            └── images/      ← Imágenes de mantenimiento
                ├── img001.jpg
                ├── img002.jpg
                └── img003.png

public/
└── storage/                 ← Enlace simbólico (apunta a storage/app/public)
    └── maintenances/
        ├── formats/
        └── images/
```

**URLs Generadas:**
- `http://localhost/storage/maintenances/formats/abc123.pdf`
- `http://localhost/storage/maintenances/images/img001.jpg`

---

## Verificación de Configuración

### 1. Verificar Enlace Simbólico

**Windows (CMD o PowerShell):**
```bash
dir public\storage
```

Deberías ver algo como:
```
<SYMLINK>      storage [C:\laragon\www\computermanagement-backend\storage\app\public]
```

**Linux/Mac:**
```bash
ls -la public/storage
```

Deberías ver algo como:
```
storage -> ../../storage/app/public
```

### 2. Probar Acceso Directo

1. Sube un mantenimiento con una imagen
2. Copia la URL generada (ej: `http://localhost/storage/maintenances/images/img001.jpg`)
3. Pégala directamente en tu navegador
4. Deberías ver la imagen

Si no funciona, verifica:
- ✅ El enlace simbólico existe
- ✅ APP_URL está configurado correctamente
- ✅ El archivo realmente existe en `storage/app/public/maintenances/...`

### 3. Verificar Permisos (Solo Linux/Mac)

```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

---

## Problemas Comunes y Soluciones

### Problema 1: "404 Not Found" al acceder a imágenes

**Causa:** El enlace simbólico no existe

**Solución:**
```bash
php artisan storage:link
```

Si ya existe, elimínalo primero:
```bash
# Windows
rmdir public\storage

# Linux/Mac
rm public/storage

# Luego créalo de nuevo
php artisan storage:link
```

---

### Problema 2: Las URLs tienen "localhost" pero debería ser otra cosa

**Causa:** APP_URL no está configurado correctamente

**Solución:**
1. Edita `.env`
2. Cambia `APP_URL` a tu URL correcta
3. Ejecuta:
```bash
php artisan config:clear
php artisan cache:clear
```

---

### Problema 3: CORS bloquea el acceso a imágenes desde el frontend

**Causa:** El navegador bloquea recursos de otro dominio

**Solución:**
1. Edita `config/cors.php`
2. Agrega `'storage/*'` a `paths`:
```php
'paths' => ['api/*', 'storage/*'],
```
3. Ejecuta:
```bash
php artisan config:clear
```

---

### Problema 4: Las imágenes no cargan en producción

**Verificar:**
1. ✅ El enlace simbólico existe en el servidor
2. ✅ APP_URL apunta al dominio de producción (no localhost)
3. ✅ Los permisos de carpetas están correctos
4. ✅ El servidor web (Apache/Nginx) permite acceso a `public/storage`

---

## Respaldo de Archivos

Los archivos se guardan en:
```
storage/app/public/maintenances/
```

**Para hacer respaldo:**
```bash
# Comprimir carpeta
tar -czf maintenances_backup.tar.gz storage/app/public/maintenances/

# O copiar directamente
cp -r storage/app/public/maintenances /ruta/respaldo/
```

---

## Migración a Producción

Al mover tu aplicación a producción:

1. Asegúrate de que `.env` tenga el APP_URL correcto:
```env
APP_URL=https://tudominio.com
```

2. Crea el enlace simbólico en el servidor:
```bash
php artisan storage:link
```

3. Ajusta permisos (Linux):
```bash
chmod -R 755 storage
chown -R www-data:www-data storage
```

4. Limpia caché:
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

---

## Ejemplo Completo de Uso en Frontend

### React Component

```jsx
import React from 'react';

function MaintenanceDetail({ maintenance }) {
  return (
    <div className="maintenance-detail">
      <h2>Mantenimiento #{maintenance.id}</h2>

      {/* Mostrar formato físico si existe */}
      {maintenance.physical_format_url && (
        <div className="physical-format">
          <h3>Formato Físico</h3>
          <a
            href={maintenance.physical_format_url}
            target="_blank"
            rel="noopener noreferrer"
            className="btn-download"
          >
            📄 Descargar PDF
          </a>

          {/* O visualizador embebido */}
          <iframe
            src={maintenance.physical_format_url}
            width="100%"
            height="600px"
            title="Formato de Mantenimiento"
          />
        </div>
      )}

      {/* Mostrar imágenes */}
      <div className="images-gallery">
        <h3>Imágenes del Mantenimiento</h3>
        <div className="gallery-grid">
          {maintenance.images.map((image) => (
            <div key={image.id} className="image-item">
              <img
                src={image.image_url}
                alt={image.description || image.image_type}
                className="maintenance-image"
              />
              <p className="image-type">
                {image.image_type === 'antes' && '📷 Antes'}
                {image.image_type === 'despues' && '✅ Después'}
                {image.image_type === 'equipo' && '🖥️ Equipo'}
                {image.image_type === 'formato' && '📋 Formato'}
              </p>
              {image.description && (
                <p className="image-desc">{image.description}</p>
              )}
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

export default MaintenanceDetail;
```

---

## Resumen

✅ **Enlace simbólico creado:** `php artisan storage:link`
✅ **Accessors agregados:** Los modelos ahora devuelven URLs completas automáticamente
✅ **Campos nuevos en respuestas API:**
   - `physical_format_url` en Maintenance
   - `image_url` en MaintenanceImage

**Usa estos campos en tu frontend para mostrar archivos e imágenes.**

---

**Fecha:** 18 de Noviembre, 2025
**Versión:** 1.0
