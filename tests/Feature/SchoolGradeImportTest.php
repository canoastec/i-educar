<?php

namespace Tests\Feature;

use App\Services\SchoolGradeImportService;
use Database\Factories\LegacyGradeFactory;
use Database\Factories\LegacySchoolAcademicYearFactory;
use Database\Factories\LegacySchoolCourseFactory;
use Database\Factories\LegacySchoolFactory;
use Database\Factories\LegacyUserFactory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SchoolGradeImportTest extends TestCase
{
    use DatabaseTransactions;

    protected SchoolGradeImportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SchoolGradeImportService;
    }

    public function test_import_school_grade_success()
    {
        $user = LegacyUserFactory::new()->create();
        $school = LegacySchoolFactory::new()->create();
        $grade = LegacyGradeFactory::new()->create();
        $year = now()->year;

        LegacySchoolAcademicYearFactory::new()->create([
            'ref_cod_escola' => $school->cod_escola,
            'ano' => $year,
            'ativo' => 1,
            'andamento' => 1,
        ]);

        LegacySchoolCourseFactory::new()->create([
            'ref_cod_escola' => $school->cod_escola,
            'ref_cod_curso' => $grade->ref_cod_curso,
            'anos_letivos' => '{' . $year . '}',
            'ativo' => 1,
        ]);

        $params = [
            'schools' => [$school->cod_escola],
            'grades' => [$grade->cod_serie],
            'year' => $year,
            'user' => $user,
        ];

        $result = $this->service->processBatchUpdate($params);

        $this->assertEquals('completed', $result['status']);
        $this->assertEquals(1, $result['processed']);
        $this->assertEmpty($result['errors']);
        $this->assertDatabaseHas('pmieducar.escola_serie', [
            'ref_cod_escola' => $school->cod_escola,
            'ref_cod_serie' => $grade->cod_serie,
            'ativo' => 1,
        ]);
    }

    public function test_import_school_grade_with_invalid_school()
    {
        $user = LegacyUserFactory::new()->create();
        $grade = LegacyGradeFactory::new()->create();
        $year = now()->year;

        $params = [
            'schools' => [999999],
            'grades' => [$grade->cod_serie],
            'year' => $year,
            'user' => $user,
        ];

        $result = $this->service->processBatchUpdate($params);

        $this->assertEquals('failed', $result['status']);
        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('não encontrada ou inativa', $result['errors'][0]['error']);
    }

    public function test_import_multiple_schools_and_grades_success()
    {
        $user = LegacyUserFactory::new()->create();
        $school1 = LegacySchoolFactory::new()->create();
        $school2 = LegacySchoolFactory::new()->create();
        $grade1 = LegacyGradeFactory::new()->create();
        $grade2 = LegacyGradeFactory::new()->create();
        $year = now()->year;

        foreach ([$school1, $school2] as $school) {
            LegacySchoolAcademicYearFactory::new()->create([
                'ref_cod_escola' => $school->cod_escola,
                'ano' => $year,
                'ativo' => 1,
                'andamento' => 1,
            ]);
            foreach ([$grade1, $grade2] as $grade) {
                LegacySchoolCourseFactory::new()->create([
                    'ref_cod_escola' => $school->cod_escola,
                    'ref_cod_curso' => $grade->ref_cod_curso,
                    'anos_letivos' => '{' . $year . '}',
                    'ativo' => 1,
                ]);
            }
        }

        $params = [
            'schools' => [$school1->cod_escola, $school2->cod_escola],
            'grades' => [$grade1->cod_serie, $grade2->cod_serie],
            'year' => $year,
            'user' => $user,
        ];

        $result = $this->service->processBatchUpdate($params);

        $this->assertEquals('completed', $result['status']);
        $this->assertEquals(4, $result['processed']);
        $this->assertEmpty($result['errors']);
    }

    public function test_import_school_grade_with_academic_year_not_started()
    {
        $user = LegacyUserFactory::new()->create();
        $school = LegacySchoolFactory::new()->create();
        $grade = LegacyGradeFactory::new()->create();
        $year = now()->year;

        LegacySchoolAcademicYearFactory::new()->create([
            'ref_cod_escola' => $school->cod_escola,
            'ano' => $year,
            'ativo' => 1,
            'andamento' => 0,
        ]);

        LegacySchoolCourseFactory::new()->create([
            'ref_cod_escola' => $school->cod_escola,
            'ref_cod_curso' => $grade->ref_cod_curso,
            'anos_letivos' => '{' . $year . '}',
            'ativo' => 1,
        ]);

        $params = [
            'schools' => [$school->cod_escola],
            'grades' => [$grade->cod_serie],
            'year' => $year,
            'user' => $user,
        ];

        $result = $this->service->processBatchUpdate($params);

        $this->assertEquals('failed', $result['status']);
        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('não iniciado', $result['errors'][0]['error']);
    }

    public function test_import_school_grade_with_academic_year_finalized()
    {
        $user = LegacyUserFactory::new()->create();
        $school = LegacySchoolFactory::new()->create();
        $grade = LegacyGradeFactory::new()->create();
        $year = now()->year;

        LegacySchoolAcademicYearFactory::new()->create([
            'ref_cod_escola' => $school->cod_escola,
            'ano' => $year,
            'ativo' => 1,
            'andamento' => 2,
        ]);

        LegacySchoolCourseFactory::new()->create([
            'ref_cod_escola' => $school->cod_escola,
            'ref_cod_curso' => $grade->ref_cod_curso,
            'anos_letivos' => '{' . $year . '}',
            'ativo' => 1,
        ]);

        $params = [
            'schools' => [$school->cod_escola],
            'grades' => [$grade->cod_serie],
            'year' => $year,
            'user' => $user,
        ];

        $result = $this->service->processBatchUpdate($params);

        $this->assertEquals('failed', $result['status']);
        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('finalizado', $result['errors'][0]['error']);
    }

    public function test_import_school_grade_with_school_without_course()
    {
        $user = LegacyUserFactory::new()->create();
        $school = LegacySchoolFactory::new()->create();
        $grade = LegacyGradeFactory::new()->create();
        $year = now()->year;

        LegacySchoolAcademicYearFactory::new()->create([
            'ref_cod_escola' => $school->cod_escola,
            'ano' => $year,
            'ativo' => 1,
            'andamento' => 1,
        ]);

        $params = [
            'schools' => [$school->cod_escola],
            'grades' => [$grade->cod_serie],
            'year' => $year,
            'user' => $user,
        ];

        $result = $this->service->processBatchUpdate($params);

        $this->assertEquals('failed', $result['status']);
        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('não possui o ano', $result['errors'][0]['error']);
    }
}
