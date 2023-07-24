// Función para crear o editar el JSON
function crearEditarJSON() {
    var json = {
        "id": document.getElementById("id").value,
        "activo": document.getElementById("activo").checked,
        "descripcion": document.getElementById("descripcion").value,
        "coincidencia_url": document.getElementById("coincidencia_url").value.split('\n'),
        "coincidencia_texto": document.getElementById("coincidencia_texto").value.split('\n'),
        "retornar_texto": document.getElementById("retornar_texto").value,
        "retornar_httpcode": parseInt(document.getElementById("retornar_httpcode").value),
        "demorar": parseInt(document.getElementById("demorar").value)
    };

    // Para ver el JSON resultante en la consola
    //console.log(json);
    return CALLBACK.editarOAgregar(json).then(()=>{
        document.getElementById("formulario").style.display = "none";
    });
    // Aquí puedes enviar el JSON a un servidor o hacer cualquier otra cosa que desees con él
}

// Función para cargar el JSON existente para editar
function cargarJSON(jsonData) {
    document.getElementById("id").value = jsonData.id??'';
    document.getElementById("activo").checked = jsonData.activo??false;
    document.getElementById("descripcion").value = jsonData.descripcion??'';
    document.getElementById("coincidencia_url").value = jsonData.coincidencia_url?jsonData.coincidencia_url.join('\n'):'';
    document.getElementById("coincidencia_texto").value = jsonData.coincidencia_texto?jsonData.coincidencia_texto.join('\n'):'';
    document.getElementById("retornar_texto").value = jsonData.retornar_texto??'';
    document.getElementById("retornar_httpcode").value = jsonData.retornar_httpcode??200;
    document.getElementById("demorar").value = jsonData.demorar??0;
    document.getElementById("formulario").style.display = "block";
}
