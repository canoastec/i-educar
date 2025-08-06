<?php

use Canoastec\Canoastec\Models\Exam;

class Portabilis_View_Helper_DynamicInput_Prova extends Portabilis_View_Helper_DynamicInput_CoreSelect
{
    protected function inputName()
    {
        return 'ref_cod_prova';
    }

    protected function inputOptions($options)
    {
        $resources = $options['resources'];

        $serieId = $this->getSerieId($options['serieId'] ?? null);
        $cursoId = $this->getCursoId($options['cursoId'] ?? null);

        if (empty($resources)) {
            $query = Exam::query();

            if ($serieId) {
                $query->where('grade_id', $serieId);
            }

            if ($cursoId) {
                $query->where('discipline_id', $cursoId);
            }

            $resources = $query->pluck('description', 'id');
        }

        return $this->insertOption(null, 'Selecione uma prova', $resources);
    }

    public function prova($options = [])
    {
        parent::select($options);
    }
}
