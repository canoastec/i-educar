(function($){
  $(document).ready(function(){
    var $cursoField = getElementFor('curso');
    var $serieField = getElementFor('serie');
    var $provaField = getElementFor('prova');

    var handleGetProvas = function(response) {
      var selectOptions = jsonResourcesToSelectOptions(response['options']);
      updateSelect($provaField, selectOptions, "Selecione uma prova");
    }

    var updateProvas = function(){
      resetSelect($provaField);

      if ($serieField.val() && $cursoField.val()) {
        $provaField.children().first().html('Aguarde carregando...');

        var urlForGetProvas = getResourceUrlBuilder.buildUrl('/module/DynamicInput/prova', 'provas', {
          serie_id: $serieField.val(),
          curso_id: $cursoField.val()
        });

        var options = {
          url : urlForGetProvas,
          dataType : 'json',
          success  : handleGetProvas
        };

        getResources(options);
      }

      $provaField.change();
    };

    $cursoField.change(updateProvas);
    $serieField.change(updateProvas);
  });
})(jQuery);
