(function($){
  $(document).ready(function(){

    var $anoField = getElementFor('ano');
    var $provaField = getElementFor('prova');
    var $escolaField = getElementFor('escola_prova');

    $escolaField.attr('data-no-autocomplete', 'true');

    $escolaField.chosen({
      no_results_text: "Sem resultados para ",
      placeholder_text_single: "Digite o nome da escola",
      search_contains: true,
      allow_single_deselect: true,
      width: "100%"
    });

    var refreshChosen = function() {
      $escolaField.trigger('chosen:updated');
    };

    var handleGetEscolas = function(resources) {
      var selectOptions = jsonResourcesToSelectOptions(resources['options']);
      updateSelect($escolaField, selectOptions, "Selecione uma escola");
      $escolaField.val('');
      refreshChosen();
      $escolaField.change();
    };

    var updateEscolas = function(){
      resetSelect($escolaField);
      $escolaField.val('');
      $escolaField.children().first().html('Selecione uma escola');
      refreshChosen();

      if (!($anoField.val() && $provaField.val())) {
        $escolaField.change();
        return;
      }

      $escolaField.children().first().html('Aguarde carregando...');
      refreshChosen();

      var url = getResourceUrlBuilder.buildUrl('/module/DynamicInput/prova', 'escolas', {
        ano: $anoField.val(),
        prova: $provaField.val()
      });

      getResources({
        url: url,
        dataType: 'json',
        success: handleGetEscolas
      });
    };

    $anoField.change(updateEscolas);
    $provaField.change(updateEscolas);

  });
})(jQuery);
