<?php

namespace App\ClaroEnvios\Comercios;


use App\ClaroEnvios\Comercios\Direcciones\ComercioDireccion;
use App\ClaroEnvios\Comercios\Direcciones\ComercioDireccionTO;
use Illuminate\Support\Facades\DB;

class ComercioRepository implements ComercioRepositoryInterface
{

    public function guardarComercio(ComercioTO $comercioTO)
    {
        $comercio = new Comercio();
        $comercio->clave = $comercioTO->getClave();
        $comercio->descripcion = $comercioTO->getDescripcion();
        $comercio->envios_promedio = $comercioTO->getEnviosPromedio();
        $comercio->producto_tipo_id = $comercioTO->getProductoTipoId();
        $comercio->tipo_empresa = $comercioTO->getTipoEmpresa();
        $comercio->usuario_id = $comercioTO->getUsuarioId();
        $comercio->id_negociacion = $comercioTO->getIdNegociacion();
        $comercio->id_configuracion = $comercioTO->getIdConfiguracion();
       // die("<pre>".print_r($comercio));
        $comercio->save();
        $comercioTO->setId($comercio->id);
        $comercioDirecciones = $comercioTO->getComercioDirecciones();
       
        foreach ($comercioDirecciones as $direccion){
            $comercioDireccionTO = new ComercioDireccionTO();
            $comercioDireccionTO->setCodigoPostal($direccion['codigo_postal']);
            $comercioDireccionTO->setDireccionTipoId($direccion['direccion_tipo_id']);
            $comercioDireccionTO->setEstado($direccion['estado']);
            $comercioDireccionTO->setColonia($direccion['colonia']);
            $comercioDireccionTO->setMunicipio($direccion['municipio']);
            $comercioDireccionTO->setCalle($direccion['calle']);
            $comercioDireccionTO->setNumero($direccion['numero']);
            $comercioDireccionTO->setReferencias($direccion['referencias']);
            $comercioDireccionTO->setUsuarioId(auth()->id());
            if ($comercioDireccionTO instanceof ComercioDireccionTO) {
                $comercioDireccionTO->setComercioId($comercio->id);
                $this->guardarComercioDireccion($comercioDireccionTO);
            }
        }
    }

    private function guardarComercioDireccion(ComercioDireccionTO $comercioDireccionTO)
    {
        $comercioDireccion = new ComercioDireccion();
        $comercioDireccion->comercio_id = $comercioDireccionTO->getComercioId();
        $comercioDireccion->direccion_tipo_id = $comercioDireccionTO->getDireccionTipoId();
        $comercioDireccion->codigo_postal = $comercioDireccionTO->getCodigoPostal();
        $comercioDireccion->estado = $comercioDireccionTO->getEstado();
        $comercioDireccion->colonia = $comercioDireccionTO->getColonia();
        $comercioDireccion->municipio = $comercioDireccionTO->getMunicipio();
        $comercioDireccion->calle = $comercioDireccionTO->getCalle();
        $comercioDireccion->numero = $comercioDireccionTO->getNumero();
        $comercioDireccion->referencias = $comercioDireccionTO->getReferencias();
        $comercioDireccion->usuario_id = $comercioDireccionTO->getUsuarioId();
        $comercioDireccion->save();
    }

    public function registrarComercio(ComercioTO $comercioTO)
    {
        DB::transaction(
            function () use ($comercioTO) {
                $this->guardarComercio($comercioTO);
            }
        );
    }
    public function actualizaComercio(ComercioTO $comercioTO)
    {
        DB::transaction(
            function () use ($comercioTO) {
                $comercio = Comercio::findOrFail($comercioTO->id);
                $comercio->descripcion = $comercioTO->getDescripcion();
                $comercio->envios_promedio = $comercioTO->getEnviosPromedio();
                $comercio->update();
                $comercioDireccionTO = $comercioTO->getComercioDireccionTO();
                if ($comercioDireccionTO instanceof ComercioDireccionTO) {
                    $this->guardarComercioDireccion($comercioDireccionTO);
                }
            }
        );
    }
}