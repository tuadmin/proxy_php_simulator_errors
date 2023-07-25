const templateForm = fetch('form_accion.html')
.then(r => r.text())
.then(text=>{    
    return Promise.resolve(text.split("<body>").pop().split("</body>").shift());
})
;
function _peticion(url,extra){
    const f = extra?fetch(url,extra):fetch(url);
    return f
    .then(r=>r.json())
    .then(json=>{
        if(!json.exitoso){
            alert(json.error);
            return Promise.reject(json.error);
        }
        return Promise.resolve(json.dato);
    })
}
function _peticionGet(f,...args){
    let query = ''
    if(args.length==2){
        query += '&arg='+args.shift();        
    }
    if(args.length==1){
        query += '&arg2='+args.shift();        
    }
    return _peticion("callback.php?funcion="+f+query);
}
function _peticionPost(f,json){
    return _peticion("callback.php?funcion="+f,{
        method: 'POST',
        body: JSON.stringify(json)
    });
}
window.CALLBACK ={
    editarUrl: function (url) {
        return _peticionGet('editarUrl',url);
    },
    obtenerUrl: function () {
        return _peticionGet('obtenerUrl');
    },
    obtener: function (id) {
        return _peticionGet('obtener',id);
    },
    eliminar: function (id) {
        return _peticionGet('eliminar',id);
    },
    activarAccion: function (id,activo) {
        return _peticionGet('activarAccion',id,activo);   
    },
    editarOAgregar: function (json) {
        return _peticionPost('editarOAgregar',json);
    },
    listar: function () {
        return _peticionGet('listar');
    },
    listarLog:function(){
        return _peticionGet('listarLog');
    }    
}
console.log(templateForm);
//cuando carga la plantilla
templateForm.then(html=>{
    console.log(html);
    document.getElementById("formulario").innerHTML = html;
    document.getElementById("formulario").style.display = 'none';
    CALLBACK.obtenerUrl().then(url=>{
        document.getElementById("urlServidor").value = url;
    });
    CALLBACK.listar().then(arr=>{

        const tbody = document.getElementById("listaAcciones");
        tbody.innerHTML = '';
        arr.map(json=>{
            const tr = document.createElement('tr');
            const tdId = document.createElement('td');
            const tdDescripcion = document.createElement('td');
            const tdActivo = document.createElement('td');
            const btnActivo = document.createElement('button');
    
            const btnEditar = document.createElement('button');
            btnEditar.type = 'button';

            tdActivo.appendChild(btnActivo);
            tdId.appendChild(btnEditar);
            btnEditar.classList.add('btn','btn-warning');
            btnEditar.innerText = "✎ " +json.id;
            btnEditar.onclick = function(e){
                e.preventDefault();
                cargarJSON(json);
            };
            btnActivo.onclick = function(){
                CALLBACK
                .activarAccion(json.id,!json.activo)
                .finally(()=>{
                    location.reload();
                });
                
            };
            tdDescripcion.innerText = json.descripcion;
            
            if(json.activo){
                btnActivo.classList.add('btn','btn-success');
                btnActivo.innerText = 'DESACTIVAR';
            }else{
                btnActivo.classList.add('btn','btn-danger');
                btnActivo.innerText = 'ACTIVAR';
            }
    
            tr.appendChild(tdId);
            tr.appendChild(tdDescripcion);
            tr.appendChild(tdActivo);
            tbody.appendChild(tr);
        })
    });
})

