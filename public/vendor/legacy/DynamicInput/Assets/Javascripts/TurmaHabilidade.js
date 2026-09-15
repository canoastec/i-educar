(function($){
  $(document).ready(function(){

    var $escolaField = getElementFor('escola_habilidade');
    var $turmaField = getElementFor('turma_habilidade');

    if (!$turmaField.length) {
      return;
    }

    $turmaField.attr('data-no-autocomplete', 'true');

    var refreshChosen = function($field) {
      if (!$field || !$field.length) {
        return;
      }

      $field.trigger('chosen:updated');
    };

    var handleGetTurmas = function(resources) {
      var selectOptions = jsonResourcesToSelectOptions(resources['options']);
      updateSelect($turmaField, selectOptions, "Todas as turmas");
      $turmaField.val('');
      refreshChosen($turmaField);
      $turmaField.change();
    };

    var updateTurmas = function(){
      resetSelect($turmaField);
      $turmaField.val('');
      $turmaField.children().first().html('Todas as turmas');
      refreshChosen($turmaField);

      if (!$escolaField.val()) {
        $turmaField.change();
        return;
      }

      $turmaField.children().first().html('Aguarde carregando...');
      refreshChosen($turmaField);

      var url = getResourceUrlBuilder.buildUrl('/module/DynamicInput/habilidade', 'turmas', {
        escola_habilidade: $escolaField.val()
      });

      getResources({
        url: url,
        dataType: 'json',
        success: handleGetTurmas
      });
    };

    $escolaField.change(updateTurmas);

  });
})(jQuery);
