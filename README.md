# ItSt Integracion con Tango

Modulo para [Prestashop](https://www.prestashop.com/es/1.7) que integra con [Axoft Tango](http://www.axoft.com/) mediante la API Rest de [ItStuff](https://itstuff.com.ar).
Permite sincronizar pedidos, listas de precios, clientes entre otras opciones entre [Prestashop](https://www.prestashop.com/es/1.7) y [Axoft Tango](http://www.axoft.com/)

# Instalación.

Clonar el repositorio, instalar las dependencias (para desarrollo) y comprimir en un archivo zip
```
npm install
```
```
npm run build
```

Instalar el archivo zip generado en la carpera pack en [Prestashop](https://www.prestashop.com)

## Configuración

Después de instalar el modulo, es necesario configurarlo. 

### Configuración General

La configuración general incluye parametros generales para el uso del moodulo y debe configurarse antes de poder configurar el resto de las opciones.

![](https://github.com/mescalitog/itstTangoIntegracion/blob/master/documents/assets/images/config_1.jpg?raw=true)

* **Modo Producción** - Determina si el modulo esta activado
* **WS Url** - Corresponde a la url donde el modulo va a encontrar la api
* **API-KEY** - Clave de autenticación de la API
* **Detener después de errores** - Algunos procesos automáticos como la sincronizacion de precios se detienen despues de encontrar la cantidad de errores definidos en este parámetro
* **Nivel de logs** - Es el nivel de logs que el modulo va a registrar en el sistema de logs de prestashop. [Ver Logs](#logs)
* **Grabar logs en archivo** - Opcionalmente el modulo puede generar logs detallados en un archivo de logs. No es recomendable activar esta opción en producción.

### Configuración de Transportes

Si se habilita la sincronizaciónd de transportes, cuando se sincronice un [pedido](#orders-settings) los costos de envío se agregaran al pedido como un producto.

![](https://github.com/mescalitog/itstTangoIntegracion/blob/master/documents/assets/images/config_2.jpg?raw=true)

* **Sincronizar costos de envio** - Cuando esta habilitado agregarán al pedido los costos de envío como un producto.
* **Producto para costo de envío** - Este es el código del producto que se usará para sincronizar los costos de envío. El producto debe existir en Tango al momento de la configuración.

#### Reglas de transportes

Las reglas de transporte relacionan un transporte en [Prestashop](https://www.prestashop.com) con un transporte en Tango. Es necesario agregar una regla de transporte por cada transporte definido en [Prestashop](https://www.prestashop.com)

### Configuración de Precios

Si esta habilitado, el modulo puede sincronizar periodicamente los precios de Tango mediante el uso de [cron jobs](#cron-jobs). 
> Para identificar los productos se debe incluir el **Código de Articulo** de Tango en el campo **Referencia** del producto o combinación en [Prestashop](https://www.prestashop.com)

![](https://github.com/mescalitog/itstTangoIntegracion/blob/master/documents/assets/images/config_3.jpg?raw=true)

* **¿Cómo Sincronizar Precios?** - Cuando la sinrconización esta habilitada, muestra el link al job que sincroniza los precios.
* **Sincronizar Precios** - Habilita la sincronización de precios
* **Sincronizar precios para productos** - Cuando esta habilitada sincroniza precios de productos.
* **Sincronizar precios para combinaciones** - Cuando esta habilitada sincroniza precios de combinaciones de productos.

#### Monedas y Listas de Precios

El modulo puede utilizar varias listas de precios para sincronizar los precios, verificandolas en el orden que se establecio al cargarlas. Una vez que un articulo es localizado en una lista de precios, ya no se siguen verificando las demñas.
Si la lista es en _moneda extrangera_ el modulo consultará la ultima cotizacion de Tango para la moneda de la lista y convertira los valores.

> Si se usan listas de precios en moneda extrangera es importante mantener las cotizaciones actualizadas en Tango

> [Ver como configurar cron-jobs](#cron-jobs). 

### <a name="products-settings"></a>Configuración de Productos

Si esta habilitado, el modulo puede sincronizar periodicamente los productos de Tango habilitados para ventas mediante el uso de [cron jobs](#cron-jobs). 
> El codigo del producto sincronizado se incluye en el cambio *Referencia* 
> Si el **Código de Articulo** del producto sincronizado no tiene un producto con el mismo codigo en el campo **Referencia** del producto o combinación en [Prestashop](https://www.prestashop.com) el producto se crea en la categoria _NUEVOS IMPORTADOS TANGO_


* **¿Cómo Sincronizar Productos?** - Cuando la sinrconización esta habilitada, muestra el link al job que sincroniza los productos.
* **Sincronizar Productos** - Habilita la sincronización de productos

> [Ver como configurar cron-jobs](#cron-jobs). 

### <a name="orders-settings"></a>Configuración de Pedidos

El módulo sincronizará con tango todas las ordenes que esten en un estado para el que este habilitado "Consider the associated order as validated."

* **Sincronizar pedidos** - Habilita la sincronizacion de pedidos. Si la sincronizacion no esta habilitada y un pedido alcanza un estado válido, se generará un log. [Ver Logs](#logs)
* **Incluir impuestos en los productos** - Determina si los productos se sincronizarán con o sin impuestos. 
* **Talonario de pedidos** - Seleccionar desde la lista desplegable el talonario de tango que se utilizará para generar los pedidos.

### Configuración de Clientes

Cuando esta habilitado, se sincronizan las cuentas de usuario de prestashop con contactos de clientes en tango.
Tambien se crean las direcciones que existan en tango.

La sincronización tambien puede hacerse manualmente, desde el modulo de administración de clientes en Prestashop.

![](https://github.com/mescalitog/itstTangoIntegracion/blob/master/documents/assets/images/config_4.jpg?raw=true)


## <a name="logs"></a>Logs


## <a name="cron-jobs"></a>Cron Jobs


# Changelog

## [1.3.3] - 2019-06-01
### Added
- changelog to README.md

### Changed
- Corrección del calculo de formulas en creacion de pedidos. Se excluyen productos con formulas.

### Removed
- none

## [1.3.5] - 2019-06-21
### Added
- porcentaje de descuento al crear pedido
- Fechas en formato ISO 8601

### Changed
- none

### Removed
- none

## [1.3.6] - 2019-06-23
### Added
- none

### Changed
- parametro en configuracion de orden para sincronizar con o sin impuestos. [#1081](https://itstuff.com.ar/redmine/issues/1081)

### Removed
- none

## [1.3.7] - 2019-06-23
### Added
- none

### Changed
- Fix. [#1098](https://itstuff.com.ar/redmine/issues/1098)

### Removed
- none

## [1.4.2] - 2019-07-12
### Added
- Sincronizacion de Datos de Clientes

### Changed
- Requiere minimo prestashop 1.7.5

### Removed
- none