(function($){
  $(document).ready(function(){

    var $codigoField = getElementFor('codigo_habilidade');
    var $habilidadeField = getElementFor('habilidade');
    var $eixoField = getElementFor('eixo_conhecimento');

    $habilidadeField.attr('data-no-autocomplete', 'true');

    var refreshChosen = function($field) {
      if (!$field || !$field.length) {
        return;
      }

      if ($field.hasClass('chzn-done')) {
        $field.trigger('liszt:updated');
      }

      $field.trigger('chosen:updated');
    };

    var syncCodigoFromHabilidade = function(){
      if (!$codigoField.length) {
        return;
      }

      var value = $habilidadeField.val() || '';

      if ($codigoField.val() !== value) {
        $codigoField.val(value);
        refreshChosen($codigoField);
      }
    };

    var handleGetHabilidades = function(resources) {
      var selectOptions = jsonResourcesToSelectOptions(resources['options']);
      updateSelect($habilidadeField, selectOptions, "Todas as habilidades");
      $habilidadeField.val('');
      refreshChosen($habilidadeField);
      syncCodigoFromHabilidade();
    };

    var loadHabilidades = function(){
      if (!$habilidadeField.length) {
        return;
      }

      resetSelect($habilidadeField);
      $habilidadeField.children().first().html('Aguarde carregando...');
      refreshChosen($habilidadeField);

      var url = getResourceUrlBuilder.buildUrl('/module/DynamicInput/habilidade', 'habilidades', {
        eixo_conhecimento: $eixoField.length ? ($eixoField.val() || '') : ''
      });

      getResources({
        url: url,
        dataType: 'json',
        success: handleGetHabilidades
      });
    };

    $habilidadeField.change(syncCodigoFromHabilidade);

    if ($eixoField.length) {
      $eixoField.change(loadHabilidades);
    }

    loadHabilidades();

  });
})(jQuery);
