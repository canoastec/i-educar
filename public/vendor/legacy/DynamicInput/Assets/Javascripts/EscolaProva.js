(function($){
  $(document).ready(function(){

    var $anoField = getElementFor('ano');
    var $provaField = getElementFor('prova');
    var $escolaField = getElementFor('escola_prova');

    $escolaField.chosen({
      no_results_text: "Sem resultados para ",
      placeholder_text_single: "Digite o nome da escola",
      search_contains: true,
      allow_single_deselect: true,
      width: "100%"
    });

    var handleGetEscolas = function(resources) {
      var selectOptions = jsonResourcesToSelectOptions(resources['options']);
      updateSelect($escolaField, selectOptions, "Selecione uma escola");
      $escolaField.trigger('chosen:updated');
    };

    var updateEscolas = function(){
      resetSelect($escolaField);
      $escolaField.trigger('chosen:updated');

      if ($anoField.val() && $provaField.val()) {
        $escolaField.children().first().html('Aguarde carregando...');
        $escolaField.trigger('chosen:updated');

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
