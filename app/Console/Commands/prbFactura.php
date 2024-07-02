<?php

namespace App\Console\Commands;

use App\Pago;
use App\Param;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class prbFactura extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ian:prbFactura';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $pago = Pago::find(202535); // Id del pago correspondiente al concepto de Enero del 2023 del cliente 61252 que se uso para probar el proceso el dia de ayer 06/01/2023
        //dd($pago);

        $parametroUrl = Param::where('llave', 'fact_global_url')->first();
        $url = $parametroUrl->valor; //https://factudesk.api.induxsoft.net/comprobantes/

        //Envio de correo por parte del proveedor
        //Datos enviados en la peticion
        $data = array(
            "uid" => $pago->caja->plantel->matriz->fact_global_id_usu, //CUE100913 cuenta usada
            "pwd" => $pago->caja->plantel->matriz->fact_global_pass_usu, // CFDI2022 contraseña usada
            "doc" => $pago->uuid, // 0aeb1f6e-599f-4dd3-9e19-6b98a5c91fd3
            "to" => 'linares82@gmail.com', //Correo electronico que debe recibir la factura
            //"from": "Nombre para mostrar del remitente (opcional)",
            //"cc"=>"Dirección de correo cc (opcional)",opcional
            //"cco"=>"Dirección de correo de copia oculta (opcional)",
            //"reply"=>"Dirección de correo de respuesta (opcional)",
            "subject" => "Factura PRB",
            //"body"=>"Cuerpo del mensaje de correo (opcional)",
            //"tpo"=>"cfdi/cr (opcional)",
            "res"=>"both",
            //"pln"=>"(Opcional) Identificador de plantilla de representación impresa"
        );
        //dd($data);
        //Peticion de envio del correo
        //Documentacion de Guzzle https://docs.guzzlephp.org/en/stable/request-options.html#form-params
        $client = new Client(['base_uri' => $url]); //Inicializa la clase que hace la peticion con Guzzle
        $response = $client->post("enviar/", [  //Se arma la url final a la que se hace la peticon quedando https://factudesk.api.induxsoft.net/comprobantes/enviar/ con el verbo POST
            // un array con la data de los headers como tipo de peticion, etc.
            //'headers' => ['foo' => 'bar'],
            // array de datos del formulario
            //'json' => $data //Formato json fallo envio se recibe un +"success": 0 +"message": "{-99} Error en la invocación del servicio[Ln= 32]"
            'form_params'=>$data //Formato de datos tipo Form Exitoso en las pruebas
            //'query'=>$data //formato wuery string fallo envio
        ]);
        //Validacion de envio del correo
        $objR = json_decode($response->getBody()->getContents()); //Se recibe un valor null
        //dd($objR); 

        //Al tener un valor null no se puede validar el exito o fracazo de la peticion en el siguiente codigo
        
        if($objR->success==0){
            Log::info('uuid: '.$pago->uuid.' Peticion de correo fallida');
            echo 'Problema en el envio a su correo, pero puede descargar sus archivos xml y pdf lista de pagos realizados.';
            echo "<a href=\"{{route('fichaAdeudos.index')\"}} > ir a lista de pagos realizados </a>";
        }else{
            Log::info('uuid: '.$pago->uuid.' Peticion de correo exitosa');
        }
    }
}
