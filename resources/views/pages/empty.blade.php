@extends('pages.app')


@section('breadcrumb')
    <x-breadcrumb 
        :items="[
            ['label' => 'Home', 'url' => '#'],
            ['label' => 'Internal Users', 'url' => '#'],
            ['label' => 'List']
        ]" 
        title="Internal Users"
    >
        <a href="#" class="btn btn-primary">Add New</a>
    </x-breadcrumb>
@endsection