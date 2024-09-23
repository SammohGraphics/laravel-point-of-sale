@extends('dashboard.body.main')

@section('container')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            @if (session()->has('success'))
                <div class="alert text-white bg-success" role="alert">
                    <div class="iq-alert-text">{{ session('success') }}</div>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <i class="ri-close-line"></i>
                    </button>
                </div>
            @elseif (session()->has('error'))
                <div class="alert text-white bg-danger" role="alert">
                    <div class="iq-alert-text">{{ session('error') }}</div>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <i class="ri-close-line"></i>
                    </button>
                </div>
            @endif

            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-3">Database Backup List</h4>
                </div>
                <div>
                    <a href="{{ route('backup.create') }}" class="btn btn-primary add-list">Backup Now</a>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            @if (count($files) > 0)
            <div class="table-responsive rounded mb-3">
                <table class="table mb-0">
                    <thead class="bg-white text-uppercase">
                        <tr class="ligth ligth-data">
                            <th>No.</th>
                            <th>File Name</th>
                            <th>File Size</th>
                            <th>Path</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="ligth-body">
                        @foreach ($files as $file)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $file->getFilename() }}</td>
                            <td>{{ number_format($file->getSize() / 1048576, 2) }} MB</td> <!-- Converts size to MB -->
                            <td>{{ $file->getPath() }}</td>
                            <td>
                                <div class="d-flex align-items-center list-action">
                                    <a class="btn btn-success mr-2" data-toggle="tooltip" data-placement="top" title="Download"
                                        href="{{ route('backup.download', $file->getFilename()) }}"><i class="fa-solid fa-download mr-0"></i>
                                    </a>
                                    <a class="btn btn-danger mr-2" data-toggle="tooltip" data-placement="top" title="Delete"
                                        href="{{ route('backup.delete', $file->getFilename()) }}"><i class="fa-solid fa-trash mr-0"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <div class="alert alert-info">No backups available yet.</div>
            @endif
        </div>
    </div>
</div>
@endsection
