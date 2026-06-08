(function($){
  $(document).ready(function(){

    var $escolaField = getElementFor('escola_habilidade');
    var $turmaField = getElementFor('turma_habilidade');

    var handleGetTurmas = function(resources) {
      var selectOptions = jsonResourcesToSelectOptions(resources['options']);
      updateSelect($turmaField, selectOptions, "Todas as turmas");
    };

    var updateTurmas = function(){
      resetSelect($turmaField);

      if ($escolaField.val()) {
        $turmaField.children().first().html('Aguarde carregando...');

        var url = getResourceUrlBuilder.buildUrl('/module/DynamicInput/habilidade', 'turmas', {
          escola_habilidade: $escolaField.val()
        });

        var options = {
          url: url,
          dataType: 'json',
          success: handleGetTurmas
        };

        getResources(options);
      }

      $turmaField.change();
    };

    $escolaField.change(updateTurmas);

    if ($escolaField.val()) {
      updateTurmas();
    }

  });
})(jQuery);
