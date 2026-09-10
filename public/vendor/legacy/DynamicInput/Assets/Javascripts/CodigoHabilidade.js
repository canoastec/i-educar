(function($){
  $(document).ready(function(){

    var $codigoField = getElementFor('codigo_habilidade');
    var $habilidadeField = getElementFor('habilidade');
    var $eixoField = getElementFor('eixo_conhecimento');

    $codigoField.attr('data-no-autocomplete', 'true');

    var refreshChosen = function($field) {
      if (!$field || !$field.length) {
        return;
      }

      if ($field.hasClass('chzn-done')) {
        $field.trigger('liszt:updated');
      }

      $field.trigger('chosen:updated');
    };

    var syncHabilidadeFromCodigo = function(){
      if (!$habilidadeField.length) {
        return;
      }

      var value = $codigoField.val() || '';

      if ($habilidadeField.val() !== value) {
        $habilidadeField.val(value);
        refreshChosen($habilidadeField);
      }
    };

    var handleGetCodigos = function(resources) {
      var selectOptions = jsonResourcesToSelectOptions(resources['options']);
      updateSelect($codigoField, selectOptions, "Todos os códigos");
      $codigoField.val('');
      refreshChosen($codigoField);
      syncHabilidadeFromCodigo();
    };

    var loadCodigos = function(){
      if (!$codigoField.length) {
        return;
      }

      resetSelect($codigoField);
      $codigoField.children().first().html('Aguarde carregando...');
      refreshChosen($codigoField);

      var url = getResourceUrlBuilder.buildUrl('/module/DynamicInput/habilidade', 'codigos', {
        eixo_conhecimento: $eixoField.length ? ($eixoField.val() || '') : ''
      });

      getResources({
        url: url,
        dataType: 'json',
        success: handleGetCodigos
      });
    };

    $codigoField.change(syncHabilidadeFromCodigo);

    if ($eixoField.length) {
      $eixoField.change(loadCodigos);
    }

    loadCodigos();

  });
})(jQuery);
