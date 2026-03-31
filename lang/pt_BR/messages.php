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

    // Status labels
    'status_requested' => 'Solicitado',
    'status_approved' => 'Aprovado',
    'status_canceled' => 'Cancelado',
];
