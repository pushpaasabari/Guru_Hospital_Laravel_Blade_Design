<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Discharge Summary (A4) — {{ $admission->patient_name }} ({{ $admission->ip_no }})</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    @include('pdf.partials.discharge-summary-a4._styles')
</head>
<body>
    @include('pdf.partials.discharge-summary-a4._page1', ['admission' => $admission])
    @include('pdf.partials.discharge-summary-a4._page2', ['admission' => $admission])
</body>
</html>

