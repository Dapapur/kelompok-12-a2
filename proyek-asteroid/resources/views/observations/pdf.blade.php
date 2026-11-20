<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Observasi Asteroid NEO</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1e293b;
            margin: 15px;
        }
        .header {
            border-bottom: 2px solid #0284c7;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #0f172a;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 4px 0 0;
            font-size: 10px;
            color: #475569;
        }
        .meta-box {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 8px 12px;
            margin-bottom: 14px;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background-color: #0f172a;
            color: #f8fafc;
            text-align: left;
            padding: 7px 8px;
            font-size: 10px;
            text-transform: uppercase;
            border: 1px solid #0f172a;
        }
        td {
            padding: 7px 8px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge-hazard {
            background-color: #fee2e2;
            color: #b91c1c;
            padding: 2px 6px;
            font-weight: bold;
            font-size: 9px;
            border-radius: 3px;
        }
        .badge-safe {
            background-color: #e0f2fe;
            color: #0369a1;
            padding: 2px 6px;
            font-weight: bold;
            font-size: 9px;
            border-radius: 3px;
        }
        .notes p {
            margin: 0 0 4px;
        }
        .notes ul, .notes ol {
            margin: 0;
            padding-left: 16px;
        }
        .footer {
            margin-top: 18px;
            text-align: right;
            font-size: 9px;
            color: #64748b;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>ASTRONET // NEO RADAR CONTROL CENTER</h1>
        <p>Laporan Resmi Arsip Observasi & Analisis Mitigasi Near-Earth Objects</p>
    </div>

    <div class="meta-box">
        <strong>Dicetak Oleh:</strong> {{ $printedBy }} &nbsp;|&nbsp;
        <strong>Tanggal Cetak:</strong> {{ $printedAt }} &nbsp;|&nbsp;
        <strong>Total Objek Diarsipkan:</strong> {{ $observations->count() }} Asteroid &nbsp;|&nbsp;
        <strong>Objek Berbahaya:</strong> {{ $observations->where('is_hazardous', true)->count() }} Asteroid
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 17%;">Asteroid & JPL ID</th>
                <th style="width: 12%;">Citra</th>
                <th style="width: 11%;">Tgl Observasi</th>
                <th style="width: 18%;">Parameter Orbit</th>
                <th style="width: 12%;">Status & Prioritas</th>
                <th style="width: 38%;">Catatan Analisis</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($observations as $index => $obs)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $obs->name }}</strong><br>
                        <span style="color: #64748b; font-size: 9px;">JPL: {{ $obs->jpl_id ?: '-' }}</span><br>
                        <span style="color: #64748b; font-size: 9px;">Op: {{ $obs->user->name ?? 'Operator' }}</span>
                    </td>
                    <td style="text-align: center;">
                        @if ($obs->telescope_image && file_exists(public_path('storage/' . $obs->telescope_image)))
                            <img src="{{ public_path('storage/' . $obs->telescope_image) }}" style="width: 65px; height: 50px; object-fit: cover; border: 1px solid #cbd5e1;">
                        @else
                            <span style="color: #94a3b8; font-size: 9px;">-</span>
                        @endif
                    </td>
                    <td>{{ $obs->observation_date->format('d M Y') }}</td>
                    <td>
                        Diameter: {{ number_format($obs->diameter_m) }} m<br>
                        Jarak: {{ number_format($obs->distance_km) }} km<br>
                        Kecepatan: {{ number_format($obs->velocity_kmh) }} km/h
                    </td>
                    <td>
                        @if ($obs->is_hazardous)
                            <span class="badge-hazard">HAZARDOUS</span>
                        @else
                            <span class="badge-safe">SAFE</span>
                        @endif
                        <br><br>
                        <strong>Prioritas:</strong> {{ $obs->priority_level }}
                    </td>
                    <td class="notes">
                        {!! $obs->analysis_notes ?: '<em>Tidak ada catatan analisis.</em>' !!}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 16px; color: #64748b;">
                        Belum ada data observasi asteroid yang tersimpan di arsip.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dokumen dihasilkan otomatis oleh sistem ASTRONET NEO Radar • {{ $printedAt }}
    </div>

</body>
</html>