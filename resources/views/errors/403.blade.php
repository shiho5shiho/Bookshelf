@extends('errors::minimal')

@section('title', 'アクセスが拒否されました')
@section('code', '403')
@section('message', $exception->getMessage() ?: 'アクセスが拒否されました')
