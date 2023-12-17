<?php
    require_once(dirname(dirname(__DIR__)) . '/inc/bootstrap.php');
    require_once(RUTA_RAIZ . 'modelos/Enfrentamiento.php');
    require_once(RUTA_RAIZ . 'modelos/Torneo.php');
    require_once(RUTA_RAIZ . 'modelos/Equipo.php');
    require_once(RUTA_RAIZ . 'modelos/Kata.php');
    require_once(RUTA_RAIZ . 'modelos/Juez.php');
    require_once(RUTA_RAIZ . 'controladores/php/Base.php');

    $parametrosRequeridos = ["id_torneo", "numero_juez"];

    $respuesta = array();

    try{
        validarJuez();
        $datos = json_decode(file_get_contents("php://input"));
        $respuesta['estado'] = 'datos recibidos';
        validarParametros($datos, $parametrosRequeridos);
        $id_torneo = $datos->id_torneo;
        $numero_juez = $datos->numero_juez;
        $juez = new Juez($id_torneo, $numero_juez);
        $respuesta['pendientes'] = $juez->pendientesDePuntuar();
    }catch(Exception $e){
        $respuesta['error'] = $e->getMessage();
    }finally{
        echo json_encode($respuesta);
    }
?>