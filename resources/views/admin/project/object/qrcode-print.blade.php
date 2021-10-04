{{--@extends('layouts.admin')--}}
    <!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('backend/assets/images/favicon.png') }}">
    <title>CAREFAST BACKEND</title>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">


    <style>
        .background-image{
            background-image: url('{{ asset('images/carefast/print-background.jpg') }}');
            background-repeat: no-repeat;
            background-position: center bottom;
            background-size: cover;
            height: 445px;
            padding-top:90px;
        }
        @media print {
            .col-sm-1, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6,
            .col-sm-7, .col-sm-8, .col-sm-9, .col-sm-10, .col-sm-11, .col-sm-12 {
                float: left;
            }
            .col-sm-12 {
                width: 100%;
            }
            .col-sm-11 {
                width: 91.66666666666666%;
            }
            .col-sm-10 {
                width: 83.33333333333334%;
            }
            .col-sm-9 {
                width: 75%;
            }
            .col-sm-8 {
                width: 66.66666666666666%;
            }
            .col-sm-7 {
                width: 58.333333333333336%;
            }
            .col-sm-6 {
                width: 50%;
            }
            .col-sm-5 {
                width: 41.66666666666667%;
            }
            .col-sm-4 {
                width: 33.33333333333333%;
            }
            .col-sm-3 {
                width: 25%;
            }
            .col-sm-2 {
                width: 16.666666666666664%;
            }
            .col-sm-1 {
                width: 8.333333333333332%;
            }

            #non-print {
                display: none;
            }
            .background-image{
                background-image: url('{{ asset('images/carefast/print-background.jpg') }}');
                background-repeat: no-repeat;
                background-position: center bottom;
                background-size: cover;
                height: 400px;
                padding-top:75px;
            }
        }
    </style>
</head>

<body>

<!-- ============================================================== -->
<!-- Container fluid  -->
<!-- ============================================================== -->
<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div id="non-print" class="col-sm-12 pt-3 text-center">
                    <a href="{{ route('admin.project.object.qrcode', ['id' => $project->id]) }}" class="btn btn-outline-primary mr-3">
                        Back
                    </a>
                    &nbsp;
                    <button onclick="printDiv()" class="btn btn-success">Print</button>
                    <br>
                    <h3>PROJECT {{ $project->name }}</h3>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div id="print-section">
{{--                            <div class="col-sm-12">--}}
{{--                                <div class="col-sm-12" style="text-align: center;">--}}
{{--                                    <h2>Project Qr-Code</h2>--}}
{{--                                    <h3 style="font-weight: bold">{{$project->name}}</h3>--}}

{{--                                    <img src="https://api.qrserver.com/v1/create-qr-code/?data={{$project->code}}&size=150x150" alt="" title="" />--}}

{{--                                    <br>--}}
{{--                                    <br>--}}
{{--                                </div>--}}
{{--                                <hr>--}}
{{--                            </div>--}}
                            <div class="col-sm-12 p-t-20" style="text-align: center;">
                                <div class="row pl-5">

                                    @php($count=1)
                                    @foreach($placeArr as $projectObject)
                                        <div class="col-sm-5 pb-5 mx-3 my-3 background-image">
                                            @if($count == 1)
                                                <h5>Project QR-CODE</h5>
                                            @else
                                                <h5>{{substr($project->name,0,24)}}</h5>
                                            @endif
                                            <h4 style="font-weight: bold">{{substr($projectObject["name"],0,24)}}</h4>
                                            <img src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl={{$projectObject["qr_code"]}}&choe=UTF-8" alt="" title="" />
                                            <br>
                                        </div>
                                        @if($count%6 == 0)
                                            <div class="col-sm-5 pb-5 mx-3 my-3" style="height: 200px">
                                            </div>
                                            <div class="col-sm-5 pb-5 mx-3 my-3" style="height: 200px">
                                            </div>
                                        @endif
                                        @php($count++)
                                    @endforeach
                                </div>
{{--                                <table style="width: 100%">--}}
{{--                                    @php($count=1)--}}
{{--                                    @foreach($placeArr as $projectObject)--}}
{{--                                        @if($count%3 == 1)--}}
{{--                                            @if($projectObject["id"] == "0")--}}
{{--                                                <tr style="text-align: center;">--}}
{{--                                                    <td style="width: 33.33333333333333%; text-align: center;padding:20px 10px 20px 10px;">--}}
{{--                                                        <h2 style="font-weight: bold">Project QR-CODE</h2>--}}
{{--                                                        <h3 style="font-weight: bold">{{$projectObject["name"]}}</h3>--}}
{{--                                                        <img src="https://api.qrserver.com/v1/create-qr-code/?data={{$projectObject["qr_code"]}}&size=150x150" alt="" title="" />--}}
{{--                                                        <img src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl={{$projectObject["qr_code"]}}&choe=UTF-8" alt="" title="" />--}}
{{--                                                        <br>--}}
{{--                                                    </td>--}}
{{--                                            @else--}}
{{--                                                <tr style="text-align: center;">--}}
{{--                                                    <td style="width: 33.33333333333333%; text-align: center;padding:20px 10px 20px 10px;">--}}
{{--                                                        <h3 style="font-weight: bold">{{$projectObject["name"]}}</h3>--}}
{{--                                                        --}}{{--                                                        <img src="https://api.qrserver.com/v1/create-qr-code/?data={{$projectObject["qr_code"]}}&size=150x150" alt="" title="" />--}}
{{--                                                        <img src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl={{$projectObject["qr_code"]}}&choe=UTF-8" alt="" title="" />--}}
{{--                                                        <br>--}}
{{--                                                    </td>--}}
{{--                                            @endif--}}
{{--                                        @elseif($count%3 == 2)--}}
{{--                                                <td style="width: 33.33333333333333%; text-align: center;padding:20px 10px 20px 10px;">--}}
{{--                                                    <h3 style="font-weight: bold">{{$projectObject["name"]}}</h3>--}}
{{--                                                    --}}{{--                                                        <img src="https://api.qrserver.com/v1/create-qr-code/?data={{$projectObject["qr_code"]}}&size=150x150" alt="" title="" />--}}
{{--                                                    <img src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl={{$projectObject["qr_code"]}}&choe=UTF-8" alt="" title="" />--}}

{{--                                                    <br>--}}
{{--                                                </td>--}}
{{--                                        @elseif($count%3 == 0)--}}
{{--                                                <td style="width: 33.33333333333333%; text-align: center;padding:20px 10px 20px 10px;">--}}
{{--                                                    <h3 style="font-weight: bold">{{$projectObject["name"]}}</h3>--}}
{{--                                                    --}}{{--                                                        <img src="https://api.qrserver.com/v1/create-qr-code/?data={{$projectObject["qr_code"]}}&size=150x150" alt="" title="" />--}}
{{--                                                    <img src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl={{$projectObject["qr_code"]}}&choe=UTF-8" alt="" title="" />--}}
{{--                                                    <br>--}}
{{--                                                </td>--}}
{{--                                            </tr>--}}
{{--                                        @endif--}}
{{--                                        @php($count++)--}}
{{--                                    @endforeach--}}
{{--                                </table>--}}

{{--                                <div class="row">--}}
{{--                                    @if($projectObjects->count() > 0)--}}
{{--                                        @php($count=1)--}}
{{--                                        @foreach($placeArr as $projectObject)--}}

{{--                                            <div class="col-sm-4 text-center">--}}
{{--                                                <div class="form-group form-float form-group-lg">--}}
{{--                                                    <h3 style="font-weight: bold">{{$projectObject["name"]}}</h3>--}}

{{--                                                    <img src="https://api.qrserver.com/v1/create-qr-code/?data={{$projectObject["qr_code"]}}&size=200x200" alt="" title="" />--}}

{{--                                                    <br>--}}
{{--                                                    <br>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                            @php($count++)--}}
{{--                                        @endforeach--}}
{{--                                    @else--}}
{{--                                        <h4>BELUM ADA PLACE DAN OBJECT TERPILIH</h4>--}}
{{--                                    @endif--}}
{{--                                </div>--}}
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- ============================================================== -->
<!-- End Container fluid  -->
<!-- ============================================================== -->

<!-- jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

<!-- Popper JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>

<!-- Latest compiled JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>

<script>
    // function print(){
    //     w=window.open();
    //     w.document.write($('#print-section').html());
    //     w.print();
    //     w.close();
    //     // window.print();
    // }
    function printDiv(){
        window.print();
    }
</script>

</body>

</html>
