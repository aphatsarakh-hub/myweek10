@extends('layout')

@section('title', 'Aphatsara Khaemadan')

@section('content')
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <h2 class="mb-4">เกี่ยวกับผู้พัฒนา</h2>
            <div class="mb-3">
                <img src="https://via.placeholder.com/150" class="rounded-circle border" alt="Profile Image">
            </div>
            <h4 class="text-primary">{{ $name }}</h4>
            <p class="text-muted">นักศึกษาเทคโนโลยีสารสนเทศ</p>
        </div>
    </div>
@endsection
