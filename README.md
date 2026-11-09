# Empresia · Motor ERP (Dolibarr)

Este repositorio contiene el **motor ERP que se integrará como módulo de la plataforma Empresia**, cuya aplicación principal está en [angelberiguete-pixel/crm](https://github.com/angelberiguete-pixel/crm).

**Empresia es la única marca comercial.** CRM, ARM, ERP, facturación e IA forman una plataforma multiempresa; «Empresia ERP» no se ofrecerá como marca o plataforma separada.

## Estado real

- ✅ Código fuente upstream de Dolibarr ERP & CRM importado en `dolibarr/` (commit `1a9e127`), con su licencia y avisos originales conservados.
- ✅ Configuración inicial Docker Compose / MariaDB con almacenamiento persistente para futuras pruebas.
- ⏳ No se ha demostrado un despliegue público operativo del motor ERP ni pruebas end-to-end.
- ⏳ Aún NO hay integración CRM/ARM ↔ Dolibarr, sincronización fiable de datos, SSO ni aislamiento multiempresa verificado.
- ⏳ No se ha validado facturación electrónica DGII.

## Integración prevista con Empresia

1. La **identidad y acceso de clientes** permanecen bajo el CRM/ARM Empresia (Next.js, Supabase Auth y RLS).
2. Dolibarr actúa como motor ERP conectado mediante un servicio de integración autenticado: productos, facturas, compras, inventario y contabilidad según módulos realmente habilitados.
3. Cada empresa recibe una configuración ERP aislada; nunca se compartirán credenciales, documentos o registros de facturación entre tenants. El modelo de aislamiento (instancia individual o controles fuertes por cliente) debe decidirse antes de producción.
4. Los movimientos se sincronizan con identificadores estables, auditoría e idempotencia; errores o conflictos requieren revisión y no generan cobros o facturas duplicados.
5. La experiencia visible del cliente será **Empresia > ERP**, no una página o marca comercial adicional. Dolibarr podrá ser reconocido en avisos técnicos/licencia cuando corresponda.
6. La capa de IA del ARM no tendrá autorización implícita para emitir facturas, ejecutar cobros ni modificar contabilidad.

## Instalación de prueba

Ver [docs/INSTALLATION.md](docs/INSTALLATION.md). El Compose inicial usa la imagen Dolibarr oficial (no compila automáticamente las fuentes importadas) y MariaDB con volúmenes persistentes. No crear datos de clientes reales mientras falten pruebas de seguridad.

## Seguridad, licencias y obligaciones

Revisar GPL y licencias de módulos, persistencia, TLS, backups, autenticación y permisos antes de distribuir. No asumir que la facturación estándar de Dolibarr es equivalente a los e-CF de DGII en República Dominicana. No cambiar credenciales ni bases históricas como parte del cambio de marca.

## Identidad

- Marca comercial: **Empresia**
- Instagram: **@empresia.rd** (usuario seleccionado por el propietario)
- Registro de marca/nombre comercial en ONAPI: aún pendiente de confirmación.
