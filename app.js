require('colors');
const { guardarDB, leerDB: leerDBUsuarios } = require('./helpers/usuarios/guardarArchivo');
const { inquirerMenu, pausa , leerInput } = require('./helpers/inquirer');
const Tareas = require('./models/tareas');
const Usuarios = require('./models/usuarios');
const usuarioSesion = require('./models/usuarioSecion');

// const { mostrarMenu, pausa } = require('./helpers/chat');

console.clear();


const main = async() => {
    console.log('Hola mundo');
    let sesion = false;

    let opt = '';
    // const tareas = new Tareas();
    const usuarios = new Usuarios();

    const usuariosDB = await leerDBUsuarios();
    if(usuariosDB){
        // console.log('se llenara la data');
        // console.log(usuariosDB);
        usuarios.cargarUsuariosFromArray(usuariosDB);
    }

    const respCorreo = await leerInput('Ingrese su correo para loguearse: ','input');
    const respContrasenia = await leerInput('Ingrese su contrasenia para loguearse: ','password');

    const auth = new usuarioSesion();
    const respAuth = await auth.autenticar(respCorreo,respContrasenia);
    console.log(respAuth);
    return;



    // do {
        
    //     const respCorreo = await leerInput('Ingrese su correo para loguearse: ','input');
    //     const respContrasenia = await leerInput('Ingrese su contrasenia para loguearse: ','password');


        
        
    // } while ( opt !== '0' );

    do {
        //Imprimir el menú
        opt = await inquirerMenu();

        switch(opt){
            case '1':
                const NombreCompletoUsuario = await leerInput('Nombre Completo del usuario: ','input');
                const correo = await leerInput('Correo del usuario: ','input');
                const contrasenia = await leerInput('Contraseña del usuario: ','input');
                const rol = await leerInput('Rol del usuario: ','input');
                // tareas.crearTarea(desc);
                usuarios.crearUsuarios(NombreCompletoUsuario, correo, contrasenia, rol);
            break;

            case '2':
                console.log(usuarios.listadoArr);
                
            break;

        }
    
        guardarDB(usuarios.listadoArr);

        await pausa(); 

    }while(opt !== '0' );

    
}


main();