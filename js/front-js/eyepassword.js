const passwords = document.querySelectorAll('.password');

passwords.forEach((password) => {

    // Busca el botón del ojito dentro del MISMO .input-wrap que este input
    const inputWrap = password.closest('.input-wrap');
    const buttonPasswordEye = inputWrap.querySelector('.ButtonPasswordEye');
    const iconEye = buttonPasswordEye.querySelector('i');

    password.addEventListener("input", () => {
        if (password.value.length > 0) {
            buttonPasswordEye.style.visibility = "visible";
        } else {
            buttonPasswordEye.style.visibility = "hidden";
            password.type = "password";
            iconEye.classList.remove("fa-eye-slash");
            iconEye.classList.add("fa-eye");
        }
    });

    function mostrarPassword() {
        password.type = "text";
        iconEye.classList.remove("fa-eye");
        iconEye.classList.add("fa-eye-slash");
    }

    function ocultarPassword() {
        password.type = "password";
        iconEye.classList.remove("fa-eye-slash");
        iconEye.classList.add("fa-eye");
    }

    // PC
    buttonPasswordEye.addEventListener("mousedown", mostrarPassword);
    buttonPasswordEye.addEventListener("mouseup", ocultarPassword);
    buttonPasswordEye.addEventListener("mouseleave", ocultarPassword);

    // Celular
    buttonPasswordEye.addEventListener("touchstart", mostrarPassword);
    buttonPasswordEye.addEventListener("touchend", ocultarPassword);
    buttonPasswordEye.addEventListener("touchcancel", ocultarPassword);

});