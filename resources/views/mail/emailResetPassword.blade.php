<!--------------------------------------------------------------------------------------->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senne Mail</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        @media only screen and (max-width: 600px) {
            .inner-body {
                width: 100% !important;
            }

            .footer {
                width: 100% !important;
            }
        }

        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
                color: white;
                text-decoration: none;
                background-color: #EC6726;
                padding: 8px;
                border-radius: 3px;

            }
        }
    </style>
</head>

<body bgcolor="#F0ECEB" topmargin="0" marginwidth="0" marginheight="0">

    <h1>Ola {{ $user->name }}</h1>
    <p>clique no link abaixo para atualiza sua senha</p>
    <a href="{{ $url }}">Atualizar senha</a>

</body>

</html>
