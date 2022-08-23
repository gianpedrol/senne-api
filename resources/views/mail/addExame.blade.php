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
        tr.linha {
            max-width: 500px !important;
        }
    </style>
</head>

<body topmargin="0" marginwidth="0" marginheight="0">
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
                <h2 style="font-size: 16px; font-weight: bold, text-align:center">Foi Solicitado um Acréscimo de Exame</h2>
            </td>
            <td align="right"><img src="https://teste-api-senne.mageda.com.br/uploads/border.png" alt=""></td>
        </tr>
    </table>
    <table align="center" width="550">
        <tr style=" font-family: Montserrat; color: #343A40; font-weight: 500">
            <td align="center">
                <p style="text-align:center; max-width: 350px;">Numero de atendimento : {{$data['numatendimento']}}  </p>
                <h3 style="text-align:left; max-width: 350px">Solicitações : </h3>
                @foreach ( $data['solicitation']
                 as $item )
                <ul style="text-align:left; max-width: 350px;"> 
                    <li> {{$item}} </li>
                </ul>
                @endforeach

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

</body>

</html>
