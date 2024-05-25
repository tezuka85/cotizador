<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CreateCustomControllers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:custom_controller {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new controller custom class for (t1envios)';

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
        $txt_this = '$this';
        $txt_request = '$request';
        $txt_id  = '$id';
        $fileContents =

            <<<EOT
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class $name extends Controller
{

    public  function __construct()
    {
        permissions_class($txt_this);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $txt_request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $txt_request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $txt_id
     * @return \Illuminate\Http\Response
     */
    public function show($txt_id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $txt_id
     * @return \Illuminate\Http\Response
     */
    public function edit($txt_id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $txt_request
     * @param  int  $txt_id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $txt_request, $txt_id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $txt_id
     * @return \Illuminate\Http\Response
     */
    public function destroy($txt_id)
    {
        //
    }

}

EOT;

        $written = Storage::disk('controllers')->put($name.'.php', $fileContents);

        if($written) {
            $this->info('Created new Controller '.$name.'.php in App\Http\Controllers');
        } else {
            $this->info('Something went wrong');
        }

    }
}
