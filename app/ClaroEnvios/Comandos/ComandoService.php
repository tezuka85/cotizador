<?php

namespace App\ClaroEnvios\Comandos;


class ComandoService implements ComandoServiceInterface
{
    /**
     * @var ComandoRepositoryInterface
     */
    private $comandoRepository;

    /**
     * ComandoService constructor.
     */
    public function __construct(ComandoRepositoryInterface $comandoRepository)
    {
        $this->comandoRepository = $comandoRepository;
    }

    public function buscarComandos(ComandoTO $comandoTO)
    {
        return $this->comandoRepository->buscarComandos($comandoTO);
    }

    public function parametrosComandoEjecucion(ComandoEjecucionTO $comandoEjecucionTO)
    {
        $comandoTO = new ComandoTO();
        $comandoTO->setClase($comandoEjecucionTO->getClase());
        $comandoTO->setFirst(true);
        $comando = $this->comandoRepository->buscarComandos($comandoTO);
        $comandoEjecucionTO->setComandoId($comando->id);
    }
}
