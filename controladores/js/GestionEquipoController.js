import { validarSesion, validar, mostrarMensaje, estiloTabla, getCookie, redirect, obtenerTorneo, obtenerDeUrl, gohome, mostrarPantallaCarga, ocultarPantallaCarga } from "./TheController.js";

const urlAPI = "../controladores/php/EquipoAPI.php";
const id_torneo = obtenerDeUrl('id_torneo');

$(document).ready(async function () {
    mostrarPantallaCarga();
    try {
        validarSesion('tecnicoSoftware');
        const torneo = await obtenerTorneo(obtenerDeUrl('id_torneo'));
        if(torneo.modalidad_torneo == "individual"){
            window.history.back();
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
        $(".btn_eliminar").on("click", async function () {
            let opcion = confirm("¿Seguro que quieres eliminarlo?")
            if (opcion) {
                let id = $(this).attr("id");
                let nombre_equipo = id.split('-')[1];
                let equipo = {
                    nombre_equipo: nombre_equipo
                }
                //lo eliminamos
                await fetch(urlAPI, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(equipo)
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
                        } else {
                            location.reload();
                        }
                    })
                    .catch(error => console.error(error));
            };
        });
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
            let encabezado = $("<thead><th>NOMBRE</th><th>COMPETIDORES</th><th>GRUPO</th></thead>");
            tabla.html(encabezado);
            let cantidadEquipos = datos.retorno.length;
            for (let i = 0; i < cantidadEquipos; i++) {
                let fila = $("<tr>");
                let equipo = datos.retorno[i];
                let columnaAccion = "";

                //si no tiene competidores
                if(!(equipo.competidores.length > 0)){
                    //le mostramos boton eliminar
                    columnaAccion = `<td><input type='button' class='btn btn_eliminar' id='btn_eliminar-${equipo.nombre_equipo}' value='Eliminar'</td>`;
                }

                let listaCompetidores = "<table>";
                for(let j = 0; j < equipo.competidores.length; j++){
                    let competidor = equipo.competidores[j];
                    listaCompetidores += `<tr><td>${competidor.nombreCompleto_competidor}</td></tr>`;
                }
                listaCompetidores += "</table>";
                let competidores = `<td>${listaCompetidores}</td>`;
                let columnas = $(`<td>${equipo.nombre_equipo}</td>${competidores}<td>${equipo.id_grupo}</td>${columnaAccion}`);
                fila.html(columnas);
                tabla.append(fila);
            }
            containerTabla.html(tabla);
        })
        .catch(error => console.error(error));
}