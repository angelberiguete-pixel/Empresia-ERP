# Empresia Audit — módulo experimental de Dolibarr

**Estado:** primera implementación de código, pendiente de instalación real, pruebas de base de datos, autorización y despliegue. No apto todavía para información de clientes reales.

## Funciones implementadas

- Formulario interno autenticado, con permisos independientes de lectura y escritura.
- Cinco dimensiones puntuadas de 0 a 5: estrategia, ventas, procesos, tecnología y equipo.
- Índice de madurez = 4 × suma de los cinco valores (0–100).
- Origen comercial (directo, Instagram, Facebook, Google, referido o visita), campaña opcional, almacenamiento SQL.
- Total de diagnósticos, media de madurez, últimos 50 registros y agregación por origen.
- Consultas de lectura filtradas por `entity` de Dolibarr; salida escapada y token CSRF estándar.

## Arquitectura

`core/modules/modEmpresiaAudit.class.php`: descriptor del módulo y derechos.
`sql/llx_empresiaaudit_assessment.sql`: tabla MySQL/MariaDB instalada al activar el módulo.
`index.php`: formulario, puntuación, persistencia y métricas.

Está en `dolibarr/htdocs/custom/empresiaaudit` para conservar intacto el código upstream.

## Prerrequisitos para pruebas integradas

1. Confirmar que el ID provisional de módulo `590123` no colisiona con ningún otro módulo.
2. Tener Dolibarr real ejecutándose con MySQL/MariaDB exclusivo y persistente.
3. Crear una imagen propia que incluya este módulo en el directorio `custom`. La imagen pública oficial usada en `Dockerfile.vercel` **no** incluye los archivos agregados a GitHub.
4. Activar el módulo en el panel de administración y verificar su tabla.
5. Asignar derechos a un usuario de prueba; comprobar que usuarios sin derechos no acceden.
6. Probar POST CSRF, validación, separación entre entidades, consultas SQL, persistencia al reiniciar y restauración de respaldo.

## Fuera de alcance por ahora

No existe todavía conexión con oportunidades o cotizaciones de Dolibarr, generación de auditorías PDF, seguimiento automático UTM desde anuncios, IA conectada, automatizaciones ni panel financiero de conversiones. No anunciar esas prestaciones como listas.

Licencia del módulo: GPL-3.0-or-later.
