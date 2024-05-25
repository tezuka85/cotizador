<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CreateCustomViews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:custom_view {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new view custom class for (t1Envios)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->argument('name');

        $fileContents = <<<EOT
        
@extends('layouts.app')
@section('title', 'Dashboard')
@section('body-id', 'home')
@section('main-class', 'home')

@section('content')


@stop

@section('js')

    <script>

        // --

    </script>

@stop



EOT;

        // Cambiar por el nombre de la carpeta
        $name_folder = strtolower($name);
        $name_methods = [
            'index',
            'create',
            'update'
        ];
        Storage::disk('views')->makeDirectory($name);
        foreach ($name_methods as $method){
            $written = Storage::disk('views')->put($name_folder.'/'.$method.'.blade.php', $fileContents);
            if($written) {
                $this->info('Created new View '.$method.'.blade..php in resources/views/admin/'.$name_folder);
            } else {
                $this->info('Something went wrong');
            }
        }

    }
}
