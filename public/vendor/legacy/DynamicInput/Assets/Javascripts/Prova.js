(function($){
  $(document).ready(function(){
    var $turmaField = getElementFor('turma');
    var $provaField = getElementFor('prova');

    var handleGetProvas = function(response) {
      var selectOptions = jsonResourcesToSelectOptions(response['options']);
      updateSelect($provaField, selectOptions, "Selecione uma prova");
    }

    var updateProvas = function(){
      resetSelect($provaField);

      if ($turmaField.val()) {
        $provaField.children().first().html('Aguarde carregando...');

        var urlForGetProvas = getResourceUrlBuilder.buildUrl('/module/DynamicInput/prova', 'provas', {
          turma_id: $turmaField.val()
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

    $turmaField.change(updateProvas);
  });
})(jQuery);
