@extends('layouts._error')

@section('title', '403')
@section('content')
    <p class="text-grey-darker text-2xl md:text-3xl font-light mb-8 leading-normal">
        抱歉，您没有权限浏览该页面。
    </p>
@endsection
@section('errorImage')
    <div style="background-image: url({{ asset('svg/403.svg') }})" class="absolute pin bg-cover bg-no-repeat md:bg-left lg:bg-center"></div>
@endsection
