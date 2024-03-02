const Usuario = require('./usuario');

class Usuarios{
    _usuarios ={};
  
    constructor(){
        this._usuarios = {}
    };

    get listadoArr(){
        const listado = [];
        Object.keys(this._usuarios).forEach(userKey => {
            const usuario = this._usuarios[userKey];
            listado.push(usuario);
        });
        return listado;
    };

    crearUsuarios(nombreCompleto= '', correo= '', contrasenia = '', rol= ''){
        const usuario = new Usuario(nombreCompleto,correo, contrasenia, rol);
        this._usuarios[usuario.id] = usuario; 
    };

    cargarUsuariosFromArray(data = []){
        data.forEach(usuario =>{
            this._usuarios[usuario.id] = usuario;
        });
    };

    




}



module.exports = Usuarios;