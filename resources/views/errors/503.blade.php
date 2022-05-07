@extends('layouts._error')

@section('title', '503')
@section('content')
    <p class="text-grey-darker text-2xl md:text-3xl font-light mb-8 leading-normal">
        抱歉，我们正在做一些维护。请稍后再回来查看。
    </p>
@endsection
@section('errorImage')
    <div style="background-image: url({{ asset('svg/503.svg') }})" class="absolute pin bg-cover bg-no-repeat md:bg-left lg:bg-center"></div>
@endsection
