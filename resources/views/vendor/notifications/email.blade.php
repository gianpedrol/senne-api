<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senne Mail</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat, Calibri:wght@400;500;700&display=swap" rel="stylesheet">

    <style>

    </style>
</head>

<body topmargin="0" marginwidth="0" marginheight="0" style="width=600px">
    <table width="500" align="center">
        <table width="500" align="center">
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

        <table  width="500" align="center">
            <tr style=" font-family: Montserrat, Calibri; color: #B8BD5A">
                <td align="left"><img src="https://teste-api-senne.mageda.com.br/uploads/border.png" alt=""></td>
                <td align="center">
                                        <h2 style="font-size: 14px; font-weight: bold">Olá,{{}}<br> tudo bem?</h2>
                </td>
                <td align="right"><img src="https://teste-api-senne.mageda.com.br/uploads/border.png" alt=""></td>
            </tr>
        </table>
        <table width="500" align="center">
            <tr style=" font-family: Montserrat, Calibri; color: #343A40; font-weight: 500" width="500">
                <td align="center">
                    <p style="text-align:center; max-width: 350px; font-weight: bold; padding: 30px 0">Seja bem-vindo ao
                        portal<br>
                        Senne Liquor.</p>
                </td>
            </tr>    
    

                
        </table>
        <table width="500" align="center">
            <tr  width="500">
                <td   width="500" align="center"> 
                    @isset($actionText) <?php
                    switch ($level) {
                        case 'success':
                        case 'error':
                            $color = $level;
                            break;
                    }
                    ?>
                        @component('mail::button', ['url' => $actionUrl])
                        @endcomponent
    
    
                    @endisset
              {{--      <p style="text-align:center; max-width: 350px; margin-top: 30px;">Caso o botão não esteja funcionando,
                        clique no link abaixo ou copie e cole em seu navegador. </p>
                    <a style="width: 300px; font-size 14px" href="{{ $actionUrl }}">{{ $actionUrl }}</a>

                    --}}
    
                </td>
            </tr>

        </table>
        <table width="500"  align="center" style="margin-top: 70px">
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
