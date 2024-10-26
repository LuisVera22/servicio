<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style type="text/css">
        body {
            font-family: 'Verdana', sans-serif;
            font-size: 15px;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            text-align: center;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }
        .logo {
            margin-right: 15px;
        }
        .logo img {
            max-width: 50px;
        }
        h1 {
            font-size: 22px;
            font-weight: bold;
            color: #2c3e50;
            margin: 0;
        }
        p {
            font-size: 16px;
            color: #555;
            line-height: 1.6;
        }
        .new-password {
            color: #e74c3c; /* Color de la nueva contraseña */
            font-weight: bold;
            font-size: 18px; /* Tamaño del texto */
            background-color: #f9e5e5; /* Fondo suave */
            padding: 5px 10px; /* Espaciado alrededor del texto */
            border-radius: 5px; /* Bordes redondeados */
        }
        .user-name {
            color: #3498db; /* Color del nombre del usuario */
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            font-size: 13px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <img src="{{ asset('storage/img_logo_empresa/spektro.png') }}" alt="Logo de la empresa">
            </div>
            <h1>SPEKTRO360 S.A.C</h1>
        </div>

        <p>Hola <span class="user-name">{{ $username }}</span>,</p>
        <p>Tu contraseña ha sido cambiada exitosamente.</p>
        <p>Tu nueva contraseña es: <span class="new-password">{{ $newpassword }}</span></p>
        <p>Por favor, asegúrate de guardarla en un lugar seguro.</p>

        <div class="footer">
            <p>Este es un mensaje automático, por favor no respondas a este correo.</p>
            <p>&copy; 2024 SPEKTRO. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
