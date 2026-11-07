# Empresia ERP

ERP independiente basado en Odoo Community, con personalizaciones propias para empresas de República Dominicana.

## Estado

**Preparación inicial.** El código de Odoo aún no se ha importado, instalado ni desplegado. No usar en producción.

## Origen

- Upstream: https://github.com/odoo/odoo
- Versión objetivo inicial: rama estable 19.0 (validar compatibilidad y dependencias antes de importar).
- Mantener los avisos de copyright, la licencia original y las licencias individuales de los módulos.

## Objetivo de primera fase

1. Importar el código completo de Odoo Community preservando su licencia.
2. Preparar PostgreSQL y entorno de ejecución reproducible.
3. Arrancar Odoo y verificar autenticación y módulos base.
4. Incorporar módulos personalizados de Empresia en directorio independiente.
5. Añadir pruebas de aislamiento entre empresas, permisos y auditoría.
6. Preparar despliegue de staging en un servidor compatible con procesos Python persistentes.

## Nota de seguridad

No incluir credenciales, datos reales de clientes ni secretos en Git. El repositorio es actualmente público; revisar visibilidad antes de incorporar desarrollos privados.
