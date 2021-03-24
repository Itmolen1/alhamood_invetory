@extends('shared.layout-admin')
@section('title', 'Receivable Summary Analysis')

@section('content')

    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                </div>
                <div class="col-md-7 align-self-center text-right">
                    <div class="d-flex justify-content-end align-items-center">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                            <li class="breadcrumb-item active">Receivable Summary Analysis</li>
                        </ol>
                       </div>
                </div>
            </div>

            <h2 class="card-title">Receivable Summary Analysis</h2>

            <form action="{{ URL('ViewReceivableSummaryAnalysis') }}" method="post" enctype="multipart/form-data">
                @csrf

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">From date</label>
                        <input type="date" value="{{ date('Y-m-d') }}" id="fromDate" name="fromDate" class="form-control" placeholder="dd/mm/yyyy" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label">To date</label>
                        <input type="date" value="{{ date('Y-m-d') }}" id="toDate" name="toDate" class="form-control" placeholder="dd/mm/yyyy" required>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success" tabindex="3">View</button>
            </div>
            </form>
        </div>
    </div>

@endsection
