<?php

namespace Tests\Unit\Services;

use App\Models\SchoolClassInep;
use App\Services\SchoolClass\SchoolClassService;
use App\Services\SchoolClassInepService;
use Database\Factories\LegacySchoolClassFactory;
use Database\Factories\SchoolClassInepFactory;
use iEducar\Modules\SchoolClass\Period;
use Tests\TestCase;

class SchoolClassInepServiceTest extends TestCase
{
    private SchoolClassInepService $service;
    private SchoolClassService $schoolClassService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->schoolClassService = $this->createMock(SchoolClassService::class);
        $this->service = new SchoolClassInepService($this->schoolClassService);
    }

    public function test_store_cria_novo_registro_inep()
    {
        $schoolClass = LegacySchoolClassFactory::new()->create();
        $codigoInep = '12345678';
        $turnoId = Period::MORNING;

        $result = $this->service->store($schoolClass->cod_turma, $codigoInep, $turnoId);

        $this->assertInstanceOf(SchoolClassInep::class, $result);
        $this->assertEquals($schoolClass->cod_turma, $result->cod_turma);
        $this->assertEquals($codigoInep, $result->cod_turma_inep);
        $this->assertEquals($turnoId, $result->turma_turno_id);
    }

    public function test_delete_remove_registro_por_turma_e_turno()
    {
        $schoolClass = LegacySchoolClassFactory::new()->create();

        SchoolClassInepFactory::new()->create([
            'cod_turma' => $schoolClass->cod_turma,
            'turma_turno_id' => Period::MORNING
        ]);

        $this->service->delete($schoolClass->cod_turma, Period::MORNING);

        $this->assertDatabaseMissing('modules.educacenso_cod_turma', [
            'cod_turma' => $schoolClass->cod_turma,
            'turma_turno_id' => Period::MORNING
        ]);
    }

    public function test_delete_com_turno_null_remove_registros_com_turno_null()
    {
        $schoolClass = LegacySchoolClassFactory::new()->create();

        SchoolClassInepFactory::new()->create([
            'cod_turma' => $schoolClass->cod_turma,
            'turma_turno_id' => null
        ]);

        $this->service->delete($schoolClass->cod_turma, null);

        $this->assertDatabaseMissing('modules.educacenso_cod_turma', [
            'cod_turma' => $schoolClass->cod_turma,
            'turma_turno_id' => null
        ]);
    }

    public function test_save_sem_ineps_parciais_turno_integral_sem_enturmacoes_parciais()
    {
        $schoolClass = LegacySchoolClassFactory::new()->create();
        $codigoInep = '12345678';

        $this->schoolClassService
            ->expects($this->once())
            ->method('hasStudentsPartials')
            ->with($schoolClass->cod_turma)
            ->willReturn(false);

        $this->service->save(
            codTurma: $schoolClass->cod_turma,
            codigoInepEducacenso: $codigoInep,
            codigoInepEducacensoMatutino: null, // matutino
            codigoInepEducacensoVespertino: null, // vespertino
            turnoId: Period::FULLTIME
        );

        $this->assertDatabaseHas('modules.educacenso_cod_turma', [
            'cod_turma' => $schoolClass->cod_turma,
            'cod_turma_inep' => $codigoInep,
            'turma_turno_id' => null
        ]);
    }

    public function test_save_sem_ineps_parciais_turno_integral_com_enturmacoes_parciais()
    {
        $schoolClass = LegacySchoolClassFactory::new()->create();
        $codigoInep = '12345678';

        $this->schoolClassService
            ->expects($this->once())
            ->method('hasStudentsPartials')
            ->with($schoolClass->cod_turma)
            ->willReturn(true);

        $this->service->save(
            codTurma: $schoolClass->cod_turma,
            codigoInepEducacenso: $codigoInep,
            codigoInepEducacensoMatutino: null, // matutino
            codigoInepEducacensoVespertino: null, // vespertino
            turnoId: Period::FULLTIME
        );

        $this->assertDatabaseHas('modules.educacenso_cod_turma', [
            'cod_turma' => $schoolClass->cod_turma,
            'cod_turma_inep' => $codigoInep,
            'turma_turno_id' => Period::FULLTIME
        ]);
    }

    public function test_save_com_ineps_parciais_sempre_tem_valor_no_turno()
    {
        $schoolClass = LegacySchoolClassFactory::new()->create();
        $codigoInepIntegral = '12345678';
        $codigoInepMatutino = '87654321';
        $codigoInepVespertino = '11223344';

        $this->schoolClassService
            ->expects($this->once())
            ->method('hasStudentsPartials')
            ->with($schoolClass->cod_turma)
            ->willReturn(true);

        $this->service->save(
            codTurma: $schoolClass->cod_turma,
            codigoInepEducacenso: $codigoInepIntegral,
            codigoInepEducacensoMatutino: $codigoInepMatutino,
            codigoInepEducacensoVespertino: $codigoInepVespertino,
            turnoId: Period::FULLTIME
        );

        $this->assertDatabaseHas('modules.educacenso_cod_turma', [
            'cod_turma' => $schoolClass->cod_turma,
            'cod_turma_inep' => $codigoInepIntegral,
            'turma_turno_id' => Period::FULLTIME
        ]);

        $this->assertDatabaseHas('modules.educacenso_cod_turma', [
            'cod_turma' => $schoolClass->cod_turma,
            'cod_turma_inep' => $codigoInepMatutino,
            'turma_turno_id' => Period::MORNING
        ]);

        $this->assertDatabaseHas('modules.educacenso_cod_turma', [
            'cod_turma' => $schoolClass->cod_turma,
            'cod_turma_inep' => $codigoInepVespertino,
            'turma_turno_id' => Period::AFTERNOON
        ]);
    }

    public function test_save_remove_inep_quando_codigo_nulo()
    {
        $schoolClass = LegacySchoolClassFactory::new()->create();

        SchoolClassInepFactory::new()->create([
            'cod_turma' => $schoolClass->cod_turma,
            'turma_turno_id' => null
        ]);

        $this->schoolClassService
            ->expects($this->never())
            ->method('hasStudentsPartials');

        $this->service->save(
            codTurma: $schoolClass->cod_turma,
            codigoInepEducacenso: null, // código INEP nulo
            codigoInepEducacensoMatutino: null,
            codigoInepEducacensoVespertino: null,
            turnoId: Period::FULLTIME
        );

        $this->assertDatabaseMissing('modules.educacenso_cod_turma', [
            'cod_turma' => $schoolClass->cod_turma,
            'turma_turno_id' => null
        ]);
    }

    public function test_save_remove_ineps_parciais_quando_codigos_nulos()
    {
        $schoolClass = LegacySchoolClassFactory::new()->create();

        SchoolClassInepFactory::new()->create([
            'cod_turma' => $schoolClass->cod_turma,
            'turma_turno_id' => Period::MORNING
        ]);

        SchoolClassInepFactory::new()->create([
            'cod_turma' => $schoolClass->cod_turma,
            'turma_turno_id' => Period::AFTERNOON
        ]);

        $this->service->save(
            codTurma: $schoolClass->cod_turma,
            codigoInepEducacenso: '12345678', // mantém integral
            codigoInepEducacensoMatutino: null, // remove matutino
            codigoInepEducacensoVespertino: null, // remove vespertino
            turnoId: Period::FULLTIME
        );

        $this->assertDatabaseMissing('modules.educacenso_cod_turma', [
            'cod_turma' => $schoolClass->cod_turma,
            'turma_turno_id' => Period::MORNING
        ]);

        $this->assertDatabaseMissing('modules.educacenso_cod_turma', [
            'cod_turma' => $schoolClass->cod_turma,
            'turma_turno_id' => Period::AFTERNOON
        ]);
    }

    public function test_save_turno_noturno_nao_eh_processado()
    {
        $schoolClass = LegacySchoolClassFactory::new()->create();
        $codigoInep = '12345678';

        $this->service->save(
            codTurma: $schoolClass->cod_turma,
            codigoInepEducacenso: $codigoInep,
            codigoInepEducacensoMatutino: null,
            codigoInepEducacensoVespertino: null,
            turnoId: Period::NIGTH // turno noturno
        );

        $this->assertDatabaseHas('modules.educacenso_cod_turma', [
            'cod_turma' => $schoolClass->cod_turma,
            'cod_turma_inep' => $codigoInep,
            'turma_turno_id' => null
        ]);
    }

    public function test_save_cenario_completo_com_todos_ineps()
    {
        $schoolClass = LegacySchoolClassFactory::new()->create();
        $codigoInepIntegral = '12345678';
        $codigoInepMatutino = '87654321';
        $codigoInepVespertino = '11223344';

        $this->schoolClassService
            ->expects($this->once())
            ->method('hasStudentsPartials')
            ->with($schoolClass->cod_turma)
            ->willReturn(true);

        $this->service->save(
            codTurma: $schoolClass->cod_turma,
            codigoInepEducacenso: $codigoInepIntegral,
            codigoInepEducacensoMatutino: $codigoInepMatutino,
            codigoInepEducacensoVespertino: $codigoInepVespertino,
            turnoId: Period::FULLTIME
        );

        $registros = SchoolClassInep::where('cod_turma', $schoolClass->cod_turma)->get();
        $this->assertCount(3, $registros);

        $turnos = $registros->pluck('turma_turno_id')->sort()->values();
        $this->assertEquals([Period::MORNING, Period::AFTERNOON, Period::FULLTIME], $turnos->toArray());
    }

    public function test_save_turno_matutino_sempre_turno_null()
    {
        $schoolClass = LegacySchoolClassFactory::new()->create();
        $codigoInep = '12345678';

        $this->schoolClassService
            ->expects($this->never())
            ->method('hasStudentsPartials');

        $this->service->save(
            codTurma: $schoolClass->cod_turma,
            codigoInepEducacenso: $codigoInep,
            codigoInepEducacensoMatutino: null,
            codigoInepEducacensoVespertino: null,
            turnoId: Period::MORNING
        );

        $this->assertDatabaseHas('modules.educacenso_cod_turma', [
            'cod_turma' => $schoolClass->cod_turma,
            'cod_turma_inep' => $codigoInep,
            'turma_turno_id' => null
        ]);
    }

    public function test_save_turno_vespertino_sempre_turno_null()
    {
        $schoolClass = LegacySchoolClassFactory::new()->create();
        $codigoInep = '12345678';

        $this->schoolClassService
            ->expects($this->never())
            ->method('hasStudentsPartials');

        $this->service->save(
            codTurma: $schoolClass->cod_turma,
            codigoInepEducacenso: $codigoInep,
            codigoInepEducacensoMatutino: null,
            codigoInepEducacensoVespertino: null,
            turnoId: Period::AFTERNOON
        );

        $this->assertDatabaseHas('modules.educacenso_cod_turma', [
            'cod_turma' => $schoolClass->cod_turma,
            'cod_turma_inep' => $codigoInep,
            'turma_turno_id' => null
        ]);
    }

    public function test_save_apenas_ineps_parciais_sem_principal()
    {
        $schoolClass = LegacySchoolClassFactory::new()->create();
        $codigoInepMatutino = '87654321';
        $codigoInepVespertino = '11223344';

        $this->schoolClassService
            ->expects($this->never())
            ->method('hasStudentsPartials');

        $this->service->save(
            codTurma: $schoolClass->cod_turma,
            codigoInepEducacenso: null, // sem INEP principal
            codigoInepEducacensoMatutino: $codigoInepMatutino,
            codigoInepEducacensoVespertino: $codigoInepVespertino,
            turnoId: Period::FULLTIME
        );

        $registros = SchoolClassInep::where('cod_turma', $schoolClass->cod_turma)->get();
        $this->assertCount(2, $registros);

        $this->assertDatabaseHas('modules.educacenso_cod_turma', [
            'cod_turma' => $schoolClass->cod_turma,
            'cod_turma_inep' => $codigoInepMatutino,
            'turma_turno_id' => Period::MORNING
        ]);

        $this->assertDatabaseHas('modules.educacenso_cod_turma', [
            'cod_turma' => $schoolClass->cod_turma,
            'cod_turma_inep' => $codigoInepVespertino,
            'turma_turno_id' => Period::AFTERNOON
        ]);
    }

    public function test_save_remove_todos_quando_todos_codigos_nulos()
    {
        $schoolClass = LegacySchoolClassFactory::new()->create();

        SchoolClassInepFactory::new()->create([
            'cod_turma' => $schoolClass->cod_turma,
            'turma_turno_id' => null
        ]);

        SchoolClassInepFactory::new()->create([
            'cod_turma' => $schoolClass->cod_turma,
            'turma_turno_id' => Period::MORNING
        ]);

        SchoolClassInepFactory::new()->create([
            'cod_turma' => $schoolClass->cod_turma,
            'turma_turno_id' => Period::AFTERNOON
        ]);

        $this->schoolClassService
            ->expects($this->never())
            ->method('hasStudentsPartials');

        $this->service->save(
            codTurma: $schoolClass->cod_turma,
            codigoInepEducacenso: null, // remove principal
            codigoInepEducacensoMatutino: null, // remove matutino
            codigoInepEducacensoVespertino: null, // remove vespertino
            turnoId: Period::FULLTIME
        );

        $registros = SchoolClassInep::where('cod_turma', $schoolClass->cod_turma)->get();
        $this->assertCount(0, $registros);
    }

    public function test_save_mantem_apenas_um_inep_parcial()
    {
        $schoolClass = LegacySchoolClassFactory::new()->create();
        $codigoInepMatutino = '87654321';

        SchoolClassInepFactory::new()->create([
            'cod_turma' => $schoolClass->cod_turma,
            'turma_turno_id' => null
        ]);

        SchoolClassInepFactory::new()->create([
            'cod_turma' => $schoolClass->cod_turma,
            'turma_turno_id' => Period::AFTERNOON
        ]);

        $this->service->save(
            codTurma: $schoolClass->cod_turma,
            codigoInepEducacenso: null, // remove principal
            codigoInepEducacensoMatutino: $codigoInepMatutino, // mantém apenas matutino
            codigoInepEducacensoVespertino: null, // remove vespertino
            turnoId: Period::FULLTIME
        );

        $registros = SchoolClassInep::where('cod_turma', $schoolClass->cod_turma)->get();
        $this->assertCount(1, $registros);

        $this->assertDatabaseHas('modules.educacenso_cod_turma', [
            'cod_turma' => $schoolClass->cod_turma,
            'cod_turma_inep' => $codigoInepMatutino,
            'turma_turno_id' => Period::MORNING
        ]);

        $this->assertDatabaseMissing('modules.educacenso_cod_turma', [
            'cod_turma' => $schoolClass->cod_turma,
            'turma_turno_id' => null
        ]);

        $this->assertDatabaseMissing('modules.educacenso_cod_turma', [
            'cod_turma' => $schoolClass->cod_turma,
            'turma_turno_id' => Period::AFTERNOON
        ]);
    }

    public function test_delete_nao_remove_outros_turnos()
    {
        $schoolClass = LegacySchoolClassFactory::new()->create();

        SchoolClassInepFactory::new()->create([
            'cod_turma' => $schoolClass->cod_turma,
            'turma_turno_id' => Period::MORNING
        ]);

        SchoolClassInepFactory::new()->create([
            'cod_turma' => $schoolClass->cod_turma,
            'turma_turno_id' => Period::AFTERNOON
        ]);

        $this->service->delete($schoolClass->cod_turma, Period::MORNING);

        $this->assertDatabaseHas('modules.educacenso_cod_turma', [
            'cod_turma' => $schoolClass->cod_turma,
            'turma_turno_id' => Period::AFTERNOON
        ]);

        $this->assertDatabaseMissing('modules.educacenso_cod_turma', [
            'cod_turma' => $schoolClass->cod_turma,
            'turma_turno_id' => Period::MORNING
        ]);
    }
}
