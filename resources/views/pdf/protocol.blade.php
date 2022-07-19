<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
        integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">

    <title>Protocolo Atendimento</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        header {
            margin-top: 30px;
            max-width: 100%;
            padding: 30px
        }

        div.logo {
            max-width: 30%;
            display: inline-block;
            margin-right: 30px;

        }

        div.title {
            display: inline-block;
            max-width: 70%;
        }

        div.title h3 {
            font-size: 25px;
        }

        div.info-container-left {
            display: inline-block;
            max-width: 50%;
            padding: 30px;
            text-align: left;
        }

        div.info-container-right {
            display: inline-block;
            max-width: 50%;
            padding: 30px;
            text-align: left;
            margin-left: 140px;

        }

        div .info p span {
            background-color: #ccc;
            padding: 6px 4px;
            margin-bottom: 10px;
            width: 80px;
        }


        table {
            padding: 30px;
        }

        table td:first-child {
            width: 200px;
        }

        table td:last-child {
            width: 100px;
        }

        table thead {
            background-color: #ccc;
            text-align: center;
            border: 1px solid #000;
        }

        tbody td {
            padding: 8px;
        }

        div.container-info {
            text-align: center;
            margin: 0 90px;
        }

        div.container-info p {
            font-size: 16px;
        }

        div.container-info p a {
            font-size: 32px;
            color: #000;
            font-weight: bold;
        }

        div.box-atendimento h2 {
            font-size: 22px;
            color: #000;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <header class="container">
        <div class="logo">
            <img src="https://teste-api-senne.mageda.com.br/uploads/logosenne.png" alt="" srcset="">
        </div>
        <div class="title">
            <h3>Protocolo para acesso aos Resultados de Exames</h3>
            <div>
    </header>
    <div class="container">

        <div class="info-container-left">
            <div class="info">
                <div class="info-description">
                    <p><span>Nome: </span> {{ $data['name'] }}</p>
                </div>
            </div>
            <div class="info">
                <div class="info-description">
                    <p><span>Atendimento: </span> {{ $data['numatendimento'] }}</p>
                </div>
            </div>
        </div>
        <div class="info-container-right">
            <div class="info">
                <div class="info-description">
                    <p><span>Data Coleta: </span> {{ $data['colectdate'] }}</p>
                </div>
            </div>
            <div class="info">
                <div class="info-description">
                    <p><span>Médico: </span> {{ $data['namedoctor'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <table border="1" align="center" width="600">
        <thead>
            <tr>
                <td>
                    <h4>Exames</h4>
                </td>
                <td>
                    <h4>Entrega</h4>
                </td>
            </tr>
        <tbody>
            @foreach ($data['exams'] as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['date'] }}</td>
                </tr>
            @endforeach
        </tbody>
        </thead>
    </table>

    <div class="container-info">
        <p> Os resultados de exames estarão disponíveis na internet após a liberação médica.
            Para consultá-los, acesse:</p>

        <div>
            <p><a href="http://"> www.senneliquor.com.br</a></p>
        </div>

        <p>Clique em "Resultados de Exames", em seguida selecione a opção "Paciente" e preencha os dados abaixo para
            acessar a sua página de Resultados de Exames.
        </p>
        <p>Nesta página você poderá acessar os resultados de todos os exames que você já realizou no Senne Liquor
            Diagnóstico.
        </p>

        <div class="box-login">
            <h2>LOGIN: {{ $data['cpf'] }}</h2>
            <h2>SENHA : {{ $senha_md5 }}</h2>
        </div>
        <p>O prazo de liberação dos resultados pode sofrer alterações após a avaliação da equipe
            médica.
        </p>
        <p>Em caso de dúvidas, sugestões ou reclamações favor entrar em contato: </p>
        <div class="box-atendimento">
            <h2>Entrar em contato: atendimento@senneliquor.com.br</h2>
            <h2>(11) 3286-8989 ou (19) 4141-7270</h2>
        </div>

    </div>
    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: jQuery and Bootstrap Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous">
    </script>

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"
        integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"
        integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous">
    </script>
    -->
</body>

</html>
