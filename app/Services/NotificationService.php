<?php

namespace App\Services;

use App\Models\TravelRequest;
use App\Models\User;
use App\Notifications\TravelRequestStatusChanged;
use App\Notifications\GenericNotification;
use App\Services\Interfaces\NotificationServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use App\Mail\CancellationRequested;
use App\Mail\CancellationApproved;
use App\Mail\CancellationRejected;

class NotificationService implements NotificationServiceInterface
{
    /**
     * Envia uma notificação de mudança de status para o usuário que fez a solicitação de viagem
     *
     * @param TravelRequest $travelRequest
     * @return void
     */
    public function sendStatusChangeNotification(TravelRequest $travelRequest): void
    {
        try {
            // Busca o usuário que fez a solicitação
            $user = User::find($travelRequest->user_id);
            
            if (!$user) {
                Log::warning("Não foi possível enviar notificação: usuário não encontrado (ID: {$travelRequest->user_id})");
                return;
            }
            
            // Envia a notificação usando a classe TravelRequestStatusChanged
            $user->notify(new TravelRequestStatusChanged($travelRequest));
            
            Log::info("Notificação de mudança de status enviada para o usuário {$user->name} (ID: {$user->id})");
        } catch (\Exception $e) {
            Log::error("Erro ao enviar notificação de mudança de status: " . $e->getMessage());
        }
    }
    
    /**
     * Envia uma notificação genérica para um usuário
     *
     * @param User $user
     * @param string $subject
     * @param string $message
     * @return void
     */
    public function sendNotificationToUser(User $user, string $subject, string $message): void
    {
        try {
            $user->notify(new GenericNotification($subject, $message));
            
            Log::info("Notificação genérica enviada para o usuário {$user->name} (ID: {$user->id})");
        } catch (\Exception $e) {
            Log::error("Erro ao enviar notificação genérica: " . $e->getMessage());
        }
    }
    
    /**
     * Envia uma notificação para múltiplos usuários
     *
     * @param array $users
     * @param string $subject
     * @param string $message
     * @return void
     */
    public function sendNotificationToMultipleUsers(array $users, string $subject, string $message): void
    {
        try {
            Notification::send($users, new GenericNotification($subject, $message));
            
            $userCount = count($users);
            Log::info("Notificação genérica enviada para {$userCount} usuários");
        } catch (\Exception $e) {
            Log::error("Erro ao enviar notificação para múltiplos usuários: " . $e->getMessage());
        }
    }
    
    public function sendCancellationRequestNotification(TravelRequest $travelRequest, string $confirmationLink): void
    {
        $admins = User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            Notification::send($admin, new GenericNotification('Solicitação de cancelamento de viagem', 'Um usuário solicitou o cancelamento de uma viagem aprovada:', 'Revisar solicitação de cancelamento', $confirmationLink));
            Mail::to($admin->email)->send(new CancellationRequested($travelRequest, $confirmationLink));
        }
    }
    
    public function sendCancellationApprovedNotification(TravelRequest $travelRequest): void
    {
        $user = User::find($travelRequest->user_id);
        
        Mail::to($user->email)->send(new CancellationApproved($travelRequest));
        Notification::send($user, new GenericNotification('Solicitação de cancelamento aprovada', 'Sua solicitação de cancelamento de viagem foi aprovada:'));
    }
    
    public function sendCancellationRejectedNotification(TravelRequest $travelRequest, string $rejectionReason): void
    {
        $user = User::find($travelRequest->user_id);
        
        Mail::to($user->email)->send(new CancellationRejected($travelRequest, $rejectionReason));
        Notification::send($user, new GenericNotification('Solicitação de cancelamento rejeitada', 'Sua solicitação de cancelamento de viagem foi rejeitada:'));
    }
}
