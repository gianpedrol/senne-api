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
                <h2 style="font-weight: bold">Olá,</h2>
                <h3>O seu cadastro foi efetuado com sucesso!</h3>
            </td>

        </tr>

        <tr>
            <td width="350" style="padding-left: 36px; font-family: Montserrat;">
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Cum, quisquam qui eum dolores necessitatibus
                    consequatur natus facere aliquid odit officiis?</p>
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Magnam ullam corrupti obcaecati molestias
                    quisquam tempore odit commodi nisi explicabo sequi? Ut expedita dignissimos excepturi laborum
                    ratione. Ut, provident! Quae, sit?</p>
                <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Nisi libero aliquam rem dolorem sequi ab
                    asperiores nam expedita commodi temporibus?</p>
            </td>

        </tr>
        <tr>
            <td style="padding-left: 36px;padding-top: 30px;font-family: Montserrat; color:white; font-weight: 500;">

                @isset($actionText) <?php
switch ($level) {
    case 'success':
    case 'error':
        $color = $level;
        break;
}
?>
                    @component('mail::button', ['url' => $actionUrl])
                        {{ $actionText }}
                    @endcomponent
                @endisset

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
