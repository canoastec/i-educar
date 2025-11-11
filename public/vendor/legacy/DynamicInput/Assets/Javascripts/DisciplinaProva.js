(function($){
  $(document).ready(function(){

    var $anoField = getElementFor('ano');
    var $serieField = getElementFor('serie_prova');
    var $disciplinaField = getElementFor('disciplina_prova');
    var $provaField = getElementFor('prova');

    var handleGetDisciplinas = function(resources) {
      var selectOptions = jsonResourcesToSelectOptions(resources['options']);
      updateSelect($disciplinaField, selectOptions, "Selecione uma disciplina");
      $disciplinaField.trigger('chosen:updated');
    };

    var updateDisciplinas = function(){
      resetSelect($disciplinaField);
      resetSelect($provaField);
      $disciplinaField.trigger('chosen:updated');
      $provaField.trigger('chosen:updated');

      if ($anoField.val() && $serieField.val()) {
        $disciplinaField.children().first().html('Aguarde carregando...');

        var url = getResourceUrlBuilder.buildUrl('/module/DynamicInput/prova', 'disciplinas', {
          ano: $anoField.val(),
          serie_prova: $serieField.val()
        });

        var options = {
          url: url,
          dataType: 'json',
          success: handleGetDisciplinas
        };

        getResources(options);
      }

      $disciplinaField.change();
    };

    $anoField.change(updateDisciplinas);
    $serieField.change(updateDisciplinas);

  });
})(jQuery);




