<!DOCTYPE html>
<html>
<head>
    <title>Nova solicitação de cancelamento</title>
</head>
<body>
    <h1>Nova solicitação de cancelamento de viagem</h1>
    
    <p>Olá Administrador,</p>
    
    <p>Um usuário solicitou o cancelamento de uma viagem aprovada:</p>
    
    <ul>
        <li><strong>Solicitante:</strong> {{ $travelRequest->requester_name }}</li>
        <li><strong>Destino:</strong> {{ $travelRequest->destination }}</li>
        <li><strong>Data de partida:</strong> {{ date('d/m/Y', strtotime($travelRequest->departure_date)) }}</li>
        <li><strong>Data de retorno:</strong> {{ date('d/m/Y', strtotime($travelRequest->return_date)) }}</li>
        <li><strong>Motivo do cancelamento:</strong> {{ $travelRequest->cancellation_reason }}</li>
        <li><strong>Data da solicitação:</strong> {{ date('d/m/Y H:i', strtotime($travelRequest->cancellation_requested_at)) }}</li>
    </ul>
    
    <p>Para revisar esta solicitação, clique no link abaixo:</p>
    
    <p><a href="{{ $confirmationLink }}">Revisar solicitação de cancelamento</a></p>
    
    <p>Atenciosamente,<br>Sistema de Viagens</p>
</body>
</html> 