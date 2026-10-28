<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Guru Hospital Documents')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f4f7fb; color:#172b4d; }
        .hospital-brand { color:#17365d; font-weight:800; letter-spacing:.02em; }
        .card { border:0; box-shadow:0 2px 12px rgba(23,54,93,.07); }
        .required:after { content:" *"; color:#b42318; }
        @media print { .no-print { display:none!important } body { background:#fff } }
    </style>
</head>
<body>
<nav class="navbar navbar-light bg-white border-bottom mb-4 no-print">
    <div class="container"><span class="navbar-brand hospital-brand">GURU HOSPITAL</span><span class="text-muted small">Document Pack</span></div>
</nav>
<main class="container pb-5">
    @if(session('status')) <div class="alert alert-success no-print">{{ session('status') }}</div> @endif
    @if($errors->any())
        <div class="alert alert-danger no-print"><strong>Please correct these fields:</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @yield('content')
</main>
</body>
</html>
