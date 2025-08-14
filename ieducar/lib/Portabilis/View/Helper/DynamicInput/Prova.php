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

        $turmaId = $this->getSerieId($options['turmaId'] ?? null);

        if ($turmaId) {
            $resources = \Canoastec\Canoastec\Models\AppliedExam::query()
                ->where('school_class_id', $turmaId)
                ->pluck('description', 'id');
        }

        return $this->insertOption(null, 'Selecione uma prova', $resources);
    }

    public function prova($options = [])
    {
        parent::select($options);
    }
}
