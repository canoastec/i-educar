(function($){
  $(document).ready(function(){

    var $anoField = getElementFor('ano');
    var $provaField = getElementFor('prova');
    var $serieField = getElementFor('serie_prova');
    var $disciplinaField = getElementFor('disciplina_prova');

    $provaField.attr('data-no-autocomplete', 'true');

    var refreshChosen = function($field) {
      if (!$field || !$field.length) {
        return;
      }

      $field.trigger('chosen:updated');
    };

    var handleGetProvas = function(resources) {
      var selectOptions = jsonResourcesToSelectOptions(resources['options']);
      updateSelect($provaField, selectOptions, "Selecione uma prova");
      $provaField.val('');
      refreshChosen($provaField);
      $provaField.change();
    };

    var updateProvas = function(){
      resetSelect($provaField);
      $provaField.val('');
      $provaField.children().first().html('Selecione uma prova');
      refreshChosen($provaField);

      if (!($anoField.val() && $serieField.length && $disciplinaField.length && $serieField.val() && $disciplinaField.val())) {
        $provaField.change();
        return;
      }

      $provaField.children().first().html('Aguarde carregando...');
      refreshChosen($provaField);

      var urlForGetProvas = getResourceUrlBuilder.buildUrl('/module/DynamicInput/prova', 'provas', {
        ano: $anoField.val(),
        serie_prova: $serieField.val(),
        disciplina_prova: $disciplinaField.val()
      });

      getResources({
        url: urlForGetProvas,
        dataType: 'json',
        success: handleGetProvas
      });
    };

    $anoField.change(updateProvas);
    $serieField.change(updateProvas);
    $disciplinaField.change(updateProvas);

  });
})(jQuery);
