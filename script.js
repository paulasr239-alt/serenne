document.addEventListener("DOMContentLoaded", function () {
const senha = document.querySelector('input[name="senha"]');
    const confirmar = document.querySelector('input[name="confirmar"]');

    if (senha && confirmar) {

        function validarSenha() {
            confirmar.setCustomValidity(
                senha.value !== confirmar.value
                    ? "As senhas não coincidem."
                    : ""
            );
        }

        senha.addEventListener("input", validarSenha);
        confirmar.addEventListener("input", validarSenha);
    }

    
    document.querySelectorAll('input[name="quantidade"]').forEach(function (campo) {

        campo.addEventListener("input", function () {

            const quantidade = Number(campo.value);

            if (quantidade < 1) {
                campo.setCustomValidity("Informe uma quantidade válida.");
            } else if (campo.max && quantidade > Number(campo.max)) {
                campo.setCustomValidity("Quantidade superior ao estoque.");
            } else {
                campo.setCustomValidity("");
            }

        });

    });

    document.querySelectorAll(".form-excluir").forEach(function (formulario) {

        formulario.addEventListener("submit", function (evento) {

            if (!confirm("Deseja realmente excluir esta joia?")) {
                evento.preventDefault();
            }

        });

    });

});