(function($){
  $(document).ready(function(){

    var $escolaField = getElementFor('escola_habilidade');

    $escolaField.chosen({
      no_results_text: "Sem resultados para ",
      placeholder_text_single: "Digite o nome da escola",
      search_contains: true,
      allow_single_deselect: true,
      width: "100%"
    });

    var handleGetEscolas = function(resources) {
      var selectOptions = jsonResourcesToSelectOptions(resources['options']);
      updateSelect($escolaField, selectOptions, "Toda a rede");
      $escolaField.trigger('chosen:updated');
    };

    var updateEscolas = function(){
      resetSelect($escolaField);
      $escolaField.children().first().html('Aguarde carregando...');
      $escolaField.trigger('chosen:updated');

      var url = getResourceUrlBuilder.buildUrl('/module/DynamicInput/habilidade', 'escolas', {});

      var options = {
        url: url,
        dataType: 'json',
        success: handleGetEscolas
      };

      getResources(options);

      $escolaField.change();
    };

    updateEscolas();

  });
})(jQuery);
