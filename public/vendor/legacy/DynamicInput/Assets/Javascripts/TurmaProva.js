(function($){
  $(document).ready(function(){

    var $anoField = getElementFor('ano');
    var $provaField = getElementFor('prova');
    var $escolaField = getElementFor('escola_prova');
    var $turmaField = getElementFor('turma_prova');

    var handleGetTurmas = function(resources) {
      var selectOptions = jsonResourcesToSelectOptions(resources['options']);
      updateSelect($turmaField, selectOptions, "Selecione uma turma");
    };

    var updateTurmas = function(){
      resetSelect($turmaField);

      if ($anoField.val() && $provaField.val() && $escolaField.val()) {
        $turmaField.children().first().html('Aguarde carregando...');

        var url = getResourceUrlBuilder.buildUrl('/module/DynamicInput/prova', 'turmas', {
          ano: $anoField.val(),
          prova: $provaField.val(),
          escola_prova: $escolaField.val()
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

    $anoField.change(updateTurmas);
    $provaField.change(updateTurmas);
    $escolaField.change(updateTurmas);

    if ($anoField.val() && $provaField.val() && $escolaField.val()) { updateTurmas(); }

  });
})(jQuery);




