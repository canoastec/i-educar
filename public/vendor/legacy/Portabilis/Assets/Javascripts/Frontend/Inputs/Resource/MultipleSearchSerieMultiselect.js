(function($){
  $(document).ready(function(){
    var $instituicaoField = getElementFor('instituicao');
    var $escolaField = getElementFor('escola');
    var $cursoField = getElementFor('curso');
    var $serieField = getElementFor('serie');
    var $turmaField = getElementFor('turma');
    var $anoField = getElementFor('ano');

    var clearDependentFields = function() {
      clearValues($serieField);
      clearValues($turmaField);
    };

    var handleGetSeries = function(response) {
      var normalizedOptions = {};

      $j.each(response['options'] || {}, function(id, value) {
        normalizedOptions[id.replace(/^__/, '')] = value;
      });

      updateChozen($serieField, normalizedOptions);
      $serieField.trigger('change');
    };

    var updateSeries = function() {
      clearDependentFields();

      if ($instituicaoField.val() && $escolaField.val() && $cursoField.val() && $cursoField.val().length) {
        var urlForGetSeries = getResourceUrlBuilder.buildUrl('/module/DynamicInput/serie', 'series', {
          instituicao_id: $instituicaoField.val(),
          escola_id: $escolaField.val(),
          curso_id: $cursoField.val(),
          ano: $anoField.val()
        });

        var options = {
          url: urlForGetSeries,
          dataType: 'json',
          success: handleGetSeries
        };

        getResources(options);
      }
    };

    $cursoField.change(updateSeries);
    $escolaField.change(updateSeries);
    $instituicaoField.change(updateSeries);
    $anoField.change(updateSeries);
  });
})(jQuery);
