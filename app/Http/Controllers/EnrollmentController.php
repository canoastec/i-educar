<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnrollmentRequest;
use App\Models\LegacyRegistration;
use App\Models\LegacySchoolClass;
use App\Services\EnrollmentService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Throwable;

class EnrollmentController extends Controller
{
    /**
     * Renderiza a view da enturmação.
     *
     *
     * @return View
     */
    public function viewEnroll(
        LegacyRegistration $registration,
        LegacySchoolClass $schoolClass,
        EnrollmentService $enrollmentService
    ) {
        $this->breadcrumb('Enturmar matrícula', [
            url('intranet/educar_index.php') => 'Escola',
        ]);

        $enrollments = $registration->enrollments()->active()->get();
        $enableCancelButton = $enrollments->where('schoolClass.id', $schoolClass->id)->isNotEmpty();

        if ($enableCancelButton) {
            $hasPermission = Gate::allows('view', 696);

            if (!$hasPermission) {
                return redirect()->back()->with('error', 'Você não tem permissão para desenturmar alunos.');
            }
        }

        $this->menu(578);

        $anotherClassroomEnrollments = $enrollmentService->anotherClassroomEnrollments($schoolClass, $registration);

        return view('enrollments.enroll', [
            'registration' => $registration,
            'enrollments' => $enrollments,
            'schoolClass' => $schoolClass,
            'enableCancelButton' => $enableCancelButton,
            'anotherClassroomEnrollments' => $anotherClassroomEnrollments,
        ]);
    }

    /**
     * @return RedirectResponse
     */
    public function enroll(
        EnrollmentService $enrollmentService,
        EnrollmentRequest $request,
        LegacyRegistration $registration,
        LegacySchoolClass $schoolClass
    ) {
        DB::beginTransaction();
        $date = Carbon::createFromFormat('d/m/Y', $request->input('enrollment_date'));

        try {
            // Se for desenturmação (cancelamento)
            if ($request->input('is_cancellation')) {
                $enrollmentFromId = $request->input('enrollment_from_id');
                $enrollment = $registration->enrollments()->whereKey($enrollmentFromId)->firstOrFail();

                $enrollmentService->cancelEnrollment($enrollment, $date);
                $successMessage = 'Desenturmação realizada com sucesso.';
            } else {
                $enrollmentService->enroll($registration, $schoolClass, $date, false);
                $successMessage = 'Enturmação realizada com sucesso.';
            }

            DB::commit();
            return redirect('/intranet/educar_matricula_det.php?cod_matricula=' . $registration->id)
                ->with('success', $successMessage);

        } catch (Throwable $throwable) {
            DB::rollback();
            return redirect()->back()->with('error', $throwable->getMessage());
        }
    }

    /**
     * Renderiza a view do remanejamento.
     *
     * @return View
     */
    public function viewRelocate(
        LegacyRegistration $registration,
        LegacySchoolClass $schoolClass,
        EnrollmentService $enrollmentService
    ) {
        $this->breadcrumb('Remanejar matrícula', [
            url('intranet/educar_index.php') => 'Escola',
        ]);

        $this->menu(578);

        $enableCancelButton = $enrollmentService->isEnrolled($schoolClass, $registration);
        $anotherClassroomEnrollments = $enrollmentService->anotherClassroomEnrollments($schoolClass, $registration);

        return view('enrollments.relocate', [
            'registration' => $registration,
            'enrollments' => $registration->activeEnrollments()->get(),
            'schoolClass' => $schoolClass,
            'enableCancelButton' => $enableCancelButton,
            'anotherClassroomEnrollments' => $anotherClassroomEnrollments,
        ]);
    }

    /**
     * Realiza remanejamento (transferência entre turmas)
     *
     * @return RedirectResponse
     */
    public function relocate(
        EnrollmentService $enrollmentService,
        EnrollmentRequest $request,
        LegacyRegistration $registration,
        LegacySchoolClass $schoolClass
    ) {
        DB::beginTransaction();
        $date = Carbon::createFromFormat('d/m/Y', $request->input('enrollment_date'));

        // Para remanejamento, sempre precisa ter uma enturmação de origem
        $enrollmentFromId = $request->input('enrollment_from_id');
        if (!$enrollmentFromId) {
            return redirect()->back()->with('error', 'É necessário selecionar a turma de origem para o remanejamento.');
        }

        $enrollment = $registration->enrollments()->whereKey($enrollmentFromId)->firstOrFail();

        try {
            // 1. Cancela a enturmação atual
            $enrollmentService->cancelEnrollment($enrollment, $date);

            // 2. Busca a enturmação anterior para marcar como remanejada
            $previousEnrollment = $enrollmentService->getPreviousEnrollmentAccordingToRelocationDate($registration);

            $isRelocatedSameClassGroup = false;
            if ($previousEnrollment !== null && $previousEnrollment->school_class_id === $schoolClass->id) {
                $isRelocatedSameClassGroup = true;
                $enrollmentService->markAsRelocatedSameClassGroup($previousEnrollment);
            }

            // 3. Marca como remanejada e reordena se for turma diferente
            if ($previousEnrollment && $previousEnrollment->school_class_id !== $schoolClass->id) {
                $enrollmentService->markAsRelocated($previousEnrollment);
                $enrollmentService->reorderSchoolClassAccordingToRelocationDate($previousEnrollment);
            }

            // 4. Enturma na nova turma
            $enrollmentService->enroll($registration, $schoolClass, $date, $isRelocatedSameClassGroup);

            DB::commit();
            return redirect('/intranet/educar_matricula_det.php?cod_matricula=' . $registration->id)
                ->with('success', 'Remanejamento realizado com sucesso.');

        } catch (Throwable $throwable) {
            DB::rollback();
            return redirect()->back()->with('error', $throwable->getMessage());
        }
    }
}
