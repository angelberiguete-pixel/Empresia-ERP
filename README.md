# Empresia ERP

Plataforma empresarial en desarrollo. El código fuente de **Dolibarr ERP & CRM** fue importado en `dolibarr/` (commit `1a9e127`), conservando su licencia y avisos originales.

## Estado real

- ✅ Fuentes upstream de Dolibarr importadas en `dolibarr/`.
- ✅ Configuración inicial de Docker Compose (rama de staging).
- ⏳ Dolibarr aún no ha sido probado en ejecución en un servidor.
- ⏳ Sin despliegue público ni pruebas end-to-end.
- ⏳ La identidad Empresia, diagnósticos, IA e integraciones aún requieren desarrollo.

## Instalación para pruebas

Ver [docs/INSTALLATION.md](docs/INSTALLATION.md). El compose inicial usa la **imagen oficial** de Dolibarr (no compila la carpeta fuente importada) y MariaDB con volúmenes persistentes, para verificar el funcionamiento base antes de personalizarlo.

## Arquitectura prevista

Dolibarr proveerá el motor ERP. Los módulos propios de Empresia se construirán mediante puntos de extensión y servicios separados. No se pretende mezclar código de Odoo con el de Dolibarr.

## Seguridad y licencias

No subas secretos a Git; revisa las licencias GPL del código y de los módulos antes de redistribuirlos. El entorno de prueba no debe usar datos reales de clientes hasta comprobar autenticación, permisos y aislamiento. No asumir que la facturación estándar cumple los requisitos fiscales electrónicos de República Dominicana sin implementación y validación específicas.
