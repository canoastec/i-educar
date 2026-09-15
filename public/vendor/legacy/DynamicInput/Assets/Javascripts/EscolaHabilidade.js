(function($){
  $(document).ready(function(){

    var $escolaField = getElementFor('escola_habilidade');

    $escolaField.attr('data-no-autocomplete', 'true');

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
      $escolaField.val('');
      $escolaField.trigger('chosen:updated');
      $escolaField.change();
    };

    var updateEscolas = function(){
      resetSelect($escolaField);
      $escolaField.val('');
      $escolaField.children().first().html('Aguarde carregando...');
      $escolaField.trigger('chosen:updated');

      var url = getResourceUrlBuilder.buildUrl('/module/DynamicInput/habilidade', 'escolas', {});

      getResources({
        url: url,
        dataType: 'json',
        success: handleGetEscolas
      });
    };

    updateEscolas();

  });
})(jQuery);
