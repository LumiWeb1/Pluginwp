(function ($) {
    $(document).ready(function () {
        // Agregar una clase personalizada al contenedor principal
   
        // Agregar un manejador de eventos para el formulario
        $("form").on("submit", function (e) {
            e.preventDefault(); // Evitar el envío del formulario

            // Obtener el valor de la clave de API
            var apiKey = $("#api_key").val();

            // Realizar alguna validación o acción con la clave de API
            if (apiKey.trim() === "") {
                alert("Por favor, ingresa una clave de API válida.");
            } else {
                // Si todo está bien, enviar el formulario
                $(this).off("submit").submit();
            }
        });
    });
})(jQuery);