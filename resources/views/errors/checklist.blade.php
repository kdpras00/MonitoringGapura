@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header bg-danger text-white">
            <h3 class="m-0">Error Pada Data Checklist</h3>
        </div>
        <div class="card-body">
            <p class="alert alert-danger">
                Terjadi error saat memproses data checklist. Detail error telah dicatat untuk perbaikan.
            </p>
            
            <div class="mb-4">
                <h5>Detail Error:</h5>
                <pre class="bg-light p-3">{{ $error }}</pre>
            </div>
            
            <div class="mb-4">
                <h5>URL yang Diakses:</h5>
                <pre class="bg-light p-3">{{ $url }}</pre>
            </div>
            
            <div class="alert alert-info">
                <p class="mb-0">
                    <strong>Saran Tindakan:</strong>
                </p>
                <ul class="mb-0 mt-2">
                    <li>Refresh halaman dan coba kembali</li>
                    <li>Kembali ke <a href="{{ url('/') }}">halaman utama</a></li>
                    <li>Hubungi administrator sistem jika masalah berlanjut</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection 