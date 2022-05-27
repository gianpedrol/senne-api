@component('mail::message')
    <h1>Ola {{ $user->name }}</h1>
    <p>clique no link abaixo para atualiza sua senha</p>
    <a href="{{ $url }}">Atualizar senha</a>
@endcomponent
