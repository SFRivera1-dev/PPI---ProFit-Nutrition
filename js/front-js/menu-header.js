document.addEventListener("DOMContentLoaded", () => {
    const btnAbrir = document.getElementById("abrirMenu");
    const menuLateral = document.getElementById("menuLateral");
    const overlay = document.getElementById("overlay");
    const btnCerrar = document.getElementById("menuCerrar");


    function abrirMenu() {
        menuLateral.classList.add("activo");
        overlay.classList.add("activo");
    }

    function cerrarMenu() {
        menuLateral.classList.remove("activo");
        overlay.classList.remove("activo");
        
    }

    btnAbrir.addEventListener("click", abrirMenu);
    overlay.addEventListener("click", cerrarMenu); 
    btnCerrar.addEventListener("click", cerrarMenu)



});
