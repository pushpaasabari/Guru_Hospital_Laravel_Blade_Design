<h2 class="text-center">GURU HOSPITAL</h2><h3 class="text-center">MEDICAL RECORDS DEPARTMENT</h3>
<p class="text-center">32, A/2, Antony Street, Nethaji Bypass Road, Govt. Medical College Hospital Opp. Dharmapuri - 636 701.<br>Ph: 04342-262266, Cell: +91 7418084266</p>
<table class="table table-bordered">
<tr><th>Patient Name</th><td>{{ $admission->patient_name }}</td></tr>
<tr><th>Discharge Date & Time</th><td>{{ $admission->discharge_at?->format('d/m/Y H:i') }}</td></tr>
<tr><th>MRD No.</th><td>{{ $admission->mrd_no ?: 'Not recorded' }}</td></tr>
<tr><th>IP No.</th><td>{{ $admission->ip_no }}</td></tr>
<tr><th>No. of Pages</th><td>Calculate after final combined PDF is rendered</td></tr>
<tr><th>Payment Mode</th><td>{{ $admission->payment_mode ?: 'Not recorded' }}</td></tr>
<tr><th>Hand Over Sign</th><td>________________________________</td></tr>
<tr><th>Floor Incharge Sign</th><td>________________________________</td></tr>
<tr><th>MRD Incharge Sign</th><td>________________________________</td></tr>
</table>
