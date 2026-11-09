# EmpresiaPayPal — puente de conciliación para Dolibarr

**Estado:** módulo PHP listo para revisar e instalar en una instancia Dolibarr de pruebas; no se ha instalado ni probado sobre una base de datos ERP activa. No se hacen asientos ni se marcan facturas pagadas automáticamente.

## ¿Qué hace?
- Registra eventos `paypal.payment.completed` que **ya fueron verificados** por el webhook del CRM.
- Solo acepta mensajes enviados por nuestro CRM mediante firma HMAC-SHA256 y ventana de 5 minutos.
- Bloquea reutilización de `paypal_capture_id` con distinta información; los reintentos idénticos son seguros.
- Guarda `crm_tenant_id`, `crm_order_id`, `paypal_order_id`, moneda, importe en centavos y JSON original para conciliar.
- Estado inicial: `pending_mapping`. **No inventa IDs de factura Dolibarr, no genera ingresos contables y no modifica el motor PayPal original.**

## Instalación en staging
1. Arrancar primero Dolibarr con su base de datos y HTTPS. Este repositorio contiene el código fuente, no una instancia operativa.
2. Verificar compatibilidad con la versión instalada y activar el módulo **EmpresiaPayPal** desde Configuración > Módulos. Su descriptor `core/modules/modEmpresiaPayPal.class.php` crea la tabla `<prefix>empresia_paypal_inbox` sin borrarla al desactivar.
3. Configurar variables de entorno **solo en el servidor PHP**:
   - `EMPRESIA_PAYPAL_BRIDGE_SECRET`: secreto HMAC de al menos 32 caracteres compartido con CRM.
   - `EMPRESIA_PAYPAL_TENANT_ID`: UUID de la única empresa autorizada.
   - `EMPRESIA_PAYPAL_ENTITY`: ID numérico de entidad Dolibarr autorizada.
4. En el CRM configurar `EMPRESIA_PAYPAL_BRIDGE_URL=https://tu-erp.example/custom/empresiapaypal/ingest.php` y el mismo secreto.
5. Ejecutar el relay del CRM bajo autenticación de máquina, no con un usuario público. El destino devolverá `{"accepted":true,"duplicate":false}` para un evento nuevo, y `duplicate:true` para reintentos idénticos.
6. Inspeccionar manualmente filas `pending_mapping` y construir un mapeo validado de `crm_order_id` a pedido/factura Dolibarr. Confirmar moneda, entidad, importe y estado antes de contabilizar. Sin ese paso, **no hay pago contable en Dolibarr**.

## PayPal nativo de Dolibarr
Dolibarr ya contiene el módulo `paypal` y las opciones de configuración `PAYPAL_API_USER`, `PAYPAL_API_PASSWORD`, `PAYPAL_API_SIGNATURE`. Estas opciones pertenecen al módulo nativo/API Classic; no son intercambiables automáticamente con `PAYPAL_CLIENT_ID` y `PAYPAL_CLIENT_SECRET` del REST Orders v2 que usa nuestro CRM.

La estrategia es **no clonar otro PayPal completo dentro del ERP**, sino extender Dolibarr sin modificar el código original y conciliar las capturas del CRM. En una fase posterior podemos habilitar la operativa nativa de Dolibarr directamente para facturas propias del ERP, evaluando su soporte actual de PayPal y los permisos del comerciante.

## Pruebas obligatorias
- `php -l dolibarr/htdocs/custom/empresiapaypal/ingest.php`
- `php -l dolibarr/htdocs/custom/empresiapaypal/core/modules/modEmpresiaPayPal.class.php`
- POST con firma incorrecta → 401.
- POST de otra empresa → 422.
- POST con importe o moneda inválidos → 422.
- Primer POST correcto → una fila en inbox.
- Repetición idéntica → misma fila sin duplicado.
- Captura repetida con importe alterado → 409.
- Módulo deshabilitado → 503.
- Entorno sin Dolibarr instalado → imposible probar endpoints reales, no marcar éxito.

El endpoint `ingest.php` **no es** el webhook PayPal público: PayPal debe enviar el webhook a `/api/paypal/webhook` del CRM. Los dos secretos de firma son independientes.
