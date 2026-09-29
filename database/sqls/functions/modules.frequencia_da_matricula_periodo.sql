CREATE OR REPLACE FUNCTION modules.frequencia_da_matricula_periodo(
    p_matricula_id integer,
    p_data_fim date DEFAULT NULL
)
    RETURNS double precision
    LANGUAGE plpgsql
AS $function$
DECLARE
    v_data_fim date;
    v_data_inicio date;
    v_regra_falta integer;
    v_falta_aluno_id integer;
    v_turma integer;
    v_dias_letivos_serie numeric;
    v_dias_uteis_ano numeric;
    v_dias_uteis_periodo numeric;
    v_dias_letivos_periodo numeric;
    v_fracao numeric;
    v_total_faltas integer;
    v_qtd_horas_serie numeric;
    v_qtd_horas_periodo numeric;
    v_total_hora_falta float;
    v_ano_inicio date;
    v_ano_fim date;
BEGIN
    /*
        Calcula a frequência da matrícula de forma PROPORCIONAL ao período
        cursado (data da enturmação até a data de emissão do atestado),
        usando como base os dias letivos da série.

        A fração do período é a razão entre os dias úteis (segunda a sexta)
        do intervalo cursado e os dias úteis do calendário escolar (datas das
        etapas). Essa fração é aplicada sobre os dias letivos da série, sem
        depender do preenchimento de dias letivos nas etapas.
    */
    v_data_fim := COALESCE(p_data_fim, CURRENT_DATE);

    /*
        v_regra_falta:
        1 - Global
        2 - Por componente
    */
    SELECT ra.tipo_presenca
    INTO v_regra_falta
    FROM pmieducar.matricula
             INNER JOIN pmieducar.serie ON serie.cod_serie = matricula.ref_ref_cod_serie
             INNER JOIN modules.regra_avaliacao_serie_ano rasa
                        ON serie.cod_serie = rasa.serie_id AND rasa.ano_letivo = matricula.ano
             INNER JOIN modules.regra_avaliacao ra ON ra.id = rasa.regra_avaliacao_id
    WHERE matricula.cod_matricula = p_matricula_id;

    SELECT id
    INTO v_falta_aluno_id
    FROM modules.falta_aluno
    WHERE matricula_id = p_matricula_id
    ORDER BY id DESC
    LIMIT 1;

    SELECT mt.ref_cod_turma,
           COALESCE(mt.data_enturmacao, m.data_matricula, m.data_cadastro::date)
    INTO v_turma, v_data_inicio
    FROM pmieducar.matricula_turma mt
             INNER JOIN pmieducar.matricula m ON m.cod_matricula = mt.ref_cod_matricula
    WHERE mt.ref_cod_matricula = p_matricula_id
      AND mt.ativo = 1
    ORDER BY mt.data_enturmacao DESC, mt.sequencial DESC
    LIMIT 1;

    IF v_turma IS NULL THEN
        SELECT mt.ref_cod_turma,
               COALESCE(mt.data_enturmacao, m.data_matricula, m.data_cadastro::date)
        INTO v_turma, v_data_inicio
        FROM pmieducar.matricula_turma mt
                 INNER JOIN pmieducar.matricula m ON m.cod_matricula = mt.ref_cod_matricula
        WHERE mt.ref_cod_matricula = p_matricula_id
        ORDER BY mt.data_enturmacao DESC, mt.sequencial DESC
        LIMIT 1;
    END IF;

    IF v_data_inicio IS NULL THEN
        SELECT COALESCE(m.data_matricula, m.data_cadastro::date)
        INTO v_data_inicio
        FROM pmieducar.matricula m
        WHERE m.cod_matricula = p_matricula_id;
    END IF;

    SELECT s.dias_letivos,
           s.carga_horaria
    INTO v_dias_letivos_serie, v_qtd_horas_serie
    FROM pmieducar.serie s
             INNER JOIN pmieducar.matricula m ON m.ref_ref_cod_serie = s.cod_serie
    WHERE m.cod_matricula = p_matricula_id;

    IF v_dias_letivos_serie IS NULL OR v_dias_letivos_serie <= 0 THEN
        RETURN NULL;
    END IF;

    /*
        Limites do calendário escolar: apenas as datas das etapas
        (ano_letivo_modulo / turma_modulo). Os dias letivos das etapas
        não entram no cálculo.
    */
    IF v_turma IS NOT NULL THEN
        SELECT MIN(e.data_inicio),
               MAX(e.data_fim)
        INTO v_ano_inicio, v_ano_fim
        FROM (
                 SELECT CASE WHEN c.padrao_ano_escolar = 0 THEN tm.data_inicio ELSE alm.data_inicio END AS data_inicio,
                        CASE WHEN c.padrao_ano_escolar = 0 THEN tm.data_fim ELSE alm.data_fim END AS data_fim
                 FROM pmieducar.turma t
                          INNER JOIN pmieducar.curso c ON c.cod_curso = t.ref_cod_curso
                          LEFT JOIN pmieducar.turma_modulo tm
                                    ON tm.ref_cod_turma = t.cod_turma AND c.padrao_ano_escolar = 0
                          LEFT JOIN pmieducar.ano_letivo_modulo alm
                                    ON alm.ref_ano = t.ano
                                        AND alm.ref_ref_cod_escola = t.ref_ref_cod_escola
                                        AND c.padrao_ano_escolar = 1
                 WHERE t.cod_turma = v_turma
             ) e
        WHERE e.data_inicio IS NOT NULL
          AND e.data_fim IS NOT NULL;
    END IF;

    IF v_ano_inicio IS NULL OR v_ano_fim IS NULL THEN
        SELECT make_date(m.ano, 1, 1),
               make_date(m.ano, 12, 31)
        INTO v_ano_inicio, v_ano_fim
        FROM pmieducar.matricula m
        WHERE m.cod_matricula = p_matricula_id;
    END IF;

    SELECT count(*)::numeric
    INTO v_dias_uteis_ano
    FROM generate_series(v_ano_inicio, v_ano_fim, interval '1 day') d
    WHERE extract(isodow FROM d) < 6;

    SELECT count(*)::numeric
    INTO v_dias_uteis_periodo
    FROM generate_series(
                 GREATEST(v_data_inicio, v_ano_inicio),
                 LEAST(v_data_fim, v_ano_fim),
                 interval '1 day'
         ) d
    WHERE extract(isodow FROM d) < 6;

    IF v_dias_uteis_ano IS NULL OR v_dias_uteis_ano <= 0
        OR v_dias_uteis_periodo IS NULL OR v_dias_uteis_periodo <= 0 THEN
        RETURN NULL;
    END IF;

    v_fracao := v_dias_uteis_periodo / v_dias_uteis_ano;
    v_dias_letivos_periodo := v_dias_letivos_serie * v_fracao;

    IF (v_regra_falta = 1) THEN
        SELECT COALESCE(SUM(fg.quantidade), 0)
        INTO v_total_faltas
        FROM modules.falta_geral fg
        WHERE fg.falta_aluno_id = v_falta_aluno_id
          AND EXISTS (
              SELECT 1
              FROM (
                       SELECT CASE WHEN c.padrao_ano_escolar = 0 THEN tm.sequencial ELSE alm.sequencial END AS seq,
                              CASE WHEN c.padrao_ano_escolar = 0 THEN tm.data_inicio ELSE alm.data_inicio END AS data_inicio,
                              CASE WHEN c.padrao_ano_escolar = 0 THEN tm.data_fim ELSE alm.data_fim END AS data_fim
                       FROM pmieducar.turma t
                                INNER JOIN pmieducar.curso c ON c.cod_curso = t.ref_cod_curso
                                LEFT JOIN pmieducar.turma_modulo tm
                                          ON tm.ref_cod_turma = t.cod_turma AND c.padrao_ano_escolar = 0
                                LEFT JOIN pmieducar.ano_letivo_modulo alm
                                          ON alm.ref_ano = t.ano
                                              AND alm.ref_ref_cod_escola = t.ref_ref_cod_escola
                                              AND c.padrao_ano_escolar = 1
                       WHERE t.cod_turma = v_turma
                   ) et
              WHERE et.seq::varchar = fg.etapa
                AND et.data_inicio IS NOT NULL
                AND et.data_inicio <= v_data_fim
                AND (et.data_fim IS NULL OR et.data_fim >= v_data_inicio)
          );

        RETURN TRUNC(
            (((v_dias_letivos_periodo - v_total_faltas) * 100) / v_dias_letivos_periodo)::numeric,
            1
        );
    ELSE
        IF v_qtd_horas_serie IS NULL OR v_qtd_horas_serie <= 0 THEN
            RETURN NULL;
        END IF;

        v_qtd_horas_periodo := v_qtd_horas_serie * v_fracao;

        IF v_qtd_horas_periodo IS NULL OR v_qtd_horas_periodo <= 0 THEN
            RETURN NULL;
        END IF;

        SELECT COALESCE(SUM(sub_totais.totais), 0)
        INTO v_total_hora_falta
        FROM (
                 SELECT SUM(fcc.quantidade)
                            * (modules.hora_falta_por_componente(p_matricula_id, fcc.componente_curricular_id)::float * 100)::float AS totais
                 FROM modules.falta_componente_curricular fcc
                 WHERE fcc.falta_aluno_id = v_falta_aluno_id
                   AND EXISTS (
                       SELECT 1
                       FROM (
                                SELECT CASE WHEN c.padrao_ano_escolar = 0 THEN tm.sequencial ELSE alm.sequencial END AS seq,
                                       CASE WHEN c.padrao_ano_escolar = 0 THEN tm.data_inicio ELSE alm.data_inicio END AS data_inicio,
                                       CASE WHEN c.padrao_ano_escolar = 0 THEN tm.data_fim ELSE alm.data_fim END AS data_fim
                                FROM pmieducar.turma t
                                         INNER JOIN pmieducar.curso c ON c.cod_curso = t.ref_cod_curso
                                         LEFT JOIN pmieducar.turma_modulo tm
                                                   ON tm.ref_cod_turma = t.cod_turma AND c.padrao_ano_escolar = 0
                                         LEFT JOIN pmieducar.ano_letivo_modulo alm
                                                   ON alm.ref_ano = t.ano
                                                       AND alm.ref_ref_cod_escola = t.ref_ref_cod_escola
                                                       AND c.padrao_ano_escolar = 1
                                WHERE t.cod_turma = v_turma
                            ) et
                       WHERE et.seq::varchar = fcc.etapa
                         AND et.data_inicio IS NOT NULL
                         AND et.data_inicio <= v_data_fim
                         AND (et.data_fim IS NULL OR et.data_fim >= v_data_inicio)
                   )
                 GROUP BY fcc.componente_curricular_id
             ) sub_totais;

        RETURN TRUNC((100 - (v_total_hora_falta / v_qtd_horas_periodo))::numeric, 1);
    END IF;
END;
$function$;
