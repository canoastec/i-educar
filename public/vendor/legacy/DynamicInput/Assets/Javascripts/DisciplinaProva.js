(function($){
  $(document).ready(function(){

    var $anoField = getElementFor('ano');
    var $serieField = getElementFor('serie_prova');
    var $disciplinaField = getElementFor('disciplina_prova');
    var $provaField = getElementFor('prova');

    $disciplinaField.attr('data-no-autocomplete', 'true');

    var refreshChosen = function($field) {
      if (!$field || !$field.length) {
        return;
      }

      $field.trigger('chosen:updated');
    };

    var handleGetDisciplinas = function(resources) {
      var selectOptions = jsonResourcesToSelectOptions(resources['options']);
      updateSelect($disciplinaField, selectOptions, "Selecione uma disciplina");
      $disciplinaField.val('');
      refreshChosen($disciplinaField);
      $disciplinaField.change();
    };

    var updateDisciplinas = function(){
      resetSelect($disciplinaField);
      resetSelect($provaField);

      $disciplinaField.val('');
      $provaField.val('');

      $disciplinaField.children().first().html('Selecione uma disciplina');
      refreshChosen($disciplinaField);
      refreshChosen($provaField);

      if (!($anoField.val() && $serieField.val())) {
        $disciplinaField.change();
        return;
      }

      $disciplinaField.children().first().html('Aguarde carregando...');
      refreshChosen($disciplinaField);

      var url = getResourceUrlBuilder.buildUrl('/module/DynamicInput/prova', 'disciplinas', {
        ano: $anoField.val(),
        serie_prova: $serieField.val()
      });

      getResources({
        url: url,
        dataType: 'json',
        success: handleGetDisciplinas
      });
    };

    $anoField.change(updateDisciplinas);
    $serieField.change(updateDisciplinas);

  });
})(jQuery);
