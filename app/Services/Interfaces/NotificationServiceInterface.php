<?php

namespace App\Services\Interfaces;

use App\Models\TravelRequest;
use App\Models\User;

interface NotificationServiceInterface
{
    /**
     * Envia uma notificação de mudança de status para o usuário que fez a solicitação de viagem
     *
     * @param TravelRequest $travelRequest
     * @return void
     */
    public function sendStatusChangeNotification(TravelRequest $travelRequest): void;
    
    /**
     * Envia uma notificação genérica para um usuário
     *
     * @param User $user
     * @param string $subject
     * @param string $message
     * @return void
     */
    public function sendNotificationToUser(User $user, string $subject, string $message): void;
    
    /**
     * Envia uma notificação para múltiplos usuários
     *
     * @param array $users
     * @param string $subject
     * @param string $message
     * @return void
     */
    public function sendNotificationToMultipleUsers(array $users, string $subject, string $message): void;
} 