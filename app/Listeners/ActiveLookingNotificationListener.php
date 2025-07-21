<?php

namespace App\Listeners;

use App\Events\ActiveLookingCreated;
use App\Models\NotificationType;
use App\Process;
use App\Services\NotificationService;
use App\Traits\HasNotificationUsers;

class ActiveLookingNotificationListener
{
    use HasNotificationUsers;

    /**
     * @var NotificationService
     */
    protected $service;

    public function __construct(NotificationService $service)
    {
        $this->service = $service;
    }

    /**
     * Handle the event.
     *
     * @param ActiveLookingCreated $event
     * @return void
     */
    public function handle(ActiveLookingCreated $event)
    {
        $activeLooking = $event->activeLooking;
        $registration = $activeLooking->registration;

        $message = sprintf(
            'O(a) aluno(a) %s, %s, %s, %s, %s foi registrado em Busca Ativa.',
            $registration->student->person->name,
            $registration->school->name,
            $registration->grade->name,
            $registration->lastEnrollment->schoolClass->name,
            $registration->ano
        );

        $link = '/intranet/educar_busca_ativa_cad.php?id=' . $activeLooking->getKey() . '&ref_cod_matricula=' . $registration->getKey();

        $users = $this->getUsers(Process::ACTIVE_LOOKING, $registration->school->getKey());

        foreach ($users as $user) {
            $this->service->createByUser($user->cod_usuario, $message, $link, NotificationType::ACTIVE_LOOKING);
        }
    }
}
