(function($){
  $(document).ready(function(){

    var $serieField = getElementFor('serie_prova');
    var $disciplinaField = getElementFor('disciplina_prova');
    var $provaField = getElementFor('prova');
    var $eixoField = getElementFor('eixo_conhecimento');

    if (!$eixoField.length) {
      return;
    }

    $eixoField.attr('data-no-autocomplete', 'true');

    var refreshChosen = function($field) {
      if (!$field || !$field.length) {
        return;
      }

      if ($field.hasClass('chzn-done')) {
        $field.trigger('liszt:updated');
      }

      $field.trigger('chosen:updated');
    };

    var handleGetEixos = function(resources) {
      var selectOptions = jsonResourcesToSelectOptions(resources['options']);
      updateSelect($eixoField, selectOptions, "Todos os eixos");
      $eixoField.val('');
      refreshChosen($eixoField);
      $eixoField.change();
    };

    var updateEixos = function(){
      resetSelect($eixoField);
      $eixoField.val('');
      $eixoField.children().first().html('Todos os eixos');
      refreshChosen($eixoField);

      if (!($serieField.val() && $disciplinaField.val())) {
        $eixoField.change();
        return;
      }

      $eixoField.children().first().html('Aguarde carregando...');
      refreshChosen($eixoField);

      var url = getResourceUrlBuilder.buildUrl('/module/DynamicInput/prova', 'eixos', {
        serie_prova: $serieField.val(),
        disciplina_prova: $disciplinaField.val(),
        prova: $provaField.val() || ''
      });

      getResources({
        url: url,
        dataType: 'json',
        success: handleGetEixos
      });
    };

    $serieField.change(updateEixos);
    $disciplinaField.change(updateEixos);
    $provaField.change(updateEixos);

  });
})(jQuery);
