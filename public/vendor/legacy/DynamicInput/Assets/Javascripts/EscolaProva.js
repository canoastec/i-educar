(function($){
  $(document).ready(function(){

    var $anoField = getElementFor('ano');
    var $provaField = getElementFor('prova');
    var $escolaField = getElementFor('escola_prova');

    var handleGetEscolas = function(resources) {
      var selectOptions = jsonResourcesToSelectOptions(resources['options']);
      updateSelect($escolaField, selectOptions, "Selecione uma escola");
    };

    var updateEscolas = function(){
      resetSelect($escolaField);

      if ($anoField.val() && $provaField.val()) {
        $escolaField.children().first().html('Aguarde carregando...');

        var url = getResourceUrlBuilder.buildUrl('/module/DynamicInput/prova', 'escolas', {
          ano: $anoField.val(),
          prova: $provaField.val()
        });

        var options = {
          url: url,
          dataType: 'json',
          success: handleGetEscolas
        };

        getResources(options);
      }

      $escolaField.change();
    };

    $anoField.change(updateEscolas);
    $provaField.change(updateEscolas);

    if ($anoField.val() && $provaField.val()) {
      updateEscolas();
    }

  });
})(jQuery);




