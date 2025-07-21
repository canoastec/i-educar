<?php

namespace App\Services;

use App\Models\Message;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessageService
{
    public function createMessage(string $messageableType, int $messageableId, int $userId, string $description): Message
    {
        return DB::transaction(function () use ($messageableType, $messageableId, $userId, $description) {
            $message = Message::create([
                'messageable_type' => $messageableType,
                'messageable_id' => $messageableId,
                'user_id' => $userId,
                'description' => $description,
            ]);

            $message->load('user');

            return $message;
        });
    }

    public function updateMessage(int $messageId, int $userId, string $description): Message
    {
        return DB::transaction(function () use ($messageId, $userId, $description) {
            $message = Message::findOrFail($messageId);

            if (!$this->canEditMessage($message, $userId)) {
                throw new \InvalidArgumentException('Você não tem permissão para editar esta mensagem');
            }

            $message->update(['description' => $description]);

            $message->load('user');

            return $message;
        });
    }

    public function deleteMessage(int $messageId, int $userId): bool
    {
        return DB::transaction(function () use ($messageId, $userId) {
            $message = Message::findOrFail($messageId);

            if (!$this->canDeleteMessage($message, $userId)) {
                throw new \InvalidArgumentException('Você não tem permissão para excluir esta mensagem');
            }

            return $message->delete();
        });
    }

    public function findMessage(int $messageId): ?Message
    {
        return Message::with('user')->find($messageId);
    }

    public function getMessages(string $messageableType, int $messageableId): \Illuminate\Database\Eloquent\Collection
    {
        return Message::with('user')
            ->where('messageable_type', $messageableType)
            ->where('messageable_id', $messageableId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    private function canEditMessage(Message $message, int $userId): bool
    {
        if (is_null($message->user_id)) {
            return $this->isPoliInstitutionalUser($userId);
        }

        if ($message->user_id === $userId) {
            return true;
        }

        return $this->isPoliInstitutionalUser($userId);
    }

    private function canDeleteMessage(Message $message, int $userId): bool
    {
        return $this->canEditMessage($message, $userId);
    }

    private function isPoliInstitutionalUser(int $userId): bool
    {
        if ($userId === Auth::id()) {
            return Auth::user()->isAdmin();
        }

        $user = User::find($userId);
        return $user && $user->isAdmin();
    }
}
