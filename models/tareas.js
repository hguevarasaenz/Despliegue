/*
 _listao:
    { 'uuidv4-21321321': {id:uuidv4-21321321, desc:asd, completadoEn:98765}},
     'uuidv4-21321322': {id:uuidv4-21321322, desc:asd, completadoEn:98765}},
     'uuidv4-21321323': {id:uuidv4-21321323, desc:asd, completadoEn:98765}
    }
 */

const Tarea = require("./tarea");


class Tareas {
    _listado = {};

    get listadoArr(){
        const listado = [];
        Object.keys(this._listado).forEach(key =>{
            const tarea = this._listado[key];
            listado.push(tarea);
        });

        return listado;

    }

    constructor(){
        this._listado = {};
    }

    cargarTareasFromArray( data =[] ){
        data.forEach(tarea => {
            this._listado[tarea.id] = tarea;
        })
    }



    crearTarea( desc = ''){

        const tarea = new Tarea(desc);
        this._listado[tarea.id] = tarea;
    }


}


module.exports = Tareas;