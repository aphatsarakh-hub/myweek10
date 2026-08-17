@extends('layout')

@section('title', 'แก้ไขบทความ')

@section('content')
    <style>
        .form-card {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .form-card h2 {
            font-weight: 700;
            color: #2d3436;
            margin-bottom: 30px;
        }

        .form-card h2::after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, #6c5ce7, #a29bfe);
            margin: 12px auto 0;
            border-radius: 2px;
        }

        .form-card label {
            font-weight: 600;
            color: #636e72;
            margin-bottom: 6px;
            display: block;
        }

        .form-card .form-control {
            border-radius: 10px;
            border: 1px solid #dfe6e9;
            padding: 12px 14px;
            transition: all 0.2s ease;
        }

        .form-card .form-control:focus {
            border-color: #6c5ce7;
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.15);
        }

        .form-card textarea.form-control {
            resize: vertical;
        }

        .form-card .text-danger {
            font-size: 0.875rem;
        }

        .form-card .btn-primary {
            background: linear-gradient(90deg, #6c5ce7, #a29bfe);
            border: none;
            border-radius: 10px;
            padding: 10px 26px;
            font-weight: 600;
            transition: transform 0.15s ease;
        }

        .form-card .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(241, 145, 35, 0.35);
        }

        .form-card .btn-secondary {
            border-radius: 10px;
            padding: 10px 26px;
            font-weight: 600;
            background: #f1f2f6;
            border: none;
            color: #2d3436;
        }

        .form-card .btn-secondary:hover {
            background: #dfe4ea;
            color: #2d3436;
        }
    </style>

    <div class="form-card">
        <h2 class="text-center">แก้ไขบทความ</h2>
        <form method="POST" action="{{route('update', $blog->id)}}">
            @csrf
            <div class="form-group mb-3">
                <label for="title">ชื่อบทความ</label>
                <input type="text" name="title" class="form-control" value="{{ $blog->title }}">
            </div>
            @error('title')
                <div class="my-2">
                    <span class="text-danger">{{ $message }}</span>
                </div>
            @enderror

            <div class="form-group mb-3">
                <label for="content">เนื้อหา</label>
                <textarea name="content" cols="30" rows="6" class="form-control">{{ $blog->content }}</textarea>
            </div>
            @error('content')
                <div class="my-2">
                    <span class="text-danger">{{ $message }}</span>
                </div>
            @enderror

            <div class="d-flex gap-2 mt-4">
                <input type="submit" value="บันทึก" class="btn btn-primary">
                <a href="/blog2" class="btn btn-secondary">บทความทั้งหมด</a>
            </div>
        </form>
    </div>
@endsection
