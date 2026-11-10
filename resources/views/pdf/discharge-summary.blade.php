<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Discharge Summary — {{ $admission->patient_name }} ({{ $admission->ip_no }})</title>
    @include('pdf.partials.discharge-summary._styles')
</head>
<body>
    @include('pdf.partials.discharge-summary._page1', ['admission' => $admission])
    @include('pdf.partials.discharge-summary._page2', ['admission' => $admission])
</body>
</html>
