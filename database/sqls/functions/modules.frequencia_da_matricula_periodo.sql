CREATE OR REPLACE FUNCTION modules.frequencia_da_matricula_periodo(
    p_matricula_id integer,
    p_data_fim date DEFAULT NULL
)
    RETURNS double precision
    LANGUAGE plpgsql
AS $function$
DECLARE
    v_data_fim date;
    v_data_matricula date;
    v_regra_falta integer;
    v_falta_aluno_id integer;
    v_turma integer;
    v_dias_letivos_decorridos numeric;
    v_dias_letivos_referencia numeric;
    v_fracao numeric;
    v_total_faltas integer;
    v_qtd_horas_serie numeric;
    v_qtd_horas_decorridas numeric;
    v_total_hora_falta float;
BEGIN
    /*
        Calcula a frequência da matrícula de forma PROPORCIONAL, considerando
        apenas os dias letivos decorridos entre a data da matrícula e a data de
        emissão do atestado (p_data_fim), e não o ano letivo completo.

        Como as faltas são armazenadas por etapa (e não por dia), os dias
        letivos de cada etapa são distribuídos proporcionalmente pelos dias
        úteis (segunda a sexta) da etapa, de modo que a etapa em andamento
        conte somente até a data de emissão.
    */
    v_data_fim := COALESCE(p_data_fim, CURRENT_DATE);

    SELECT COALESCE(m.data_matricula, m.data_cadastro::date)
    INTO v_data_matricula
    FROM pmieducar.matricula m
    WHERE m.cod_matricula = p_matricula_id;

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

    SELECT mt.ref_cod_turma
    INTO v_turma
    FROM pmieducar.matricula_turma mt
    WHERE mt.ref_cod_matricula = p_matricula_id
      AND mt.ativo = 1
    ORDER BY mt.data_enturmacao DESC, mt.sequencial DESC
    LIMIT 1;

    /*
        Soma dos dias letivos decorridos (da matrícula até a emissão) e dos dias
        letivos de referência (da matrícula até o fim de cada etapa), ambos
        proporcionais por dias úteis de cada etapa.
    */
    SELECT
        COALESCE(SUM(
            e.dias_letivos * (
                (SELECT count(*)::numeric
                 FROM generate_series(GREATEST(e.data_inicio, v_data_matricula),
                                      LEAST(e.data_fim, v_data_fim),
                                      interval '1 day') d
                 WHERE extract(isodow FROM d) < 6)
                / NULLIF((SELECT count(*)::numeric
                          FROM generate_series(e.data_inicio, e.data_fim, interval '1 day') d
                          WHERE extract(isodow FROM d) < 6), 0)
            )
        ), 0),
        COALESCE(SUM(
            e.dias_letivos * (
                (SELECT count(*)::numeric
                 FROM generate_series(GREATEST(e.data_inicio, v_data_matricula),
                                      e.data_fim,
                                      interval '1 day') d
                 WHERE extract(isodow FROM d) < 6)
                / NULLIF((SELECT count(*)::numeric
                          FROM generate_series(e.data_inicio, e.data_fim, interval '1 day') d
                          WHERE extract(isodow FROM d) < 6), 0)
            )
        ), 0)
    INTO v_dias_letivos_decorridos, v_dias_letivos_referencia
    FROM (
             SELECT
                 CASE WHEN c.padrao_ano_escolar = 0 THEN tm.data_inicio ELSE alm.data_inicio END AS data_inicio,
                 CASE WHEN c.padrao_ano_escolar = 0 THEN tm.data_fim ELSE alm.data_fim END AS data_fim,
                 CASE WHEN c.padrao_ano_escolar = 0 THEN tm.dias_letivos ELSE alm.dias_letivos END AS dias_letivos
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
      AND e.data_fim IS NOT NULL
      AND e.dias_letivos IS NOT NULL;

    IF v_dias_letivos_decorridos IS NULL OR v_dias_letivos_decorridos <= 0 THEN
        RETURN NULL;
    END IF;

    IF (v_regra_falta = 1) THEN
        SELECT COALESCE(SUM(fg.quantidade), 0)
        INTO v_total_faltas
        FROM modules.falta_geral fg
        WHERE fg.falta_aluno_id = v_falta_aluno_id
          AND EXISTS (
              SELECT 1
              FROM (
                       SELECT
                           CASE WHEN c.padrao_ano_escolar = 0 THEN tm.sequencial ELSE alm.sequencial END AS seq,
                           CASE WHEN c.padrao_ano_escolar = 0 THEN tm.data_inicio ELSE alm.data_inicio END AS data_inicio
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
                AND et.data_inicio <= v_data_fim
          );

        RETURN TRUNC(
            (((v_dias_letivos_decorridos - v_total_faltas) * 100) / v_dias_letivos_decorridos)::numeric,
            1
        );
    ELSE
        SELECT s.carga_horaria
        INTO v_qtd_horas_serie
        FROM pmieducar.serie s
                 INNER JOIN pmieducar.matricula m ON m.ref_ref_cod_serie = s.cod_serie
        WHERE m.cod_matricula = p_matricula_id;

        v_fracao := v_dias_letivos_decorridos / NULLIF(v_dias_letivos_referencia, 0);
        v_qtd_horas_decorridas := v_qtd_horas_serie * v_fracao;

        IF v_qtd_horas_decorridas IS NULL OR v_qtd_horas_decorridas <= 0 THEN
            RETURN NULL;
        END IF;

        SELECT COALESCE(SUM(sub_totais.totais), 0)
        INTO v_total_hora_falta
        FROM (
                 SELECT
                     SUM(fcc.quantidade)
                         * (modules.hora_falta_por_componente(p_matricula_id, fcc.componente_curricular_id)::float * 100)::float AS totais
                 FROM modules.falta_componente_curricular fcc
                 WHERE fcc.falta_aluno_id = v_falta_aluno_id
                   AND EXISTS (
                       SELECT 1
                       FROM (
                                SELECT
                                    CASE WHEN c.padrao_ano_escolar = 0 THEN tm.sequencial ELSE alm.sequencial END AS seq,
                                    CASE WHEN c.padrao_ano_escolar = 0 THEN tm.data_inicio ELSE alm.data_inicio END AS data_inicio
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
                         AND et.data_inicio <= v_data_fim
                   )
                 GROUP BY fcc.componente_curricular_id
             ) sub_totais;

        RETURN TRUNC((100 - (v_total_hora_falta / v_qtd_horas_decorridas))::numeric, 1);
    END IF;
END;
$function$;
