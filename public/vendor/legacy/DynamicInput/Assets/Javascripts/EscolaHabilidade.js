(function($){
  $(document).ready(function(){

    var $escolaField = getElementFor('escola_habilidade');

    var handleGetEscolas = function(resources) {
      var selectOptions = jsonResourcesToSelectOptions(resources['options']);
      updateSelect($escolaField, selectOptions, "Toda a rede");
    };

    var updateEscolas = function(){
      resetSelect($escolaField);
      $escolaField.children().first().html('Aguarde carregando...');

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
