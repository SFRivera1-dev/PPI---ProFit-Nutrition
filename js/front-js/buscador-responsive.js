document.addEventListener("DOMContentLoaded", () => {

    const overlay = document.getElementById("overlay");
    const btnCerrar = document.getElementById("CerrarModal");
    const btnAbrirBuscador = document.getElementById("buscar-responsive")
    const BuscadorResponsive = document.getElementById("BuscadorResponsive")


    function abrirMenu() {
        overlay.classList.add("activo");
        BuscadorResponsive.classList.add("activo")
    }

    function cerrarMenu() {
        overlay.classList.remove("activo");
        BuscadorResponsive.classList.remove("activo")
        
    }

    btnAbrirBuscador.addEventListener("click", abrirMenu);
    overlay.addEventListener("click", cerrarMenu); 
    btnCerrar.addEventListener("click", cerrarMenu)



});
