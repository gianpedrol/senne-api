@component('mail::message')
    <h1>Ola {{$user->name}}</h1>
    <p>clique no link abaixo para validar seu email</p>
    <a href="{{$url}}">Verificar email</a>
@endcomponent



