<?php

return [
    // Auth
    'login_invalid' => 'E-mail ou senha incorretos.',
    'logout_success' => 'Logout realizado com sucesso.',
    'unauthenticated' => 'Não autenticado.',

    // Travel Request
    'travel_request_canceled' => 'Pedido de viagem cancelado com sucesso.',
    'travel_request_not_found' => 'Pedido de viagem com ID :id não encontrado.',
    'cannot_cancel' => 'Não é possível cancelar este pedido. Apenas pedidos com status "solicitado" podem ser cancelados.',
    'unauthorized_action' => 'Ação não autorizada.',
    'cannot_update_canceled' => 'Não é possível alterar um pedido de viagem cancelado.',

    // Enhanced cancellation
    'cannot_request_cancellation' => 'Não é possível solicitar o cancelamento. O pedido deve estar aprovado e a data de partida deve ser superior a 2 dias.',
    'cancellation_awaiting_confirmation' => 'Sua solicitação de cancelamento foi iniciada. Por favor, confirme usando o link fornecido.',
    'invalid_cancellation_token' => 'Token inválido ou a solicitação não está aguardando confirmação.',
    'cancellation_confirmed' => 'Sua solicitação de cancelamento foi confirmada e enviada para análise do administrador.',
    'not_pending_cancellation' => 'Esta solicitação não está pendente de cancelamento.',

    // Validation
    'validation_failed' => 'Os dados fornecidos são inválidos.',

    // Notifications
    'notification_greeting' => 'Olá :name',
    'notification_status_subject' => 'Sua solicitação de viagem foi :status',
    'notification_status_line' => 'Sua solicitação de viagem para :destination foi :status.',
    'notification_departure' => 'Data de ida: :date',
    'notification_return' => 'Data de volta: :date',
    'notification_cancellation_reason' => 'Motivo do cancelamento: :reason',
    'notification_thanks' => 'Obrigado por usar nosso sistema!',

    // Mail
    'mail_cancellation_requested_subject' => 'Nova solicitação de cancelamento de viagem',
    'mail_cancellation_requested_body' => 'O usuário :name solicitou o cancelamento de uma viagem aprovada.',
    'mail_cancellation_rejected_subject' => 'Solicitação de cancelamento de viagem rejeitada',
    'mail_cancellation_rejected_body' => 'Sua solicitação de cancelamento da viagem para :destination foi rejeitada.',

    // Status labels
    'status_requested' => 'Solicitado',
    'status_approved' => 'Aprovado',
    'status_canceled' => 'Cancelado',
    'status_awaiting_confirmation' => 'Aguardando Confirmação',
    'status_pending_cancellation' => 'Pendente de Cancelamento',
];
