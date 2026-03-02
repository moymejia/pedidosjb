<?php
require_once '../wisetech/table.php';
require_once '../wisetech/security.php';
require_once '../wisetech/utils.php';
require_once '../entities/configuracion.php';

class whatsapp extends table
{
    use utils;
    private $idplantilla = '';
    private $idtelefono  = '';
    private $nombre_plantilla = '';
    private $idioma = '';
    private $last_error;
    private $ACCIONES = [];

    public function __construct($nombre_plantilla)
    {   
        parent::__construct(prefijo . 'seguridad', 'plantilla_whatsapp');
        $datos_plantilla        = mysql::getrow("SELECT idplantilla,idtelefono,idaccion,idioma FROM plantilla_whatsapp WHERE nombre = '$nombre_plantilla'");
        if(empty($datos_plantilla)){
            $this->last_error = "La plantilla no existe";
            utils::report_error(validation_error,$nombre_plantilla,$this->last_error);

            return false;
        }
        $security               = new security($datos_plantilla['idaccion']);
        $this->idplantilla      = $datos_plantilla['idplantilla'];
        $this->idtelefono       = $datos_plantilla['idtelefono'];
        $this->idioma           = $datos_plantilla['idioma'];
        $this->nombre_plantilla = $nombre_plantilla;
        
    }

    public function enviar_mensaje_whatsapp($datos,$numero_telefono)
    {
        $CONFIGURACION    = new configuracion();
        $token_api        = $CONFIGURACION->get_datos_configuracion('token_api_whatsapp');
        $campos           = $this->get_campos_plantilla($this->idplantilla);

        $parametros = [];
        foreach ($campos as $campo) {
            $nombreCampo = $campo;
    
            if (!isset($datos[$nombreCampo]) || trim($datos[$nombreCampo]) === '') {
                $this->last_error = "Dato $nombreCampo vacio.";
                utils::report_error(validation_error,$this->nombre_plantilla,$this->last_error);

                return false;
            }
    
            $parametros[] = [
                "type" => "text",
                "text" => $datos[$nombreCampo]
            ];
        }
        $body = [
            "messaging_product" => "whatsapp",
            "to" => $numero_telefono,
            "type" => "template",
            "template" => [
                "name" => $this->nombre_plantilla,
                "language" => ["code" => $this->idioma],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => $parametros
                    ]
                ]
            ]
        ];

        $ch = curl_init("https://graph.facebook.com/v22.0/{$this->idtelefono}/messages");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $token_api",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($response === false) {
            $this->last_error = 'No se recibió respuesta del servidor';
            utils::report_error(validation_error,$this->nombre_plantilla,$this->last_error);
            return false;
        }
        
        $data = json_decode($response, true);
        
        if (isset($data['messages'][0]['message_status']) && $data['messages'][0]['message_status'] === 'accepted'){
            return true;
        } else {
            $this->last_error = 'Error al enviar mensaje: ' . $response;
            utils::report_error(validation_error,$this->nombre_plantilla,$this->last_error);
            return false;
        };
    }


    public function get_campos_plantilla($idplantilla)
    {   
        $campos = [];
        $sql    = mysql::getresult("SELECT nombre_campo FROM plantilla_whatsapp_campo WHERE idplantilla = '$idplantilla' ORDER BY orden ASC ");
        
        while($row = mysql::getrowresult($sql)){
            $campos[] = $row['nombre_campo'];
        }

        return $campos;
    }
}