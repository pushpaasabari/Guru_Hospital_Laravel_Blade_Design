<div class="mrd-document" style="font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;">
    {{-- Header with Logo --}}
    <div class="text-center mb-4">
        <div class="mb-2">
            <img src="{{ asset('images/guru-hospital-logo.png') }}" alt="Guru Hospital Logo" style="width: 76px; height: auto; display: inline-block;" />
        </div>

        <h2 class="fw-bold mb-1 text-dark text-uppercase" style="font-size: 21pt; letter-spacing: 1px; font-weight: 800;">
            GURU HOSPITAL
        </h2>
        <div style="font-size: 10pt; color: #222; line-height: 1.4;">
            32, A/2, Antony Street, Nethaji Bypass Road,
        </div>
        <div style="font-size: 10pt; color: #222; line-height: 1.4;">
            Govt. Medical College Hospital Opp. Dharmapuri - 636 701.
        </div>
        <div style="font-size: 10pt; font-weight: 600; color: #111; margin-top: 2px;">
            Ph : 04342-262266, Cell : 7418084266
        </div>
    </div>

    {{-- Title --}}
    <div class="text-center my-4">
        <span style="font-family: 'Times New Roman', Times, Georgia, serif; font-size: 16.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #111; display: inline-block; border-bottom: 3px double #111; padding-bottom: 3px;">
            MEDICAL RECORDS DEPARTMENT
        </span>
    </div>

    {{-- 9 Clean Form Fields covering the page --}}
    <table class="table table-borderless my-4" style="font-size: 14pt;">
        <tbody>
            <tr>
                <td style="width: 270px; font-weight: bold; padding: 20px 4px;">Patient Name</td>
                <td style="width: 35px; font-weight: bold; text-align: center; padding: 20px 4px;">:</td>
                <td style="padding: 20px 4px;"><strong>{{ $admission->patient_name }}</strong></td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 20px 4px;">Discharge Date &amp; Time</td>
                <td style="font-weight: bold; text-align: center; padding: 20px 4px;">:</td>
                <td style="padding: 20px 4px;">
                    {{ $admission->discharge_at ? $admission->discharge_at->format('d.m.Y') . ' @ ' . $admission->discharge_at->format('h.i A') : '29.08.2026 @ 04.00 PM' }}
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 20px 4px;">MRD No.</td>
                <td style="font-weight: bold; text-align: center; padding: 20px 4px;">:</td>
                <td style="padding: 20px 4px;">
                    {{ $admission->uhid ? (str_starts_with($admission->uhid, 'MRD-') ? $admission->uhid : 'MRD-' . $admission->uhid) : ($admission->mrd_no ?: ($admission->ip_no ? 'MRD-' . $admission->ip_no : '')) }}
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 20px 4px;">IP No.</td>
                <td style="font-weight: bold; text-align: center; padding: 20px 4px;">:</td>
                <td style="padding: 20px 4px;">
                    <strong>{{ $admission->ip_no }}</strong>
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 20px 4px;">No of pages</td>
                <td style="font-weight: bold; text-align: center; padding: 20px 4px;">:</td>
                <td style="padding: 20px 4px;"></td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 20px 4px;">Payment Mode</td>
                <td style="font-weight: bold; text-align: center; padding: 20px 4px;">:</td>
                <td style="padding: 20px 4px;"></td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 20px 4px;">Hand Over Sign</td>
                <td style="font-weight: bold; text-align: center; padding: 20px 4px;">:</td>
                <td style="padding: 20px 4px;"></td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 20px 4px;">Floor Incharge Sign</td>
                <td style="font-weight: bold; text-align: center; padding: 20px 4px;">:</td>
                <td style="padding: 20px 4px;"></td>
            </tr>
            <tr>
                <td style="font-weight: bold; padding: 20px 4px;">MRD Incharge Sign</td>
                <td style="font-weight: bold; text-align: center; padding: 20px 4px;">:</td>
                <td style="padding: 20px 4px;"></td>
            </tr>
        </tbody>
    </table>
</div>
