(function($){
  $(document).ready(function(){
    var $instituicaoField = getElementFor('instituicao');
    var $escolaField = getElementFor('escola');
    var $serieField = getElementFor('serie');
    var $turmaField = getElementFor('turma');
    var $anoField = getElementFor('ano');
    var currentRequestVersion = 0;

    var updateTurmas = function() {
      currentRequestVersion += 1;
      var requestVersion = currentRequestVersion;

      clearValues($turmaField);

      var selectedSeries = $serieField.val() || [];

      if (!Array.isArray(selectedSeries)) {
        selectedSeries = [selectedSeries];
      }

      selectedSeries = $j.grep(selectedSeries, function(serieId) {
        return !!serieId;
      });
      selectedSeries = $j.map(selectedSeries, function(serieId) {
        return String(serieId).replace(/^__/, '');
      });

      if (!$instituicaoField.val() || !$escolaField.val() || selectedSeries.length === 0) {
        return;
      }

      var requestCount = selectedSeries.length;
      var mergedTurmas = {};

      var flushTurmas = function() {
        if (requestVersion !== currentRequestVersion) {
          return;
        }

        if (requestCount === 0) {
          updateChozen($turmaField, mergedTurmas);
        }
      };

      var appendTurmas = function(response) {
        $j.each(response['options'] || {}, function(id, value) {
          mergedTurmas[String(id).replace(/^__/, '')] = value;
        });

        requestCount -= 1;
        flushTurmas();
      };

      var handleError = function() {
        requestCount -= 1;
        flushTurmas();
      };

      $j.each(selectedSeries, function(index, serieId) {
        var urlForGetTurmas = getResourceUrlBuilder.buildUrl('/module/DynamicInput/turma', 'turmas', {
          instituicao_id: $instituicaoField.val(),
          escola_id: $escolaField.val(),
          serie_id: serieId,
          ano: $anoField.val()
        });

        var options = {
          url: urlForGetTurmas,
          dataType: 'json',
          success: appendTurmas,
          error: handleError
        };

        getResources(options);
      });
    };

    $serieField.change(updateTurmas);
    $escolaField.change(updateTurmas);
    $instituicaoField.change(updateTurmas);
    $anoField.change(updateTurmas);
  });
})(jQuery);
