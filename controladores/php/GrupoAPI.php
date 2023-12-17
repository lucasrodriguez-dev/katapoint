<?php
    require_once(dirname(dirname(__DIR__)) . '/inc/bootstrap.php');
    require_once(RUTA_RAIZ . 'modelos/Grupo.php');
    require_once(RUTA_RAIZ . 'modelos/Torneo.php');

    $respuesta = array();

    try {
        $error = "Sesión no válida";
        validarTecnicoSoftware();
        $datos = json_decode(file_get_contents("php://input"));
        $respuesta['estado'] = 'datos recibidos';
        $metodoSolicitud = $_SERVER["REQUEST_METHOD"];
        $retorno = null;
        switch(strtoupper($metodoSolicitud)) {
            case 'GET':
                $error = "No se pudo obtener a los grupos";
                if(isset($_GET['id_torneo'])){
                    $id_torneo = $_GET['id_torneo'];
                    if(isset($_GET['id_grupo'])){
                        $id_grupo = $_GET['id_grupo'];
                        $retorno = Grupo::listarGrupo($id_torneo, $id_grupo);
                    }else{
                        $retorno = Grupo::listar($id_torneo);
                    }
                }
                break;
            case 'POST':
                $error = "No se pudo barajar a los grupos";
                validarPOST($datos);
                $torneo = Torneo::getById($datos->id_torneo);
                if($torneo === null){
                    throw new Exception("Torneo no existente");
                }
                $torneo->barajarGrupos();
                $retorno = "Grupos barajados";
                break;
            default:
                throw new Exception("Método no permitido");  
        }
        $respuesta['retorno'] = $retorno;
    } catch (Exception $e) {
        $respuesta['error'] = $error . ": " . $e->getMessage();
    } finally {
        echo json_encode($respuesta);
    }

    function validarPOST($datos){
        $parametros = [
            ["nombre" => "id_torneo", "obligatorio" => true]
        ];
        validarParametros2($datos, $parametros);
    }
?>