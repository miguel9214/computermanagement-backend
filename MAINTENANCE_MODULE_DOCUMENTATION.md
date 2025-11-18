# Documentación del Módulo de Mantenimiento

## Índice
1. [Descripción General](#descripción-general)
2. [Estructura de la Base de Datos](#estructura-de-la-base-de-datos)
3. [Modelos y Relaciones](#modelos-y-relaciones)
4. [API Endpoints](#api-endpoints)
5. [Ejemplos de Uso](#ejemplos-de-uso)
6. [Características Especiales](#características-especiales)

---

## Descripción General

El módulo de mantenimiento permite gestionar el mantenimiento de equipos, impresoras y escáneres, así como llevar un historial detallado de cambios de periféricos y componentes.

### Características Principales
- Registro de mantenimientos preventivos, correctivos y de limpieza
- Gestión de fechas de mantenimiento y próximos mantenimientos
- Carga de múltiples imágenes del equipo (antes/después/equipo/formato)
- Carga de formato físico del mantenimiento (PDF o imagen hasta 50MB)
- Historial completo de cambios de periféricos y componentes
- Actualización automática de RAM/HDD en dispositivos
- Estadísticas de costos y cambios
- Sistema polimórfico (funciona con Device, Printer y Scanner)

---

## Estructura de la Base de Datos

### Tabla: `maintenances`

Almacena los registros de mantenimiento realizados o programados.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigint | ID único del mantenimiento |
| `maintainable_type` | string | Tipo de entidad (Device, Printer, Scanner) |
| `maintainable_id` | bigint | ID de la entidad |
| `maintenance_date` | date | Fecha en que se realizó el mantenimiento |
| `next_maintenance_date` | date | Fecha del próximo mantenimiento programado |
| `maintenance_type` | string | Tipo (preventivo, correctivo, limpieza, etc.) |
| `description` | text | Descripción del mantenimiento |
| `performed_tasks` | text | Tareas realizadas durante el mantenimiento |
| `technician` | string | Nombre del técnico que realizó el mantenimiento |
| `cost` | decimal(10,2) | Costo del mantenimiento |
| `status` | string | Estado (programado, en_proceso, completado, cancelado) |
| `notes` | text | Notas adicionales |
| `physical_format_path` | string | Ruta del archivo del formato físico |
| `created_by_user` | bigint | ID del usuario que creó el registro |
| `updated_by_user` | bigint | ID del usuario que actualizó el registro |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

**Índices:**
- `maintainable_type, maintainable_id` (compuesto)

**Relaciones:**
- `maintainable` → Polimórfica (Device, Printer, Scanner)
- `created_by_user` → users.id
- `updated_by_user` → users.id

---

### Tabla: `maintenance_images`

Almacena las imágenes asociadas a cada mantenimiento.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigint | ID único de la imagen |
| `maintenance_id` | bigint | ID del mantenimiento |
| `image_path` | string | Ruta del archivo de imagen |
| `image_type` | string | Tipo (equipo, antes, despues, formato) |
| `description` | text | Descripción de la imagen |
| `order` | integer | Orden de visualización |
| `created_by_user` | bigint | ID del usuario que subió la imagen |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

**Relaciones:**
- `maintenance_id` → maintenances.id (cascade on delete)
- `created_by_user` → users.id

---

### Tabla: `peripheral_change_histories`

Registra el historial de cambios de periféricos y componentes de dispositivos.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigint | ID único del cambio |
| `device_id` | bigint | ID del dispositivo |
| `change_date` | date | Fecha del cambio |
| `change_type` | string | Tipo (ram, hdd, ssd, teclado, mouse, monitor, impresora, escaner, otro) |
| `component_name` | string | Nombre del componente |
| `old_value` | string | Valor anterior (ej: "8GB RAM") |
| `new_value` | string | Nuevo valor (ej: "16GB RAM") |
| `reason` | text | Razón del cambio |
| `cost` | decimal(10,2) | Costo del cambio |
| `supplier` | string | Proveedor del componente |
| `technician` | string | Técnico que realizó el cambio |
| `notes` | text | Notas adicionales |
| `created_by_user` | bigint | ID del usuario que creó el registro |
| `updated_by_user` | bigint | ID del usuario que actualizó el registro |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

**Relaciones:**
- `device_id` → devices.id (cascade on delete)
- `created_by_user` → users.id
- `updated_by_user` → users.id

**Funcionalidad Especial:**
Al crear un cambio de tipo `ram`, `hdd` o `ssd`, el sistema actualiza automáticamente los campos correspondientes en el dispositivo.

---

## Modelos y Relaciones

### Modelo: `Maintenance`

**Ubicación:** `app/Models/Maintenance.php`

**Relaciones:**
```php
// Relación polimórfica - puede ser Device, Printer o Scanner
public function maintainable()

// Imágenes del mantenimiento (ordenadas)
public function images()

// Usuario que creó el registro
public function createdByUser()

// Usuario que actualizó el registro
public function updatedByUser()
```

**Campos Cast:**
- `maintenance_date` → date
- `next_maintenance_date` → date
- `cost` → decimal:2

---

### Modelo: `MaintenanceImage`

**Ubicación:** `app/Models/MaintenanceImage.php`

**Relaciones:**
```php
// Mantenimiento al que pertenece
public function maintenance()

// Usuario que subió la imagen
public function createdByUser()
```

**Campos Cast:**
- `order` → integer

---

### Modelo: `PeripheralChangeHistory`

**Ubicación:** `app/Models/PeripheralChangeHistory.php`

**Relaciones:**
```php
// Dispositivo al que pertenece
public function device()

// Usuario que creó el registro
public function createdByUser()

// Usuario que actualizó el registro
public function updatedByUser()
```

**Campos Cast:**
- `change_date` → date
- `cost` → decimal:2

---

### Relaciones Agregadas a Modelos Existentes

**Device:**
```php
public function maintenances() // Mantenimientos del dispositivo
public function peripheralChanges() // Historial de cambios
```

**Printer:**
```php
public function maintenances() // Mantenimientos de la impresora
```

**Scanner:**
```php
public function maintenances() // Mantenimientos del escáner
```

---

## API Endpoints

Todas las rutas requieren autenticación (`auth:api` middleware).

### Mantenimientos

#### 1. Listar Mantenimientos
```
GET /api/maintenances
```

**Parámetros de consulta:**
- `per_page` (int, default: 10) - Elementos por página
- `search` (string) - Buscar en descripción, técnico o tipo
- `type` (string) - Filtrar por tipo: device, printer, scanner
- `entity_id` (int) - Filtrar por ID de entidad específica
- `status` (string) - Filtrar por estado: programado, en_proceso, completado, cancelado

**Respuesta exitosa (200):**
```json
{
  "message": "Maintenances retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "maintainable_type": "App\\Models\\Device",
        "maintainable_id": 1,
        "maintenance_date": "2025-11-18",
        "next_maintenance_date": "2026-02-18",
        "maintenance_type": "preventivo",
        "description": "Limpieza general",
        "performed_tasks": "Limpieza interna, cambio de pasta térmica",
        "technician": "Juan Pérez",
        "cost": "50.00",
        "status": "completado",
        "notes": null,
        "physical_format_path": "maintenances/formats/abc123.pdf",
        "created_by_user": 3,
        "updated_by_user": 3,
        "maintainable": {
          "id": 1,
          "device_name": "PC-01",
          ...
        },
        "images": [
          {
            "id": 1,
            "image_path": "maintenances/images/img001.jpg",
            "image_type": "antes",
            "description": "Estado antes del mantenimiento",
            "order": 0
          }
        ]
      }
    ],
    "total": 50
  }
}
```

---

#### 2. Crear Mantenimiento
```
POST /api/maintenances
```

**Content-Type:** `multipart/form-data`

**Campos requeridos:**
- `maintainable_type` (string) - Valores: device, printer, scanner
- `maintainable_id` (integer) - ID de la entidad
- `maintenance_date` (date) - Formato: YYYY-MM-DD
- `maintenance_type` (string) - Tipo de mantenimiento

**Campos opcionales:**
- `next_maintenance_date` (date) - Debe ser posterior a maintenance_date
- `description` (text)
- `performed_tasks` (text)
- `technician` (string)
- `cost` (numeric) - Mínimo 0
- `status` (string) - Default: completado. Valores: programado, en_proceso, completado, cancelado
- `notes` (text)
- `physical_format` (file) - PDF, JPG, JPEG, PNG - Máx: 50MB
- `images[0]` (file) - JPG, JPEG, PNG - Máx: 10MB por imagen
- `images[1]` (file)
- `images[n]` (file)
- `image_types[0]` (string) - Valores: equipo, antes, despues, formato
- `image_types[1]` (string)
- `image_types[n]` (string)
- `image_descriptions[0]` (text)
- `image_descriptions[1]` (text)
- `image_descriptions[n]` (text)

**Ejemplo usando JavaScript (FormData):**
```javascript
const formData = new FormData();

// Datos básicos
formData.append('maintainable_type', 'device');
formData.append('maintainable_id', 1);
formData.append('maintenance_date', '2025-11-18');
formData.append('next_maintenance_date', '2026-02-18');
formData.append('maintenance_type', 'preventivo');
formData.append('description', 'Limpieza general y revisión');
formData.append('performed_tasks', 'Limpieza interna, cambio pasta térmica');
formData.append('technician', 'Juan Pérez');
formData.append('cost', 50.00);
formData.append('status', 'completado');

// Formato físico
formData.append('physical_format', formatoPDFFile);

// Imágenes
formData.append('images[0]', imagenAntesFile);
formData.append('images[1]', imagenDespuesFile);
formData.append('images[2]', imagenEquipoFile);

// Tipos de imágenes
formData.append('image_types[0]', 'antes');
formData.append('image_types[1]', 'despues');
formData.append('image_types[2]', 'equipo');

// Descripciones (opcional)
formData.append('image_descriptions[0]', 'Estado antes del mantenimiento');
formData.append('image_descriptions[1]', 'Estado después del mantenimiento');
formData.append('image_descriptions[2]', 'Equipo completo');

// Enviar
fetch('/api/maintenances', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token
  },
  body: formData
});
```

**Respuesta exitosa (201):**
```json
{
  "message": "Maintenance created successfully",
  "data": {
    "id": 1,
    "maintainable_type": "App\\Models\\Device",
    "maintainable_id": 1,
    "maintenance_date": "2025-11-18",
    ...
  }
}
```

---

#### 3. Ver Mantenimiento Específico
```
GET /api/maintenances/{id}
```

**Respuesta exitosa (200):**
```json
{
  "message": "Maintenance retrieved successfully",
  "data": {
    "id": 1,
    "maintainable_type": "App\\Models\\Device",
    ...
    "maintainable": {...},
    "images": [...],
    "created_by_user": {...},
    "updated_by_user": {...}
  }
}
```

---

#### 4. Actualizar Mantenimiento
```
PUT/PATCH /api/maintenances/{id}
```

**Content-Type:** `multipart/form-data`

**Todos los campos son opcionales:**
- `maintenance_date` (date)
- `next_maintenance_date` (date)
- `maintenance_type` (string)
- `description` (text)
- `performed_tasks` (text)
- `technician` (string)
- `cost` (numeric)
- `status` (string)
- `notes` (text)
- `physical_format` (file) - Reemplaza el archivo anterior

**Respuesta exitosa (200):**
```json
{
  "message": "Maintenance updated successfully",
  "data": {...}
}
```

---

#### 5. Eliminar Mantenimiento
```
DELETE /api/maintenances/{id}
```

**Nota:** Elimina automáticamente todas las imágenes y el formato físico asociados.

**Respuesta exitosa (200):**
```json
{
  "message": "Maintenance deleted successfully"
}
```

---

#### 6. Agregar Imagen a Mantenimiento
```
POST /api/maintenances/{id}/images
```

**Content-Type:** `multipart/form-data`

**Campos requeridos:**
- `file` (file) - JPG, JPEG, PNG - Máx: 10MB
- `type` (string) - Valores: equipo, antes, despues, formato

**Campos opcionales:**
- `description` (text)

**Respuesta exitosa (201):**
```json
{
  "message": "Image added successfully",
  "data": {
    "id": 5,
    "maintenance_id": 1,
    "image_path": "maintenances/images/xyz789.jpg",
    "image_type": "despues",
    "description": "Estado final",
    "order": 3
  }
}
```

---

#### 7. Eliminar Imagen de Mantenimiento
```
DELETE /api/maintenances/{maintenanceId}/images/{imageId}
```

**Respuesta exitosa (200):**
```json
{
  "message": "Image deleted successfully"
}
```

---

#### 8. Próximos Mantenimientos Programados
```
GET /api/upcoming-maintenances
```

**Parámetros de consulta:**
- `days` (int, default: 30) - Buscar mantenimientos en los próximos X días

**Respuesta exitosa (200):**
```json
{
  "message": "Upcoming maintenances retrieved successfully",
  "data": [
    {
      "id": 5,
      "next_maintenance_date": "2025-12-01",
      "maintainable": {...}
    }
  ]
}
```

---

### Historial de Cambios de Periféricos

#### 1. Listar Cambios
```
GET /api/peripheral-changes
```

**Parámetros de consulta:**
- `per_page` (int, default: 10)
- `search` (string) - Buscar en componente, técnico o proveedor
- `device_id` (int) - Filtrar por dispositivo
- `change_type` (string) - Filtrar por tipo: ram, hdd, ssd, teclado, mouse, monitor, impresora, escaner, otro

**Respuesta exitosa (200):**
```json
{
  "message": "Peripheral changes retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "device_id": 1,
        "change_date": "2025-11-18",
        "change_type": "ram",
        "component_name": "Kingston DDR4 16GB",
        "old_value": "8GB",
        "new_value": "16GB",
        "reason": "Mejora de rendimiento",
        "cost": "75.00",
        "supplier": "Tech Store",
        "technician": "Juan Pérez",
        "notes": null,
        "device": {...}
      }
    ],
    "total": 25
  }
}
```

---

#### 2. Crear Cambio de Periférico
```
POST /api/peripheral-changes
```

**Campos requeridos:**
- `device_id` (integer) - Debe existir en devices
- `change_date` (date) - Formato: YYYY-MM-DD
- `change_type` (string) - Valores: ram, hdd, ssd, teclado, mouse, monitor, impresora, escaner, otro
- `component_name` (string) - Nombre del componente

**Campos opcionales:**
- `old_value` (string)
- `new_value` (string)
- `reason` (text)
- `cost` (numeric) - Mínimo 0
- `supplier` (string)
- `technician` (string)
- `notes` (text)

**Funcionalidad Automática:**
- Si `change_type` es `ram` y se proporciona `new_value`, actualiza automáticamente el campo `ram` del dispositivo
- Si `change_type` es `hdd` o `ssd` y se proporciona `new_value`, actualiza automáticamente el campo `hdd` del dispositivo

**Ejemplo:**
```json
{
  "device_id": 1,
  "change_date": "2025-11-18",
  "change_type": "ram",
  "component_name": "Kingston DDR4 16GB 3200MHz",
  "old_value": "8GB",
  "new_value": "16GB",
  "reason": "Mejora de rendimiento para aplicaciones de diseño",
  "cost": 75.50,
  "supplier": "Tech Store S.A.",
  "technician": "Juan Pérez"
}
```

**Respuesta exitosa (201):**
```json
{
  "message": "Peripheral change created successfully",
  "data": {
    "id": 1,
    "device_id": 1,
    "change_date": "2025-11-18",
    ...
  }
}
```

---

#### 3. Ver Cambio Específico
```
GET /api/peripheral-changes/{id}
```

**Respuesta exitosa (200):**
```json
{
  "message": "Peripheral change retrieved successfully",
  "data": {...}
}
```

---

#### 4. Actualizar Cambio
```
PUT/PATCH /api/peripheral-changes/{id}
```

**Todos los campos son opcionales:**
- `change_date` (date)
- `change_type` (string)
- `component_name` (string)
- `old_value` (string)
- `new_value` (string)
- `reason` (text)
- `cost` (numeric)
- `supplier` (string)
- `technician` (string)
- `notes` (text)

**Respuesta exitosa (200):**
```json
{
  "message": "Peripheral change updated successfully",
  "data": {...}
}
```

---

#### 5. Eliminar Cambio
```
DELETE /api/peripheral-changes/{id}
```

**Respuesta exitosa (200):**
```json
{
  "message": "Peripheral change deleted successfully"
}
```

---

#### 6. Historial de Cambios por Dispositivo
```
GET /api/devices/{deviceId}/peripheral-changes
```

**Respuesta exitosa (200):**
```json
{
  "message": "Device change history retrieved successfully",
  "data": [
    {
      "id": 1,
      "change_date": "2025-11-18",
      "change_type": "ram",
      ...
    },
    {
      "id": 2,
      "change_date": "2025-10-15",
      "change_type": "hdd",
      ...
    }
  ]
}
```

---

#### 7. Estadísticas de Cambios
```
GET /api/peripheral-changes-stats
```

**Parámetros de consulta:**
- `start_date` (date, default: 6 meses atrás)
- `end_date` (date, default: hoy)

**Respuesta exitosa (200):**
```json
{
  "message": "Statistics retrieved successfully",
  "data": {
    "total_changes": 45,
    "total_cost": "3250.75",
    "changes_by_type": [
      {
        "change_type": "ram",
        "total": 15,
        "total_cost": "1125.50"
      },
      {
        "change_type": "hdd",
        "total": 12,
        "total_cost": "980.25"
      },
      {
        "change_type": "teclado",
        "total": 8,
        "total_cost": "320.00"
      }
    ]
  }
}
```

---

## Ejemplos de Uso

### Ejemplo 1: Crear Mantenimiento Completo con Imágenes

**Usando Postman/Thunder Client:**

1. Crear nueva request POST a `http://tu-dominio/api/maintenances`
2. En Headers:
   - `Authorization`: `Bearer tu_token_jwt`
3. En Body → form-data:

| Key | Type | Value |
|-----|------|-------|
| maintainable_type | Text | device |
| maintainable_id | Text | 1 |
| maintenance_date | Text | 2025-11-18 |
| next_maintenance_date | Text | 2026-02-18 |
| maintenance_type | Text | preventivo |
| description | Text | Limpieza general y actualización |
| performed_tasks | Text | Limpieza interna, cambio pasta térmica, actualización BIOS |
| technician | Text | Juan Pérez |
| cost | Text | 50 |
| status | Text | completado |
| physical_format | File | [Seleccionar PDF] |
| images[0] | File | [Seleccionar imagen 1] |
| images[1] | File | [Seleccionar imagen 2] |
| image_types[0] | Text | antes |
| image_types[1] | Text | despues |
| image_descriptions[0] | Text | Estado antes del mantenimiento |
| image_descriptions[1] | Text | Estado después del mantenimiento |

---

### Ejemplo 2: Registrar Cambio de RAM

```javascript
// JavaScript/Fetch
const response = await fetch('/api/peripheral-changes', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    device_id: 1,
    change_date: '2025-11-18',
    change_type: 'ram',
    component_name: 'Kingston Fury DDR4 16GB 3200MHz',
    old_value: '8GB DDR4 2666MHz',
    new_value: '16GB DDR4 3200MHz',
    reason: 'Mejora de rendimiento para aplicaciones de diseño gráfico',
    cost: 75.50,
    supplier: 'Tech Store S.A.',
    technician: 'Juan Pérez',
    notes: 'Se verificó compatibilidad con motherboard'
  })
});

const data = await response.json();
console.log(data);
// El campo 'ram' del dispositivo se actualizó automáticamente a '16GB DDR4 3200MHz'
```

---

### Ejemplo 3: Obtener Próximos Mantenimientos

```javascript
// Obtener mantenimientos programados para los próximos 15 días
const response = await fetch('/api/upcoming-maintenances?days=15', {
  headers: {
    'Authorization': 'Bearer ' + token
  }
});

const data = await response.json();
console.log(data.data); // Array de mantenimientos próximos
```

---

### Ejemplo 4: Agregar Imagen Adicional a Mantenimiento Existente

```javascript
const formData = new FormData();
formData.append('file', imagenFile);
formData.append('type', 'formato');
formData.append('description', 'Formato firmado escaneado');

const response = await fetch('/api/maintenances/5/images', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token
  },
  body: formData
});
```

---

## Características Especiales

### 1. Relaciones Polimórficas

El sistema de mantenimientos utiliza relaciones polimórficas, lo que significa que un mantenimiento puede aplicarse a:
- Dispositivos (Device)
- Impresoras (Printer)
- Escáneres (Scanner)

Esto evita duplicación de código y permite gestionar todos los mantenimientos desde una sola tabla.

**Ejemplo:**
```php
// Obtener todos los mantenimientos de un dispositivo
$device = Device::find(1);
$mantenimientos = $device->maintenances;

// Obtener todos los mantenimientos de una impresora
$printer = Printer::find(1);
$mantenimientos = $printer->maintenances;
```

---

### 2. Actualización Automática de Dispositivos

Cuando se registra un cambio de RAM, HDD o SSD, el sistema actualiza automáticamente los campos correspondientes en el dispositivo:

```php
// En PeripheralChangeHistoryController.php líneas 86-91
if ($validated['change_type'] === 'ram' && $validated['new_value']) {
    $device->update(['ram' => $validated['new_value']]);
} elseif (in_array($validated['change_type'], ['hdd', 'ssd']) && $validated['new_value']) {
    $device->update(['hdd' => $validated['new_value']]);
}
```

Esto mantiene sincronizada la información del dispositivo con su historial de cambios.

---

### 3. Gestión Automática de Archivos

El sistema gestiona automáticamente la eliminación de archivos cuando:
- Se elimina un mantenimiento (elimina todas las imágenes y el formato físico)
- Se elimina una imagen individual
- Se reemplaza el formato físico en una actualización

**Ubicación de archivos:**
- Formatos físicos: `storage/app/public/maintenances/formats/`
- Imágenes: `storage/app/public/maintenances/images/`

**Acceso público:**
Para acceder a los archivos desde el navegador, debes crear el enlace simbólico:
```bash
php artisan storage:link
```

Luego los archivos estarán disponibles en:
- `http://tu-dominio/storage/maintenances/formats/archivo.pdf`
- `http://tu-dominio/storage/maintenances/images/imagen.jpg`

---

### 4. Límites de Archivo Ajustados

Los límites de archivo han sido aumentados para permitir documentos más grandes:

| Tipo de Archivo | Límite | Formatos Permitidos |
|-----------------|--------|---------------------|
| Formato Físico | 50 MB | PDF, JPG, JPEG, PNG |
| Imágenes | 10 MB | JPG, JPEG, PNG |

**Configuración PHP requerida:**
El servidor Laragon ya tiene configurado:
- `upload_max_filesize`: 2G
- `post_max_size`: 2G
- `max_execution_time`: Sin límite

---

### 5. Ordenamiento de Imágenes

Las imágenes se ordenan automáticamente según el orden en que se suben (`order` field). Esto permite mantener una secuencia lógica (ej: antes → durante → después).

---

### 6. Validaciones Automáticas

El sistema valida automáticamente:
- ✅ Tipos de archivo permitidos
- ✅ Tamaño máximo de archivos
- ✅ Fechas válidas (next_maintenance_date debe ser posterior a maintenance_date)
- ✅ Valores numéricos no negativos para costos
- ✅ Estados válidos para mantenimientos
- ✅ Tipos válidos para cambios de periféricos
- ✅ Existencia de dispositivos antes de crear cambios

---

### 7. Auditoría de Cambios

Todos los registros incluyen:
- `created_by_user`: Usuario que creó el registro
- `updated_by_user`: Usuario que actualizó el registro
- `created_at`: Timestamp de creación
- `updated_at`: Timestamp de última actualización

Esto permite rastrear quién y cuándo se realizaron cambios en el sistema.

---

## Archivos Creados/Modificados

### Migraciones
- `database/migrations/2025_11_18_160716_create_maintenances_table.php`
- `database/migrations/2025_11_18_160802_create_peripheral_change_histories_table.php`
- `database/migrations/2025_11_18_160836_create_maintenance_images_table.php`

### Modelos
- `app/Models/Maintenance.php` (nuevo)
- `app/Models/MaintenanceImage.php` (nuevo)
- `app/Models/PeripheralChangeHistory.php` (nuevo)
- `app/Models/Device.php` (modificado - agregadas relaciones)
- `app/Models/Printer.php` (modificado - agregadas relaciones)
- `app/Models/Scanner.php` (modificado - agregadas relaciones)

### Controladores
- `app/Http/Controllers/MaintenanceController.php` (nuevo)
- `app/Http/Controllers/PeripheralChangeHistoryController.php` (nuevo)

### Rutas
- `routes/api.php` (modificado - agregadas rutas)

---

## Notas Importantes

1. **Autenticación:** Todas las rutas requieren un token JWT válido en el header `Authorization: Bearer {token}`

2. **Permisos:** Actualmente no hay sistema de permisos específico. Todos los usuarios autenticados pueden realizar todas las operaciones.

3. **Soft Deletes:** No se implementó soft deletes. Las eliminaciones son permanentes.

4. **Transacciones:** Todas las operaciones que involucran múltiples inserciones/actualizaciones usan transacciones de base de datos para mantener la integridad.

5. **CORS:** Asegúrate de tener configurado CORS correctamente en `config/cors.php` para permitir peticiones desde tu frontend.

6. **Storage Link:** Recuerda ejecutar `php artisan storage:link` para crear el enlace simbólico y poder acceder a los archivos públicamente.

---

## Soporte y Contacto

Para reportar problemas o sugerencias sobre el módulo de mantenimiento, contacta al equipo de desarrollo.

---

**Versión:** 1.0
**Fecha:** 18 de Noviembre, 2025
**Autor:** Sistema de Gestión de Equipos Hospitalarios
