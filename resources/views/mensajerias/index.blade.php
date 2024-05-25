@extends('admin.layouts.admin')

@section('content')
    <!-- page content -->
    <div class="">
        <div class="row" style="margin-bottom: 15px;">
        </div>
    </div>
    <!-- /page content -->
@endsection

@section('scripts')
    @parent
    {{ Html::script(mix('assets/admin/js/dashboard.js')) }}

@endsection

@section('styles')
    @parent
    {{ Html::style(mix('assets/admin/css/dashboard.css')) }}
@endsection
