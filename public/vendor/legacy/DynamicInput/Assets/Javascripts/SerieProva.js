(function($){
  $(document).ready(function(){

    var $anoField = getElementFor('ano');
    var $disciplinaField = getElementFor('disciplina_prova');
    var $serieField = getElementFor('serie_prova');

    $serieField.attr('data-no-autocomplete', 'true');

    var refreshChosen = function($field) {
      if (!$field || !$field.length) {
        return;
      }

      $field.trigger('chosen:updated');
    };

    var handleGetSeries = function(resources) {
      var selectOptions = jsonResourcesToSelectOptions(resources['options']);
      updateSelect($serieField, selectOptions, "Selecione uma série");
      $serieField.val('');
      refreshChosen($serieField);
      $serieField.change();
    };

    var updateSeries = function(){
      resetSelect($serieField);
      resetSelect($disciplinaField);
      resetSelect(getElementFor('prova'));

      $serieField.val('');
      $disciplinaField.val('');
      getElementFor('prova').val('');

      $serieField.children().first().html('Selecione uma série');
      refreshChosen($serieField);
      refreshChosen($disciplinaField);
      refreshChosen(getElementFor('prova'));

      if (!$anoField.val()) {
        $serieField.change();
        return;
      }

      $serieField.children().first().html('Aguarde carregando...');
      refreshChosen($serieField);

      var url = getResourceUrlBuilder.buildUrl('/module/DynamicInput/prova', 'series', {
        ano: $anoField.val()
      });

      getResources({
        url: url,
        dataType: 'json',
        success: handleGetSeries
      });
    };

    $anoField.change(updateSeries);

    if ($anoField.val()) {
      updateSeries();
    }

  });
})(jQuery);
