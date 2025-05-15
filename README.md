# MTC-Assistance
Proyecto final del curso de construccion de aplicaciones web de cuarto semestre

## Funcionalidades Principales

### 1. Gestión de Usuarios
- Registro de nuevos usuarios
- Inicio de sesión con autenticación segura
- Gestión de perfiles de usuario
- Control de sesiones

### 2. Gestión de Organizaciones
- Creación de organizaciones
- Asignación de códigos únicos para unirse
- Edición de información de la organización
- Eliminación de organizaciones
- Unirse a organizaciones existentes mediante código

### 3. Gestión de Áreas
- Creación de áreas dentro de organizaciones
- Asignación de responsables
- Edición y eliminación de áreas
- Vista detallada de cada área

### 4. Gestión de Asistentes
- Registro de asistentes por área
- Información detallada de cada asistente
- Edición y eliminación de asistentes
- Visualización de lista de asistentes por área

### 5. Control de Asistencia
- Registro de asistencia por fecha
- Múltiples estados de asistencia (presente, ausente, justificado)
- Vista de asistencia diaria
- Historial de asistencia
- Estadísticas de asistencia

### 6. Reportes
- Historial completo de asistencia
- Exportación de datos
- Estadísticas de asistencia por área y organización

## Tecnologías Utilizadas
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla JS)
- **Backend**: PHP 8.x
- **Base de datos**: MySQL
- **Servidor**: Apache
- **Autenticación**: Sistema propio con sesiones PHP
- **UI/UX**: Diseño responsivo para dispositivos móviles y escritorio

## Instalación y Configuración

1. Clonar el repositorio en el directorio de su servidor web:
2. Configurar la base de datos:
- Crear una base de datos MySQL
- Importar el archivo `database/schema.sql`

3. Configurar la conexión a la base de datos:
- Editar el archivo `config/database.php` con las credenciales correspondientes

4. Acceder a la aplicación:
- Navegar a `http://su-servidor/MTC-Assistance/index.html`

5. Acceso inicial:
- Crear una cuenta de usuario
- Iniciar sesión con las credenciales creadas

## Utilidad del Proyecto

MTC-Assistance está diseñado para:

- **Instituciones educativas**: Control de asistencia de estudiantes en diferentes cursos o clases
- **Empresas y organizaciones**: Seguimiento de asistencia de empleados en diferentes departamentos
- **Eventos y conferencias**: Registro de participantes en diferentes sesiones
- **Iglesias y organizaciones religiosas**: Control de asistencia en diferentes ministerios o áreas
- **Clubes y asociaciones**: Seguimiento de participación de miembros en distintas actividades

El sistema elimina la necesidad de llevar registros manuales de asistencia, reduciendo errores y mejorando la eficiencia administrativa. Además, proporciona datos históricos y estadísticas que permiten tomar decisiones informadas sobre la participación y asistencia en diferentes áreas.

## Contribución

Si deseas contribuir a este proyecto, por favor:
1. Haz un fork del repositorio
2. Crea una rama para tu función: `git checkout -b nueva-funcion`
3. Realiza tus cambios y haz commit: `git commit -m 'Añadir nueva función'`
4. Sube tus cambios: `git push origin nueva-funcion`
5. Envía un Pull Request

## Licencia

Este proyecto está bajo la Licencia MIT - consulta el archivo LICENSE para más detalles.

## Desarrolladores
- [Tu Nombre]
- [Otros colaboradores]

---

Desarrollado como proyecto final del curso de Construcción de Aplicaciones Web - Cuarto Semestre.
