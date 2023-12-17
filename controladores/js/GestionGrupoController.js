import { validarSesion, validar, mostrarMensaje, estiloTabla, redirect, obtenerTorneo, obtenerDeUrl, gohome, mostrarPantallaCarga, ocultarPantallaCarga } from "./TheController.js";

const urlAPI = "../controladores/php/GrupoAPI.php";
const id_torneo = obtenerDeUrl('id_torneo');
let torneo = null;

$(document).ready(async function () {
    mostrarPantallaCarga();
    torneo = await obtenerTorneo(id_torneo);
    try {
        validarSesion('tecnicoSoftware');
        $("#btn_ingresar").on("click", async function () {
            await barajarGrupos();
            await construirTabla();
        });
        try {
            await construirTabla();
            ocultarPantallaCarga();
        } catch (e2) {
            mostrarMensaje(e2, "rojo");
        }
    } catch (e) {
        console.error(e);
        if(e === "No se inició sesión"){
            gohome();
        }
    }
});

async function construirTabla() {
    let url = `${urlAPI}?id_torneo=${id_torneo}`;
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
            let tabla = $(`<table border='1' class='tabla' ${style}>`);

            let encabezadoEquipos = "EQUIPOS";
            if(torneo.modalidad_torneo == "individual"){
                encabezadoEquipos = "COMPETIDORES";
            }

            let encabezado = $(`<thead><th>GRUPO</th><th>${encabezadoEquipos}</th></thead>`);
            tabla.html(encabezado);
            let cantidadGrupos = datos.retorno.length;
            for (let i = 0; i < cantidadGrupos; i++) {
                let fila = $("<tr>");
                let grupo = datos.retorno[i];

                let listaEquipos = "<table>";
                for(let j = 0; j < grupo.equipos.length; j++){
                    let equipo = grupo.equipos[j];
                    let nombreEquipo = equipo.nombre_equipo;
                    if(torneo.modalidad_torneo == "individual"){
                        if(equipo.competidores[0] != null){
                            nombreEquipo = equipo.competidores[0].nombreCompleto_competidor;
                        }
                    }
                    listaEquipos += `<tr><td>${nombreEquipo}</td></tr>`;
                }
                listaEquipos += "</table>";
                let equipos = `<td>${listaEquipos}</td>`;
                let columnas = $(`<td>${grupo.id_grupo}</td>${equipos}`);
                fila.html(columnas);
                tabla.append(fila);
            }
            containerTabla.html(tabla);
        })
        .catch(error => console.error(error));
}

async function barajarGrupos(){
    let objeto = {
        id_torneo: id_torneo
    }
    await fetch(urlAPI, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(objeto)
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
                alert(error);
            }
            
        })
        .catch(error => console.error(error));
}