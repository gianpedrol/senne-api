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

    </style>
</head>

<body topmargin="0" marginwidth="0" marginheight="0"  style="max-width=600px">
<table width="600">
    <table align="center">
        <tr>
            <td align="center" height="100">
                <img src="https://teste-api-senne.mageda.com.br/uploads/logosenne.png" alt="Senne Liquor" width="138"
                    height="57">
            </td>
        </tr>

        <tr>
            <td align="center"
                style="background-image: url('https://teste-api-senne.mageda.com.br/uploads/Frame.png'); background-size: cover
            ; height: 170px; width: 500px;">
                <img style="margin-top: 50px;" src="https://teste-api-senne.mageda.com.br/uploads/icon.png"
                    alt="" srcset="">
            </td>
        </tr>
    </table>
    <table align="center" width="500">
        <tr style=" font-family: Montserrat; color: #B8BD5A">
            <td align="left"><img src="https://teste-api-senne.mageda.com.br/uploads/border.png" alt=""></td>
            <td align="center">
                @if (!empty($data['name']) )
                <h2 style="font-size: 18px; font-weight: bold">Olá {{$data['name']}}, tudo bem?</h2>
                @else
                <h2 style="font-size: 18px; font-weight: bold">Olá, tudo bem?</h2>
                @endif
            </td>
            <td align="right"><img src="https://teste-api-senne.mageda.com.br/uploads/border.png" alt=""></td>
        </tr>
    </table>
    <table align="center" width="500">
        <tr style=" font-family: Montserrat; color: #343A40; font-weight: 500" width="500">
            <td align="center">
                <p style="text-align:center; max-width: 350px; font-weight: bold;">Sua solicitação de redefinição de senha foi recebida com sucesso.</p>


                <a style="color:white; text-decoration: none; background-color:#ABB056; padding: 10px 50px; border-radius: 40px;"
                    href="{{ $url }}" target="_blank" rel="noopener">Alterar Senha </a>
        
                </td>
            </tr> 
        </table>
        
    <table align="center" width="500">
        <tr  width="500" align="center">
            <td  width="500" align="center">
                <p style="text-align:center; max-width: 350px; margin-top: 30px;">Caso o botão não esteja funcionando,
                    clique no link abaixo ou copie e cole em seu navegador. </p>
                <p style="text-align:center; max-width: 350px; margin-top: 30px;">
                    <span style="text-align:center; max-width: 350px; margin-top: 30px;">
                        {{ $url }}                   
                    </span>
                </p>
               
        
                <p style="text-align:center; max-width: 350px; font-weight: bold; font-size: 16px;">Caso não tenha sido
                    você, entre em contato conosco. </p>
            </td>            
        </tr>

    </table>

                


   
    <table align="center" width="500" style="margin-top: 70px">
        <tr
            style="background-image: url('https://teste-api-senne.mageda.com.br/uploads/footer.png'); background-size: contain; background-repeat: 
            no-repeat;height: 80px;">
            <td>

            </td>
        </tr>
    </table>
</table>
    



</body>

</html>
