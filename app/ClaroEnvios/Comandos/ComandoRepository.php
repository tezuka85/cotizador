<?php
namespace App\ClaroEnvios\Comandos;


class ComandoRepository implements ComandoRepositoryInterface
{

    public function buscarComandos(ComandoTO $comandoTO)
    {
        $comando = Comando::query();
        $comando->when(
            $comandoTO->getClase(),
            function ($query) use($comandoTO) {
                $query->where('clase', '=', $comandoTO->getClase());
            }
        );
        if ($comandoTO->getFirst()) {
            return $comando->first();
        }
        return $comando->get();
    }

    public function guardarComandoEjecucion(
        ComandoEjecucionTO $comandoEjecucionTO
    ) {
        $comandoEjecucion = new ComandoEjecucion();
        $comandoEjecucion->comando_id = $comandoEjecucionTO->getComandoId();
        $comandoEjecucion->fecha_inicio = $comandoEjecucionTO->getFechaInicio();
        $comandoEjecucion->fecha_fin = date('Y-m-d H:i:s');
        $comandoEjecucion->save();
    }
}
