(function($){
  $(document).ready(function(){

    var $disciplinaField = getElementFor('disciplina_habilidade');

    var handleGetDisciplinas = function(resources) {
      var selectOptions = jsonResourcesToSelectOptions(resources['options']);
      updateSelect($disciplinaField, selectOptions, "Todas as disciplinas");
      $disciplinaField.trigger('chosen:updated');
    };

    var updateDisciplinas = function(){
      resetSelect($disciplinaField);
      $disciplinaField.children().first().html('Aguarde carregando...');

      var url = getResourceUrlBuilder.buildUrl('/module/DynamicInput/habilidade', 'disciplinas', {});

      var options = {
        url: url,
        dataType: 'json',
        success: handleGetDisciplinas
      };

      getResources(options);

      $disciplinaField.change();
    };

    updateDisciplinas();

  });
})(jQuery);
