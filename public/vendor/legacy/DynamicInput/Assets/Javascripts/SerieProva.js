(function($){
  $(document).ready(function(){

    var $anoField = getElementFor('ano');
    var $disciplinaField = getElementFor('disciplina_prova');
    var $serieField = getElementFor('serie_prova');

    var handleGetSeries = function(resources) {
      var selectOptions = jsonResourcesToSelectOptions(resources['options']);
      updateSelect($serieField, selectOptions, "Selecione uma série");
      $serieField.trigger('chosen:updated');
    };

    var updateSeries = function(){
      resetSelect($serieField);
      resetSelect($disciplinaField);
      resetSelect(getElementFor('prova'));
      $serieField.trigger('chosen:updated');
      $disciplinaField.trigger('chosen:updated');
      getElementFor('prova').trigger('chosen:updated');

      if ($anoField.val()) {
        $serieField.children().first().html('Aguarde carregando...');

        var url = getResourceUrlBuilder.buildUrl('/module/DynamicInput/prova', 'series', {
          ano: $anoField.val()
        });

        var options = {
          url: url,
          dataType: 'json',
          success: handleGetSeries
        };

        getResources(options);
      }

      $serieField.change();
    };

    $anoField.change(updateSeries);
    // only depends on year

    if ($anoField.val()) { updateSeries(); }

  });
})(jQuery);




