(function($){
  $(document).ready(function(){

    var $codigoField = getElementFor('codigo_habilidade');
    var $habilidadeField = getElementFor('habilidade');

    var handleGetCodigos = function(resources) {
      var selectOptions = jsonResourcesToSelectOptions(resources['options']);
      updateSelect($codigoField, selectOptions, "Todos os códigos");
    };

    var loadCodigos = function(){
      resetSelect($codigoField);
      $codigoField.children().first().html('Aguarde carregando...');

      var url = getResourceUrlBuilder.buildUrl('/module/DynamicInput/habilidade', 'codigos', {});

      var options = {
        url: url,
        dataType: 'json',
        success: handleGetCodigos
      };

      getResources(options);

      $codigoField.change();
    };

    $codigoField.change(function(){
      if (!$habilidadeField.length) {
        return;
      }

      var value = $codigoField.val();

      if ($habilidadeField.val() !== value) {
        $habilidadeField.val(value);
        if ($habilidadeField.hasClass('chzn-done')) {
          $habilidadeField.trigger('liszt:updated');
        }
      }
    });

    loadCodigos();

  });
})(jQuery);
