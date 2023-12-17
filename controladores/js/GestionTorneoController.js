import { validarSesion, validar, mostrarMensaje, gohome, redirect, mostrarPantallaCarga, ocultarPantallaCarga, estiloTabla } from "./TheController.js";

let urlAPITorneo = "../controladores/php/TorneoAPI.php";

$(document).ready(async function () {
    mostrarPantallaCarga();
    $(".btn_cerrarSesion").on("click", function(){
        document.cookie = "sesion_tecnicoSoftware=; max-age=0; path=/";
        gohome();
    });
    try {
        validarSesion('tecnicoSoftware');
        $("#btn_ingresar").on("click", function () {
            redirect("crearTorneo.html")
        });
        try {
            await construirTabla();
            ocultarPantallaCarga();
        } catch (e2) {
            $("#mensaje").html(e2);
            $("#mensaje").css("color", "verde");
        }
        $(".btn_continuar").on("click", function () {
            let id = $(this).attr("id");
            let id_torneo = id.split('-')[1];
            redirect(`interfazTecnicoSoftware.html?id_torneo=${id_torneo}`, 500);
        });
        $(".btn_eliminar").on("click", async function () {
            let opcion = confirm("¿Seguro que quieres eliminarlo?")
            if (opcion) {
                let id = $(this).attr("id");
                let id_torneo = id.split('-')[1];
                let torneo = {
                    id_torneo: id_torneo
                }
                //lo eliminamos
                await fetch(urlAPITorneo, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(torneo)
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
    await fetch(urlAPITorneo, {
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
            let encabezado = $("<thead><th>ID</th><th>NOMBRE</th><th>MODALIDAD</th><th>SEXO</th><th>RANGO DE EDAD</th><th>TÉCNICO DE SOFTWARE</th><th>FECHA</th><th>ESTADO</th><th colspan='2'>ACCIÓN</th></thead>");
            tabla.html(encabezado);
            let estado = "";
            let accion = "";
            let cantidadTorneos = datos.retorno.length;
            for (let i = 0; i < cantidadTorneos; i++) {
                let fila = $("<tr>");
                estado = datos.retorno[i].estado_torneo;
                accion = "";
                if (estado === "en curso") {
                    accion = `<td><input type='button' class='btn btn_continuar' id='btn_continuar-${datos.retorno[i].id_torneo}' value='Continuar'></td><td><input type='button' class='btn btn_eliminar' id='btn_eliminar-${datos.retorno[i].id_torneo}' value='Eliminar'</td>`;
                } else {
                    accion = `<td colspan='2'><input type='button' class='btn btn_eliminar' id='btn_eliminar-${datos.retorno[i].id_torneo}' value='Eliminar'</td>`;
                }

                let columnas = $("<td>" + datos.retorno[i].id_torneo + "</td><td>" + datos.retorno[i].nombre_torneo + "</td><td>" + datos.retorno[i].modalidad_torneo + "</td><td>" + datos.retorno[i].sexo_torneo + "</td><td>" + datos.retorno[i].rangoEdad_torneo + "</td><td>" + datos.retorno[i].nombreUsuario_tecnicoSoftware + "</td><td>" + datos.retorno[i].fecha_torneo + "</td><td>" + estado + "</td>" + accion);
                fila.html(columnas);
                tabla.append(fila);
            }
            containerTabla.html(tabla);
        })
        .catch(error => console.error(error));
}