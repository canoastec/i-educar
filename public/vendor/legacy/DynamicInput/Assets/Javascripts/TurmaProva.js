(function($){
  $(document).ready(function(){

    var $anoField = getElementFor('ano');
    var $provaField = getElementFor('prova');
    var $escolaField = getElementFor('escola_prova');
    var $turmaField = getElementFor('turma_prova');

    $turmaField.attr('data-no-autocomplete', 'true');

    var refreshChosen = function($field) {
      if (!$field || !$field.length) {
        return;
      }

      if ($field.hasClass('chzn-done')) {
        $field.trigger('liszt:updated');
      }

      $field.trigger('chosen:updated');
    };

    var clearTurma = function(placeholder) {
      resetSelect($turmaField);
      $turmaField.val('');
      $turmaField.children().first().html(placeholder || 'Selecione uma turma');
      refreshChosen($turmaField);
    };

    var handleGetTurmas = function(resources) {
      var selectOptions = jsonResourcesToSelectOptions(resources['options']);
      updateSelect($turmaField, selectOptions, 'Selecione uma turma');
      $turmaField.val('');
      refreshChosen($turmaField);
      $turmaField.change();
    };

    var updateTurmas = function(){
      clearTurma('Selecione uma turma');

      if (!($anoField.val() && $provaField.val() && $escolaField.val())) {
        $turmaField.change();
        return;
      }

      $turmaField.children().first().html('Aguarde carregando...');
      refreshChosen($turmaField);

      var url = getResourceUrlBuilder.buildUrl('/module/DynamicInput/prova', 'turmas', {
        ano: $anoField.val(),
        prova: $provaField.val(),
        escola_prova: $escolaField.val()
      });

      getResources({
        url: url,
        dataType: 'json',
        success: handleGetTurmas
      });
    };

    $anoField.change(updateTurmas);
    $provaField.change(updateTurmas);
    $escolaField.change(updateTurmas);

  });
})(jQuery);
