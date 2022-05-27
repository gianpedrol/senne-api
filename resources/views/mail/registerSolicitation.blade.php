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
    <table>
        <tr>
            <td align="left" height="100">
                <img src="https://teste-api-senne.mageda.com.br/uploads/logosenne.png" alt="Senne Liquor" width="138"
                    height="57">
            </td>
        </tr>
        <tr>
            <td height="20"></td>
        </tr>

        <tr>
            <td width="500" style="padding-left: 36px; font-family: Montserrat; color: #EC6726">
                <h2 style="font-weight: bold">Solicitação de Cadastro para plataforma</h2>
            </td>

        </tr>

        <tr>
            <td width="350" style="padding-left: 36px; font-family: Montserrat;">
                @if ($data['name'])
                    <p>Nome: {{ $data['name'] }}</p>
                @endif
                @if ($data['cpf'])
                    <p>CPF: {{ $data['cpf'] }}</p>
                @endif
                @if ($data['phone'])
                    <p>Telefone: {{ $data['phone'] }}</p>
                @endif
                @if ($data['email'])
                    <p>Email: {{ $data['email'] }}</p>
                @endif
                @if ($data['nameempresa'])
                    <p>Nome da Empresa: {{ $data['nameempresa'] }}</p>
                @endif
                @if ($data['razaosocial'])
                    <p>Razão Social: {{ $data['razaosocial'] }}</p>
                @endif
                @if ($data['cnpj'])
                    <p>CNPJ: {{ $data['cnpj'] }}</p>
                @endif
                @if ($data['classification'])
                    <p>Classificação: {{ $data['classification'] }}</p>
                @endif

                </p>
            </td>

        </tr>

        <tr>
            <td style="padding-left: 36px;padding-top: 30px;font-family: Montserrat;">
                <h3 style="color:#EC6726; font-size:14px ">Equipe Senne</h3>
            </td>
        </tr>

    </table>
</body>

</html>
