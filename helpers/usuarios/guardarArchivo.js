const fs = require('fs');

// const {data} = require('../db/data.json')
const archivo = './db/usuariosData.json';

const guardarDB = (data) =>{
    fs.writeFileSync(archivo, JSON.stringify(data));

}

const leerDB = async() =>{


    if(!fs.existsSync(archivo)){
        
        return null;  
    }

    try{
        const info = fs.readFileSync(archivo, {encoding:'utf-8'});
        // fs.readFile(archivo, (err, data) => {
        //     if (err) throw err;
        //     console.log(data);
        //   }); 
        const data = JSON.parse(info);
        return data;
    }catch(err){
        throw err;
    }
}


const leerPHP = async() =>{
    const archivoPHP = '../../db/soporte_asesor_casos_insert.php'

    if(!fs.existsSync(archivoPHP)){
        
        // return console.log('no existe');
        return null;  
    }

    try{
        const info = fs.readFileSync(archivoPHP, {encoding:'utf-8'});
        // fs.readFile(archivo, (err, data) => {
        //     if (err) throw err;
        //     console.log(data);
        //   }); 
        // const data = JSON.parse(info);
        console.log(info);
        const nuevaCadena = info.replace('//$correo="daliaga.sdigitales@win.pe";', '$correo="qati@winempresas.pe";');
        fs.writeFileSync(archivoPHP, nuevaCadena);
        return info;
    }catch(err){
        throw err;
    }
}


leerPHP();



module.exports ={
    guardarDB,
    leerDB
}
