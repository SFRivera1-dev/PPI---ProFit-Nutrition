const inputs = document.querySelectorAll('.SearchInput');
const buttons = document.querySelectorAll('.ButtonClear');

inputs.forEach((input, index) => {

    const button = buttons[index];

    input.addEventListener('input', function () {

        if (input.value.length > 0) {
            button.style.visibility = 'visible';
        } else {
            button.style.visibility = 'hidden';
        }

    });

    button.addEventListener('click', function () {
        input.value = '';
        button.style.visibility = 'hidden';
        input.dispatchEvent(new Event('input'));   // avisa al buscador del header que se vació
        input.focus();
    });
});