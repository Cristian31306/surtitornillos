<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aviso de Vencimiento</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #1a202c;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 30px;
            color: #333333;
            line-height: 1.6;
        }
        .alert {
            background-color: #fefcbf;
            border-left: 4px solid #ecc94b;
            padding: 15px;
            margin: 20px 0;
            font-weight: bold;
            color: #975a16;
        }
        .footer {
            background-color: #f1f5f9;
            text-align: center;
            padding: 15px;
            font-size: 12px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Recordatorio de Vencimiento</h2>
        </div>
        <div class="content">
            <p>Hola,</p>
            <p>Este es un aviso automático del sistema para informar que la membresía/licencia de la plataforma está próxima a vencer.</p>
            
            <div class="alert">
                Faltan exactamente {{ $daysRemaining }} días para el vencimiento.
            </div>
            
            <p><strong>Fecha de vencimiento configurada:</strong> {{ $expirationDate }}</p>
            
            <p>Por favor, asegúrate de gestionar la renovación antes de esta fecha para evitar la interrupción del servicio.</p>
            
            <p>Saludos,<br>El equipo de {{ config('app.name') }}</p>
        </div>
        <div class="footer">
            Este es un correo generado automáticamente. Por favor, no respondas a este mensaje.
        </div>
    </div>
</body>
</html>
