<?php

namespace iEducar\Modules\Educacenso\Model;

class Transtornos
{
    public const DISCALCULIA = 1;

    public const DISGRAFIA = 2;

    public const DISLALIA = 3;

    public const DISLEXIA = 4;

    public const TDAH = 5;

    public const TPAC = 6;

    public const OUTROS = 999;

    public static function getDescriptiveValues()
    {
        return [
            self::DISCALCULIA => 'Discalculia ou outro transtorno da matemática e raciocínio lógico',
            self::DISGRAFIA => 'Disgrafia, Disortografia ou outro transtorno da escrita e ortografia',
            self::DISLALIA => 'Dislalia ou outro transtorno da linguagem e comunicação',
            self::DISLEXIA => 'Dislexia',
            self::TDAH => 'Transtorno do Déficit de Atenção com Hiperatividade (TDAH)',
            self::TPAC => 'Transtorno do Processamento Auditivo Central (TPAC)',
            self::OUTROS => 'Outros',
        ];
    }
}
