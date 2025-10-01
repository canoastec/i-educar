(function($){
  $(document).ready(function(){

    var $anoField = getElementFor('ano');
    var $provaField = getElementFor('prova');

    var handleGetProvas = function(resources) {
      var selectOptions = jsonResourcesToSelectOptions(resources['options']);
      updateSelect($provaField, selectOptions, "Selecione uma prova");
    }

    var updateProvas = function(){
      resetSelect($provaField);

      if ($anoField.val()) {
        $provaField.children().first().html('Aguarde carregando...');

        var urlForGetProvas = getResourceUrlBuilder.buildUrl('/module/DynamicInput/prova', 'provas', {
          ano: $anoField.val()
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

    if ($anoField.val()) {
      updateProvas();
    }

  });
})(jQuery);


