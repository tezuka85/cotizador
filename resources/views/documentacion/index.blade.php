<!Doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Document</title>
    <link rel="stylesheet" href="{!! asset('css/app.css') !!}">
    <link rel="stylesheet" href="{!! asset('assets/css/styles.css') !!}">
    <link rel="stylesheet" href="{!! asset('assets/prism/prism.css') !!}">
</head>
<style>
    .header{
        position: fixed;
        height: 70px;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 20;
        background: #FFF;
    }

    .body{
        position: relative;
        margin-left: 180px;
    }

    .content-body{
        margin-top: 50px;
    }
    .sidebar{
        padding-top: 30px;
        margin-top:70px;
        padding-bottom: 200px;
        width: 230px;
        position: fixed;
        left: 0;
        top: 0;
        bottom: 0;
        box-shadow: 1px 0 0 #F1F3F5;
        background: #F9FBFD;
        overflow-y: scroll;
    }
    .doc-sub-menu{
        margin-left: 30px;
    }
</style>
<body>
    <div class="doc-wrapper">
        <div class="container">
        <div id="doc-header" class="doc-header text-center shadow-sm p-3 mb-2 header" >
            <div class="row">
                <div class="col-lg-2">
                    <img src="/images/logo-t1.png" style="height: 40px">
                </div>
                <div class="col-lg-10">
                    <h1 class="doc-title"><i class="icon fa fa-paper-plane"></i> T1 Envíos API </h1>
                </div>
            </div>
        </div>
        <div class="body row content-body">
            <div class="doc-content col-md-12 col-12 order-1 content-body" >
                <div class="content-inner">
                    <!--Claro Envios Api-->
                    <section id="claro-envios-api" class="doc-section">
                        <div class="section-block">
                            <p>API para cotización, generación de guías, rastreo y generación de recolección de paquetes.</p>
                        </div>
                    </section>
                    <!--Headers-->
                    <section id="headers" class="doc-section">
                        <h3 class="section-title">Headers</h3>
                        <div class="section-block">
                            <p>Todas las peticiones deben llevar los siguientes Headers:</p>
                            <div>
                                <table class="table table-sm table-condensed table-bordered table-striped">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th class="text-center">Key</th>
                                            <th class="text-center">Valor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-center">Content-Type</td>
                                            <td class="text-center">application/json</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center">Accept</td>
                                            <td class="text-center">application/json</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center">Authorization</td>
                                            <td class="text-center">Bearer token Proporcionado</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                    <hr>
                    <!--Tarificador rate -->
                    <section id="tarificador-rate" class="doc-section">
                        <div>
                            <h3 class="section-title">Generar Cotización</h3>
                            <p>Método para la generación de la cotización.</p>
                            <ul class="bg-light shadow-sm p-3 mb-2 rounded">
                                <li><b>Url: {!! route('tarificador.cotizarMensajerias') !!}</b></li>
                                <li><b>Método: POST</b></li>
                            </ul>
                            <b>Datos de Json</b>
                            <div class="table-responsive-sm">
                            <table class="table table-sm table-condensed">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Atributos</th>
                                        <th>Tipo</th>
                                        <th>Descripción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th scope="row">codigo_postal_origen</th>
                                        <td>required</td>
                                        <td>Código postal de la dirección origen</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">codigo_postal_destino</th>
                                        <td>required</td>
                                        <td>Código postal de la dirección destino</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">peso</th>
                                        <td>required, integer</td>
                                        <td>Peso de paquete en kilogramos</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">largo</th>
                                        <td>required, integer</td>
                                        <td>Largo de paquete en centímetros</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">ancho</th>
                                        <td>required, integer</td>
                                        <td>Ancho de paquete en centímetros</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">alto</th>
                                        <td>required, integer</td>
                                        <td>Alto de paquete en centímetros</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">dias_embarque</th>
                                        <td>required, integer</td>
                                        <td>Días de embarque</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">seguro</th>
                                        <td>required,boolean</td>
                                        <td>Determina si el paquete tiene seguro. True para asegurar paquete, False para caso contrario</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">valor_paquete</th>
                                        <td>Es requerido si el campo "seguro" es true</td>
                                        <td>Valor de factura del paquete</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">tipo_paquete</th>
                                        <td>required, integer entre 1 y 2</td>
                                        <td>Indica si es paquete o sobre, 1 para sobre y 2 para paquete</td>
                                    </tr>
                                </tbody>
                            </table>
                            </div>
                        </div>
                        <div id="tarificador-rate-request"  class="section-block">
                            <h4 class="block-title">Request</h4>
                            <p>Json de datos para armar la petición.</p>
                            <div class="code-block">
                                <h6>Estructura de Request:</h6>
                                <pre class="json-display">
                                    {
                                      "codigo_postal_origen":"55770",
                                      "codigo_postal_destino":"99993",
                                      "peso":10,
                                      "largo":65,
                                      "ancho":20,
                                      "alto":30,
                                      "dias_embarque":1,
                                      "seguro":true,
                                      "valor_paquete":1200
                                      "tipo_paquete": 2
                                    }
                                </pre>
                            </div>
                        </div>
                        <div id="tarificador-rate-response"  class="section-block">
                            <h4 class="block-title">Response</h4>
                            <p>Respuesta de petición de cotización JSON.</p>
                            <div>
                                <b>Composición de JSON response.</b>
                                <p>El campo "data": contiene un arreglo de las cotizaciones de las mensajerías configuradas.</p>
                                <p>Cada elemento contiene la información de cada mensajería y el elemento cotización contiene lo siguiente: </p>
                                <ul>
                                    <li><b>status: </b> ok o error</li>
                                    <li><b>message: </b> Mensaje arrojado cuando se obtiene la respuesta de la cotización.</li>
                                    <li><b>servicios: </b> Cotización dependiento de los tipos de servicios.
                                        <ul>
                                            <li><b>tipo_servicio_descripcion</b></li>
                                            <li><b>tipo_servicio</b></li>
                                            <li><b>fecha_mensajeria_entrega</b></li>
                                            <li><b>fecha_claro_entrega</b></li>
                                            <li><b>dias_entrega</b></li>
                                            <li><b>costo_cliente</b></li>
                                            <li><b>costo_total</b></li>
                                            <li><b>porcentaje_negociacion</b></li>
                                            <li><b>costo_negociacion</b></li>
                                            <li><b>porcentaje_seguro</b></li>
                                            <li><b>valor_paquete</b></li>
                                            <li><b>costo_mensajeria</b></li>
                                            <li><b>moneda</b></li>
                                            <li><b>peso</b></li>
                                            <li><b>peso_unidades</b></li>
                                            <li><b>largo</b></li>
                                            <li><b>ancho</b></li>
                                            <li><b>alto</b></li>
                                            <li><b>codigo_servicio</b></li>
                                            <li><b>token</b> Token de cotización necesario para generar la guia del paquete.</li>
                                        </ul>
                                    </li>
                                    <li><b>location: </b></li>
                                </ul>
                            </div>
                            <div class="code-block">
                                <h6>Estructura de Response:</h6>
                                <pre class="json-display">
                                    {
                                        "status": "ok",
                                        "message": "Búsqueda exitosa!",
                                        "data": [
                                            {
                                                "id": 1,
                                                "clave": "DHL",
                                                "descripcion": "DHL",
                                                "comercio": 149,
                                                "seguro": true,
                                                "cotizacion": {
                                                    "status": "ok",
                                                    "message": "Búsqueda exitosa",
                                                    "servicios": {
                                                        "ECONOMY SELECT DOMESTIC": {
                                                            "tipo_servicio_descripcion": "Economico",
                                                            "tipo_servicio": "ECONOMY SELECT DOMESTIC",
                                                            "fecha_mensajeria_entrega": "2019-08-08",
                                                            "fecha_claro_entrega": "2019-08-09",
                                                            "dias_entrega": 9,
                                                            "costo_cliente": "87.000",
                                                            "costo_total": 99.78,
                                                            "porcentaje_negociacion": 0,
                                                            "costo_negociacion": "0.78",
                                                            "porcentaje_seguro": "1.00",
                                                            "valor_paquete": "1200",
                                                            "costo_mensajeria": "87.000",
                                                            "moneda": "MXN",
                                                            "peso": "10",
                                                            "peso_unidades": "KG",
                                                            "largo": "65",
                                                            "ancho": "20",
                                                            "alto": "30",
                                                            "codigo_servicio": "G",
                                                            "token": "WP9ESYVvmNtSQi6ztXNxwagCmFP8Zmf9PU2IYXycZSjxXOUlJCI0fQdIoiMR"
                                                        },
                                                        "EXPRESS DOMESTIC": {
                                                            "tipo_servicio_descripcion": "Dia Siguiente",
                                                            "tipo_servicio": "EXPRESS DOMESTIC",
                                                            "fecha_mensajeria_entrega": "2019-08-08",
                                                            "fecha_claro_entrega": "2019-08-09",
                                                            "dias_entrega": 9,
                                                            "costo_cliente": "87.000",
                                                            "costo_total": 99.78,
                                                            "porcentaje_negociacion": 0,
                                                            "costo_negociacion": "0.78",
                                                            "porcentaje_seguro": "1.00",
                                                            "valor_paquete": "1200",
                                                            "costo_mensajeria": "87.000",
                                                            "moneda": "MXN",
                                                            "peso": "10",
                                                            "peso_unidades": "KG",
                                                            "largo": "65",
                                                            "ancho": "20",
                                                            "alto": "30",
                                                            "codigo_servicio": "N",
                                                            "token": "tVj0uLzHpBq91RSIZkNns2xhC6iv8aRXMTqKYRm42Md46IuYeEMYcs8Dqwhd"
                                                        }
                                                    },
                                                    "location": "https://xmlpitest-ea.dhl.com/XMLShippingServlet?isUTF8Support=true"
                                                }
                                            },
                                            {
                                                "id": 2,
                                                "clave": "FEDEX",
                                                "descripcion": "FEDEX",
                                                "comercio": 149,
                                                "seguro": true,
                                                "cotizacion": {
                                                    "status": "ok",
                                                    "message": "Búsqueda exitosa",
                                                    "servicios": {
                                                        "FEDEX_EXPRESS_SAVER": {
                                                            "tipo_servicio_descripcion": "Economico",
                                                            "tipo_servicio": "FEDEX_EXPRESS_SAVER",
                                                            "fecha_mensajeria_entrega": "2019-08-02",
                                                            "fecha_claro_entrega": "2019-08-03",
                                                            "dias_entrega": 4,
                                                            "costo_cliente": "22.11",
                                                            "costo_claro": "22.11",
                                                            "costo_total": 58.3311,
                                                            "porcentaje_negociacion": 1,
                                                            "costo_negociacion": "0.00",
                                                            "porcentaje_seguro": "3.00",
                                                            "valor_paquete": "1200",
                                                            "costo_mensajeria": "22.11",
                                                            "moneda": "MXN",
                                                            "peso": "10",
                                                            "peso_unidades": "KG",
                                                            "largo": "65",
                                                            "ancho": "20",
                                                            "alto": "30",
                                                            "codigo_servicio": "FEDEX_EXPRESS_SAVER",
                                                            "token": "a1aC2blA9gnDMNb4xwOrH1EvMVzAY1dRAzr3U0dzSqVGBxBvL4XyvGoXwZjo"
                                                        }
                                                    },
                                                    "location": "https://wsbeta.fedex.com:443/web-services/"
                                                }
                                            }
                                        ]
                                    }
                                </pre>
                            </div>
                        </div>
                        <div id="tarificador-rate-error"  class="section-block">
                            <h4 class="block-title">Error</h4>
                            <p>Posibles errores por falta de algún campo requerido.</p>
                            <pre class="json-display">
                                {
                                    "message": "Datos Incorrectos.",
                                    "errors": {
                                        "codigo_postal_origen": [
                                            "El campo codigo postal origen es obligatorio."
                                        ],
                                        "codigo_postal_destino": [
                                            "El campo codigo postal destino es obligatorio."
                                        ],
                                        "peso": [
                                            "El campo peso es obligatorio."
                                        ],
                                        "largo": [
                                            "El campo largo es obligatorio."
                                        ],
                                        "ancho": [
                                            "El campo ancho es obligatorio."
                                        ],
                                        "alto": [
                                            "El campo alto es obligatorio."
                                        ],
                                        "dias_embarque": [
                                            "El campo dias embarque es obligatorio."
                                        ],
                                        "seguro": [
                                            "El campo seguro es obligatorio."
                                        ]
                                    }
                                }

                            </pre>
                        </div>
                    </section>
                    <hr>
                    <!--Tarificador generacion de guia -->
                    <section id="tarificador-generacion-guia" class="doc-section">
                        <div>
                            <h3 class="section-title">Generación de Guía</h3>
                            <p>Método para la generación de una guía a partir de una cotización.</p>
                            <ul class="bg-light shadow-sm p-3 mb-2 rounded">
                                <li><b>Url: {!! route('tarificador.generarGuiaMensajeria', '') !!}/{cotizacion_id}</b></li>
                                <li><b>Método: POST</b></li>
                            </ul>
                            <b>Datos de Json</b>
                            <div class="table-responsive-sm">
                                <table class="table table-sm table-condensed">
                                    <thead class="thead-dark">
                                    <tr>
                                        <th>Atributos</th>
                                        <th>Tipo</th>
                                        <th>Descripción</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <th scope="row">nombre_origen</th>
                                        <td>required</td>
                                        <td>Nombre de remitente</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">apellidos_origen</th>
                                        <td>required</td>
                                        <td>Código postal de la dirección remitente</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">email_origen</th>
                                        <td>required, email</td>
                                        <td>Email de remitente</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">calle_origen</th>
                                        <td>required</td>
                                        <td>Calle de remitente</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">numero_origen</th>
                                        <td>required</td>
                                        <td>Número de remitente</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">colonia_origen</th>
                                        <td>required</td>
                                        <td>Colonia remitente</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">telefono_origen</th>
                                        <td>required, min:8</td>
                                        <td>Teléfono remitente</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">estado_origen</th>
                                        <td>required,boolean</td>
                                        <td>Estado remitente</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">municipio_origen</th>
                                        <td>required</td>
                                        <td>Municipio remitente</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">referencias_origen</th>
                                        <td>required</td>
                                        <td>Referencias de remitente</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">nombre_destino</th>
                                        <td>required</td>
                                        <td>Nombre de destinatario</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">apellidos_destino</th>
                                        <td>required</td>
                                        <td>Apellidos de destinatario</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">email_destino</th>
                                        <td>required, email</td>
                                        <td>Email de destinatario</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">calle_destino</th>
                                        <td>required</td>
                                        <td>Calle de destinatario</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">numero_destino</th>
                                        <td>required</td>
                                        <td>Número de destinatario</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">colonia_destino</th>
                                        <td>required</td>
                                        <td>Colonia de destinatario</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">telefono_destino</th>
                                        <td>requerid, min:8</td>
                                        <td>Teléfono de destinatario</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">estado_destino</th>
                                        <td>required</td>
                                        <td>Estado de destinatario</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">municipio_destino</th>
                                        <td>required</td>
                                        <td>Municipio de destinatario</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">referencias_destino</th>
                                        <td>required</td>
                                        <td>Referencias de destinatario</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">generar_recoleccion</th>
                                        <td>Opcional, boolean</td>
                                        <td>True si desea generar recolección cuando se genera la guía </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">contenido</th>
                                        <td>required,string, max:25</td>
                                        <td>Breve descripción del contenido del paquete</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="tarificador-generacion-guia-request"  class="section-block">
                            <h4 class="block-title">Request</h4>
                            <p>Json de datos para armar la petición.</p>
                            <div class="code-block">
                                <h6>Estructura de Request:</h6>
                                <pre class="json-display">
                                    {
                                      "nombre_origen":"David",
                                      "apellidos_origen":"Pérez",
                                      "email_origen":"david_perez@correo.com",
                                      "calle_origen":"Lago mayor",
                                      "numero_origen":"20",
                                      "colonia_origen":"Flores",
                                      "telefono_origen":"81345345",
                                      "estado_origen":"Estado de México",
                                      "municipio_origen":"Coacalco",
                                      "referencias_origen":"Referencia remitente",
                                      "nombre_destino":"Jesus",
                                      "apellidos_destino":"Luna",
                                      "email_destino":"jesus@correo.com",
                                      "calle_destino":"Duraznos",
                                      "numero_destino":"20",
                                      "colonia_destino":"Granjas",
                                      "telefono_destino":"67865432",
                                      "estado_destino":"Sonora",
                                      "municipio_destino":"Sonora",
                                      "referencias_destino":"Referencia destino",
                                      "generar_recoleccion":false,
                                      "contenido": "Este es un contenido"
                                    }
                                </pre>
                            </div>
                        </div>
                        <div id="tarificador-generacion-guia-response"  class="section-block">
                            <h4 class="block-title">Response</h4>
                            <p>Respuesta de petición de generación de guía JSON.</p>
                            <div>
                                <b>Composicion de JSON response</b>
                                <p>El campo "data": contiene los datos retornados de la generación de la guía.</p>
                                <ul>
                                    <li><b>status: </b> ok o error</li>
                                    <li><b>message: </b> Mensaje mostrado cuando se obtiene la respuesta de una cotización</li>
                                    <li>
                                        <b>data: </b> Cotización dependiento de los tipos de servicios
                                        <ul>
                                            <li><b>guia: </b>Número de guía</li>
                                            <li><b>file: </b>Archivo codificado</li>
                                            <li><b>pick_up: </b>Id de pick up</li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                            <div class="code-block">
                                <h6>Estructura de Response:</h6>
                                <pre class="json-display">
                                    {
                                        "status": "ok",
                                        "message": "Guia generada correctamente",
                                        "data": {
                                            "guia": "8617898483",
                                            "file": "%PDF-1.4...",
                                            "pick_up": "139103"
                                        }
                                    }
                                </pre>
                            </div>
                        </div>
                        <div id="tarificador-generacion-guia-error"  class="section-block">
                            <h4 class="block-title">Error</h4>
                            <p>Posibles errores debido a que no existe la cotización.</p>
                            <pre class="json-display">
                                {
                                    "status": "error",
                                    "message": "No existe una cotización con el token proporcionado"
                                }
                            </pre>
                        </div>
                    </section>
                    <hr>
                    <!--Tarificador rastreo de guia -->
                    <section id="tarificador-rastreo" class="doc-section">
                        <div>
                            <h3 class="section-title">Rastreo de Guía</h3>
                            <p>Método para el rastreo de la guía.</p>
                            <ul class="bg-light shadow-sm p-3 mb-2 rounded">
                                <li><b>Url: {!! route('tarificador.consultarGuiaMensajeria', '') !!}/{guia}</b></li>
                                <li><b>Método: GET</b></li>
                            </ul>
                        </div>
                        <div id="tarificador-rastreo-response"  class="section-block">
                            <h4 class="block-title">Response</h4>
                            <p>Respuesta de petición de generacion de guía JSON.</p>
                            <div>
                                <b>Composicion de JSON response</b>
                                <p>El campo "data": contiene los datos retornados de la generacion de una guía.</p>
                                <ul>
                                    <li><b>status: </b> ok o error.</li>
                                    <li><b>message: </b> Mensaje mostrado cuando se obtiene la respuesta de una cotización</li>
                                    <li>
                                        <b>data: </b> Cotización dependiento de los tipos de servicios
                                        <ul>
                                            <li><b>guia: </b>Número de guía</li>
                                            <li><b>file: </b>Archivo codificado</li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                            <div class="code-block">
                                <h6>Estructura de Response:</h6>
                                <pre class="json-display">
                                    {
                                        "status": "ok",
                                        "message": "Búsqueda exitosa",
                                        "data": {
                                            "rastreo": [
                                                {
                                                    "status": "SUCCESS",
                                                    "codigo_ubicacion_origen": "",
                                                    "codigo_ubicacion_destino": "",
                                                    "status_entrega": "",
                                                    "fecha_envio": "",
                                                    "eventos": [
                                                        {
                                                            "fecha_entrega": "",
                                                            "codigo_evento": "",
                                                            "evento": "",
                                                            "codigo_ubicacion": "",
                                                            "ubicacion": ""
                                                        }
                                                    ],
                                                    "ubicacion_origen": "",
                                                    "ubicacion_destino": ""
                                                }
                                            ]
                                        }
                                    }
                                </pre>
                            </div>
                        </div>
                        <div id="tarificador-rastreo-error"  class="section-block">
                            <h4 class="block-title">Error</h4>
                            <p>Posibles errores debido a que no existe o no se encuentra la guía.</p>
                            <pre class="json-display">
                                {
                                    "status": "error",
                                    "message": "La guia numero_de_guia no existe"
                                }
                            </pre>
                        </div>
                    </section>
                    <hr>
                    <!--Generacion de recoleccion-->
                    <section id="tarificador-recoleccion" class="doc-section">
                        <div>
                            <h3 class="section-title">Generación de Recolección</h3>
                            <p>Método para generar la recolección de la guía.</p>
                            <ul class="bg-light shadow-sm p-3 mb-2 rounded">
                                <li><b>Url: {!! route('tarificador.recoleccionService', '') !!}/{guia}</b></li>
                                <li><b>Método: GET</b></li>
                            </ul>
                        </div>
                        <div id="tarificador-recoleccion-response"  class="section-block">
                            <h4 class="block-title">Response</h4>
                            <p>Respuesta de una petición  de generación recolección de guía JSON.</p>
                            <div>
                                <b>Composicion de JSON response</b>
                                <p>El campo "data": contiene los datos retornados de recolección de guía.</p>
                                <ul>
                                    <li><b>status: </b> ok o error</li>
                                    <li><b>message: </b> Mensaje mostrado cuando se obtiene la respuesta de una recolección</li>
                                    <li>
                                        <b>data: </b> Cotización dependiento de los tipos de servicios
                                        <ul>
                                            <li><b>pick_up: </b>Número de recolección</li>
                                            <li><b>localizacion: </b>Código de localización de recolección generado por la mensajería</li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                            <div class="code-block">
                                <h6>Estructura de Response:</h6>
                                <pre class="json-display">
                                    {
                                        "status": "ok",
                                        "message": "Petición exitosa!",
                                        "data": {
                                            "recoleccion": {
                                                "status": "ok",
                                                "mensaje": "",
                                                "pick_up": "",
                                                "localizacion": ""
                                            }
                                        }
                                    }
                                </pre>
                            </div>
                        </div>
                        <div id="tarificador-recoleccion-error"  class="section-block">
                            <h4 class="block-title">Error</h4>
                            <p>Posibles errores debido a que no se pudo realizar la recolección.</p>
                            <pre class="json-display">
                                {
                                    "status": "ok",
                                    "message": "Petición exitosa!",
                                    "data": {
                                        "recoleccion": {
                                            "status": "Error",
                                            "mensaje": "Mensaje de error regresado por la mensajería"
                                        }
                                    }
                                }
                            </pre>
                        </div>
                    </section>
                </div>
            </div>

            <div class="doc-sidebar col-md-3 col-12 sidebar">
                <div id="doc-nav " class="doc-nav ">
                    <nav id="doc-menu" class="nav doc-menu flex-column ">
                        <!--Claro envios Api-->
                        <a class="nav-link scrollto" href="#claro-envios-api">Claro Envíos API</a>
                        <!--Headers-->
                        <a class="nav-link scrollto" href="#headers">Headers</a>
                        <!--Tarificador Rate-->
                        <a class="nav-link "  data-toggle="collapse"  href="#tarificador-rate">Generar Cotización</a>
                        <nav class="doc-sub-menu nav flex-column border-left" >
                            <a class="nav-link " href="#tarificador-rate-request">Request</a>
                            <a class="nav-link " href="#tarificador-rate-response">Response</a>
                            <a class="nav-link " href="#tarificador-rate-error">Error</a>
                        </nav>

                        <!--Tarificador generacion de guia-->
                        <a class="nav-link scrollto" href="#tarificador-generacion-guia">Generación de Guía</a>
                        <nav class="doc-sub-menu nav flex-column border-left">
                            <a class="nav-link scrollto" href="#tarificador-generacion-guia-request">Request</a>
                            <a class="nav-link scrollto" href="#tarificador-generacion-guia-response">Response</a>
                            <a class="nav-link scrollto" href="#tarificador-generacion-guia-error">Error</a>
                        </nav>

                        <!--Tarificador rastreo de guia-->
                        <a class="nav-link scrollto" href="#tarificador-rastreo">Rastreo de Guía</a>
                        <nav class="doc-sub-menu nav flex-column border-left">
                            <a class="nav-link scrollto" href="#tarificador-rastreo-response">Response</a>
                            <a class="nav-link scrollto" href="#tarificador-rastreo-error">Error</a>
                        </nav>

                        <!--Tarificador recoleccion de guia-->
                        <a class="nav-link scrollto" href="#tarificador-recoleccion">Recolección de Guía</a>
                        <nav class="doc-sub-menu nav flex-column border-left">
                            <a class="nav-link scrollto" href="#tarificador-recoleccion-response">Response</a>
                            <a class="nav-link scrollto" href="#tarificador-recoleccion-error">Error</a>
                        </nav>
                    </nav>

                </div>
            </div>
        </div>
    </div>
    </div>

    <script src="{!! asset('js/app.js') !!}"></script>
    <script src="{!! asset('assets/prism/min/prism-min.js') !!}"></script>
    <script src="{!! asset('assets/jquery-scrollto/jquery.scrollTo.min.js') !!}"></script>
    <script src="{!! asset('assets/js/jquery.json-editor.min.js') !!}"></script>
    <script>
        $(document).ready(function () {

                function getJson($this) {
                    try {
                        return JSON.parse($this.html());
                    } catch (ex) {
                        alert('Wrong JSON Format: ' + ex);
                    }
                }
                $('.json-display').each(
                    function () {
                        new JsonEditor($(this), getJson($(this)));
                    }
                );
            }
        );
    </script>
</body>
</html>