(function($){
  $(document).ready(function(){

    var $codigoField = getElementFor('codigo_habilidade');
    var $habilidadeField = getElementFor('habilidade');

    var handleGetHabilidades = function(resources) {
      var selectOptions = jsonResourcesToSelectOptions(resources['options']);
      updateSelect($habilidadeField, selectOptions, "Todas as habilidades");
    };

    var loadHabilidades = function(){
      resetSelect($habilidadeField);
      $habilidadeField.children().first().html('Aguarde carregando...');

      var url = getResourceUrlBuilder.buildUrl('/module/DynamicInput/habilidade', 'habilidades', {});

      var options = {
        url: url,
        dataType: 'json',
        success: handleGetHabilidades
      };

      getResources(options);

      $habilidadeField.change();
    };

    // Espelha a seleção da habilidade no campo Código.
    $habilidadeField.change(function(){
      if (!$codigoField.length) {
        return;
      }

      var value = $habilidadeField.val();

      if ($codigoField.val() !== value) {
        $codigoField.val(value);
        if ($codigoField.hasClass('chzn-done')) {
          $codigoField.trigger('liszt:updated');
        }
      }
    });

    loadHabilidades();

  });
})(jQuery);
