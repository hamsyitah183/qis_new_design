@extends('pages.app')


@section('breadcrumb')
    <x-breadcrumb 
        :items="[
            ['label' => 'Home', 'url' => '#'],
          
        ]" 
        title="Dashboard"
    >
     
    </x-breadcrumb>
@endsection

@section('content')
    {{-- <a href="{{ route('public.permitApplication') }}">Apply for self!</a> --}}
@endsection