@extends('layouts.app')
@section('title','首页')

@section('content')
  <h1>这里是首页</h1>
  {{ storage_path('app/public') }}
  {{storage_path('app')}}
@stop