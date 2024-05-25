<?php

namespace App\ClaroEnvios\Mensajerias\Auditorias;


class CostoMensajeriaAuditoriaRepository implements CostoMensajeriaAuditoriaRepositoryInterface
{

    public function guardarCostoMensajeria(CostoMensajeriaAuditoriaTO $costoMensajeriaAuditoriaTO)
    {
        $costoMensajeriaAuditoria = new CostoMensajeriaAuditoria();
        $costoMensajeriaAuditoria->costo_mensajeria_id = $costoMensajeriaAuditoriaTO->getCostoMensajeriaId();
        $costoMensajeriaAuditoria->comercio_id = $costoMensajeriaAuditoriaTO->getComercioId();
        $costoMensajeriaAuditoria->negociacion_id = $costoMensajeriaAuditoriaTO->getNegociacionId();
        $costoMensajeriaAuditoria->mensajeria_id = $costoMensajeriaAuditoriaTO->getMensajeriaId();
        $costoMensajeriaAuditoria->porcentaje = $costoMensajeriaAuditoriaTO->getPorcentaje();
        $costoMensajeriaAuditoria->costo = $costoMensajeriaAuditoriaTO->getCosto();
        $costoMensajeriaAuditoria->porcentaje_seguro = $costoMensajeriaAuditoriaTO->getPorcentajeSeguro();
        $costoMensajeriaAuditoria->usuario_id = $costoMensajeriaAuditoriaTO->getUsuarioId();
        $costoMensajeriaAuditoria->funcion = $costoMensajeriaAuditoriaTO->getFuncion();
        $costoMensajeriaAuditoria->save();
    }

    public function guardarCostoMensajeriaPorcentaje(
        CostoMensajeriaPorcentajeAuditoriaTO $costoMensajeriaPorcentajeAuditoriaTO
    ) {
        $costoMensajeriaPorcentajeAuditoria = new CostoMensajeriaPorcentajeAuditoria();
        $costoMensajeriaPorcentajeAuditoria->costo_mensajeria_porcentaje_id
            = $costoMensajeriaPorcentajeAuditoriaTO->getCostoMensajeriaPorcentajeId();
        $costoMensajeriaPorcentajeAuditoria->costo_mensajeria_id
            = $costoMensajeriaPorcentajeAuditoriaTO->getCostoMensajeriaId();
        $costoMensajeriaPorcentajeAuditoria->mensajeria_id
            = $costoMensajeriaPorcentajeAuditoriaTO->getMensajeriaId();
        $costoMensajeriaPorcentajeAuditoria->comercio_id
            = $costoMensajeriaPorcentajeAuditoriaTO->getComercioId();
        $costoMensajeriaPorcentajeAuditoria->porcentaje
            = $costoMensajeriaPorcentajeAuditoriaTO->getPorcentaje();
        $costoMensajeriaPorcentajeAuditoria->costo
            = $costoMensajeriaPorcentajeAuditoriaTO->getCosto();
        $costoMensajeriaPorcentajeAuditoria->porcentaje_seguro
            = $costoMensajeriaPorcentajeAuditoriaTO->getPorcentajeSeguro();
        $costoMensajeriaPorcentajeAuditoria->usuario_id
            = $costoMensajeriaPorcentajeAuditoriaTO->getUsuarioId();
        $costoMensajeriaPorcentajeAuditoria->updated_usuario_id
            = $costoMensajeriaPorcentajeAuditoriaTO->getUpdatedUsuarioId();
        $costoMensajeriaPorcentajeAuditoria->funcion
            = $costoMensajeriaPorcentajeAuditoriaTO->getFuncion();
        $costoMensajeriaPorcentajeAuditoria->save();
    }
}