@extends('pages.app')

@section('pageName', 'Dashboard')

@push('scripts')
   

@endpush




@section('breadcrumb')
    <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => '/'],
          
        ]" title="View Application">

    </x-breadcrumb>
@endsection

@section('content')

   

   

@endsection

@push('scripts')
   
@endpush