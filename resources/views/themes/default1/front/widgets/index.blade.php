@extends('themes.default1.layouts.master')
@section('content-header')
<h1>
All Widgets
</h1>
  <ol class="breadcrumb">
        <li><a href="{{url('/')}}"><i class="fa fa-dashboard"></i> Home</a></li>
         <li class="active">Widgets</li>
      </ol>
@stop
@section('content')
<div class="box box-primary">

    <div class="box-header">
        @if (count($errors) > 0)
        <div class="alert alert-danger">
            <strong>Whoops!</strong> There were some problems with your input.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(Session::has('success'))
        <div class="alert alert-success alert-dismissable">
            <i class="fa fa-ban"></i>
            <b>{{Lang::get('message.alert')}}!</b> {{Lang::get('message.success')}}.
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {{Session::get('success')}}
        </div>
        @endif
        <!-- fail message -->
        @if(Session::has('fails'))
        <div class="alert alert-danger alert-dismissable">
            <i class="fa fa-ban"></i>
            <b>{{Lang::get('message.alert')}}!</b> {{Lang::get('message.failed')}}.
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {{Session::get('fails')}}
        </div>
        @endif
        <div id="response"></div>

        <h4>{{Lang::get('message.widgets')}}
            <a href="{{url('widgets/create')}}" class="btn btn-primary btn-sm pull-right"><span class="glyphicon glyphicon-plus"></span>&nbsp;&nbsp;{{Lang::get('message.create')}}</a></h4>
    </div>


    <div class="box-body">
        <div class="row">

            <div class="col-md-12">
       
                   <table id="widgets-table" class="table display" cellspacing="0" width="100%" styleClass="borderless">

                    <thead><tr>
                        <th class="no-sort"><input type="checkbox" name="select_all" onchange="checking(this)"></th>
                         <th>Name</th>
                          <!-- <th>Type</th> -->
                          <th>Created At</th>
                           <th>Action</th>
                        </tr></thead>
                     </table>
                

            </div>
        </div>

    </div>

</div>

<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css" />
<script src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
        $('#widgets-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{!! route('get-widgets') !!}',
            "oLanguage": {
                "sLengthMenu": "_MENU_ Records per page",
                "sSearch"    : "Search: ",
                "sProcessing": '<img id="blur-bg" class="backgroundfadein" style="top:40%;left:50%; width: 50px; height:50 px; display: block; position:    fixed;" src="{!! asset("lb-faveo/media/images/gifloader3.gif") !!}">'
            },
                "columnDefs": [{
                "defaultContent": "-",
                "targets": "_all"
              }],
     
            columns: [
               
                {data: 'name', name: 'name'},
                {data: 'type', name: 'Type'},
                {data: 'created_at', name: 'Created At'},
                {data: 'action', name: 'Action'}
            ],
            "fnDrawCallback": function( oSettings ) {
                $('.loader').css('display', 'none');
            },
            "fnPreDrawCallback": function(oSettings, json) {
                $('.loader').css('display', 'block');
            },
        });
    </script>

@stop

@section('icheck')
<script>
    $(function () {


        //Enable check and uncheck all functionality
        $(".checkbox-toggle").click(function () {
            var clicks = $(this).data('clicks');
            if (clicks) {
                //Uncheck all checkboxes
                $(".mailbox-messages input[type='checkbox']").iCheck("uncheck");
                $(".fa", this).removeClass("fa-check-square-o").addClass('fa-square-o');
            } else {
                //Check all checkboxes
                $(".mailbox-messages input[type='checkbox']").iCheck("check");
                $(".fa", this).removeClass("fa-square-o").addClass('fa-check-square-o');
            }
            $(this).data("clicks", !clicks);
        });


    });
</script>
@stop