<?php

namespace App\ClaroEnvios\Respuestas;



class Response
{
    public static $messages = [
        'processError' => 'Error al procesar petición',
        'successfulSearch' => 'Búsqueda exitosa!',
        'successfulEdit' => 'Registro actualizado correctamente!',
        'successfulCreate' => 'Registro exitoso!',
        'successfulSave' => 'Datos guardados correctamente!',
        'successfulDelete' => 'Registro eliminado correctamente!',
        'dataNotFund' => 'No se encontraron registros con datos proporcionados',
        'courierActive' => 'Mensajeria activada correctamente',
        'guideGenerated' => 'Guía generada correctamente',
        'guideGeneratedError' => 'Guía generada con Error',
        'requestSuccessfully' => 'Petición procesada correctamente!',
        'unautorized' => 'El usuario no tiene permiso para realizar esta acción',
        'noCoverage' => 'Sin transportista / Sin cobertura',
        'dataInvalid' => 'The given data was invalid.',
        'successfulSaldo' => 'Saldo actualizado corectamente!',
        'noService' => 'Sin servicio',
        ];

}