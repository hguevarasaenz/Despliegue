const { leerDB: leerDBMenu } = require("../helpers/menu/guardarArchivo");



class Menu{

    constructor(){}


    async getMenuPorRol(rol='0'){
        const traerDataMenu = await this.traerDataMenu();
        const respMenuPorRol = traerDataMenu.reduce( (acc,menu) =>{
            const existeRol = menu.tipoRol.some(rolMenu => rolMenu.rol ===  rol);
            if(existeRol){
                acc.push({
                    "value": menu.value,
                    "name": menu.name,
                })
            }
            return acc;
        },[]);

        return respMenuPorRol;
    }

    async traerDataMenu(){
        const respDataMenu = await leerDBMenu();
        // const resp = datamenu || [];
        // console.log(resp);
        return respDataMenu || [];
        
    }

}

// const as = new Menu();
// const b = async ()=> {
//     const asdasd = await as.getMenuPorRol('2');
//     console.log(asdasd);
//     return asdasd;
// }
// console.log(b());



// const traerDataMenu = async () =>{
//         const respDataMenu = await leerDBMenu();
//         // const resp = respDataMenu || [];
//         // console.log(respDataMenu);
//         return respDataMenu || [];
//     }
// traerDataMenu();
// console.log(__dirname);


// const getMenuPorRol = async (rol='0') =>{
//         const traerData = await traerDataMenu();
//         // console.log(traerData);
//         const resp = traerData.reduce( (acc,menu) =>{
//             const existeRol = menu.tipoRol.some(rolMenu => rolMenu.rol ===  rol);
//             if(existeRol){
//                 acc.push({
//                     "value": menu.value,
//                     "name": menu.name,
//                 })
//             }


//             return acc;
//         },[]);

//         console.log(resp);
        
// }
// getMenuPorRol('2');



module.exports = Menu;