(function($){
  $(document).ready(function(){

    var $anoField = getElementFor('ano');
    var $provaField = getElementFor('prova');
    var $serieField = getElementFor('serie_prova');
    var $disciplinaField = getElementFor('disciplina_prova');

    var handleGetProvas = function(resources) {
      var selectOptions = jsonResourcesToSelectOptions(resources['options']);
      updateSelect($provaField, selectOptions, "Selecione uma prova");
      $provaField.trigger('chosen:updated');
    }

    var updateProvas = function(){
      resetSelect($provaField);
      $provaField.trigger('chosen:updated');

      if ($anoField.val() && $serieField.length && $disciplinaField.length && $serieField.val() && $disciplinaField.val()) {
        $provaField.children().first().html('Aguarde carregando...');

        var urlForGetProvas = getResourceUrlBuilder.buildUrl('/module/DynamicInput/prova', 'provas', {
          ano: $anoField.val(),
          serie_prova: $serieField.val(),
          disciplina_prova: $disciplinaField.val()
        });

        var options = {
          url: urlForGetProvas,
          dataType: 'json',
          success: handleGetProvas
        };

        getResources(options);
      }

      $provaField.change();
    };

    $anoField.change(updateProvas);
    $serieField.change(updateProvas);
    $disciplinaField.change(updateProvas);

    if ($anoField.val() && $serieField.length && $disciplinaField.length && $serieField.val() && $disciplinaField.val()) {
      updateProvas();
    }

  });
})(jQuery);


