<!DOCTYPE html>
<html>
<head>
    <title>Solicitação de cancelamento aprovada</title>
</head>
<body>
    <h1>Solicitação de cancelamento aprovada</h1>
    
    <p>Olá {{ $travelRequest->requester_name }},</p>
    
    <p>Sua solicitação de cancelamento de viagem foi aprovada:</p>
    
    <ul>
        <li><strong>Destino:</strong> {{ $travelRequest->destination }}</li>
        <li><strong>Data de partida:</strong> {{ date('d/m/Y', strtotime($travelRequest->departure_date)) }}</li>
        <li><strong>Data de retorno:</strong> {{ date('d/m/Y', strtotime($travelRequest->return_date)) }}</li>
        <li><strong>Motivo do cancelamento:</strong> {{ $travelRequest->cancellation_reason }}</li>
        <li><strong>Data da solicitação:</strong> {{ date('d/m/Y H:i', strtotime($travelRequest->cancellation_requested_at)) }}</li>
    </ul>
    
    <p>Atenciosamente,<br>Sistema de Viagens</p>
</body>
</html> 