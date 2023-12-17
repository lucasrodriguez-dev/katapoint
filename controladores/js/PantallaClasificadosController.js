import { mostrarMensaje, estiloTabla, redirect, obtenerTorneo, obtenerDeUrl, mostrarPantallaCarga, ocultarPantallaCarga } from "./TheController.js";

const urlAPI = "../controladores/php/EnfrentamientoAPI.php";
const id_torneo = obtenerDeUrl('id_torneo');
let encabezadoEquipo = "EQUIPO";
let torneo = null;

$(document).ready(async function () {
    mostrarPantallaCarga();
    try {
        torneo = await obtenerTorneo(obtenerDeUrl('id_torneo'));
        if(torneo !== null){
            if(torneo.modalidad_torneo == "individual"){
                encabezadoEquipo = "COMPETIDOR";
            }
        }
        $("#btn_ingresar").on("click", function () {
            redirect(`registrarEquipo.html?id_torneo=${id_torneo}`);
        });
        try {
            await construirTabla();
            ocultarPantallaCarga();
        } catch (e2) {
            $("#mensaje").html(e2);
            $("#mensaje").css("color", "verde");
        }
    } catch (e) {
        console.error(e);
    }
});

async function construirTabla() {
    let url = `${urlAPI}?id_torneo=${id_torneo}&clasificados=true`;
    await fetch(url, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
        .then(function (respuesta) {
            if (!respuesta.ok) {
                throw new Error("Error: " + respuesta.status);
            }
            return respuesta.json();
        })
        .then(function (datos) {
            console.log(datos);
            if (datos.error) {
                let error = datos.error;
                mostrarMensaje(error, 'rojo');
            }
            //declaramos los estilos para la tabla
            let style = estiloTabla();

            let containerTabla = $("#container_tabla");
            let encabezado = $(`<thead><th>POSICIÓN</th><th>${encabezadoEquipo}</th><th>PUNTAJE</th></thead>`);
            let cantidadEnfrentamientos = datos.retorno.length;

            //por cada enfrentamiento, generamos una tabla nueva
            for (let i = 0; i < cantidadEnfrentamientos; i++) {
                let tabla = $(`<table border='1' class='tabla' ${style}>`);
                tabla.html(encabezado);
                let enfrentamiento = datos.retorno[i];
                let equipos = ordenarEquipos(enfrentamiento.equipos);
                let cantidadEquipos = equipos.length;

                //por cada equipo, generamos una fila nueva
                for(let j = 0; j < cantidadEquipos; j++){
                    let equipo = equipos[j];
                    let fila = $("<tr>");
                    let nombreEquipo = equipo.nombre_equipo;
                    if(torneo.modalidad_torneo == "individual"){
                        if(equipo.competidores[0] !== null){
                            nombreEquipo = equipo.competidores[0].nombreCompleto_competidor;
                        }
                    }
                    let columnas = $(`<td>${equipo.posicion}</td><td>${nombreEquipo}</td><td>${equipo.puntaje}</td>`);
                    fila.html(columnas);
                    tabla.append(fila);
                }
                containerTabla.html(tabla);
            }
        })
        .catch(error => console.error(error));
}

function ordenarEquipos(equipos){
    for(let i = 0; i < (equipos.length - 1); i++){
        for(let j = (i + 1); j < equipos.length; j++){
            let equipoAnterior = equipos[i];
            let equipoPosterior = equipos[j];
            if(equipoAnterior && equipoPosterior){
                if(equipoPosterior.posicion < equipoAnterior.posicion){
                    equipos[j] = equipoAnterior;
                    equipos[i] = equipoPosterior;
                }
            }
        }
    }
    return equipos;
}