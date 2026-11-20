<?php

namespace App\Http\Controllers;

use App\Exports\AsteroidObservationsExport;
use App\Models\AsteroidObservation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class AsteroidObservationController extends Controller
{
    // READ: Menampilkan seluruh daftar arsip observasi asteroid
    public function index()
    {
        $observations = AsteroidObservation::with('user')
            ->latest()
            ->get();

        return view('observations.index', compact('observations'));
    }

    // CREATE (Form): Menampilkan form tambah observasi (mendukung prefill dari Radar)
    public function create(Request $request)
    {
        $prefill = [
            'jpl_id'           => $request->query('jpl_id', ''),
            'name'             => $request->query('name', ''),
            'diameter_m'       => $request->query('diameter_m', ''),
            'distance_km'      => $request->query('distance_km', ''),
            'velocity_kmh'     => $request->query('velocity_kmh', ''),
            'is_hazardous'     => $request->boolean('is_hazardous'),
            'observation_date' => $request->session()->get('last_scan_date', date('Y-m-d')),
        ];

        return view('observations.create', compact('prefill'));
    }

    // CREATE (Store): Menyimpan data observasi & catatan WYSIWYG ke database
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jpl_id'           => ['nullable', 'string', 'max:100'],
            'name'             => ['required', 'string', 'max:255'],
            'diameter_m'       => ['required', 'integer', 'min:1'],
            'distance_km'      => ['required', 'integer', 'min:1'],
            'velocity_kmh'     => ['required', 'integer', 'min:1'],
            'priority_level'   => ['required', 'in:LOW,NORMAL,HIGH,CRITICAL'],
            'observation_date' => ['required', 'date'],
            'analysis_notes'   => ['nullable', 'string'],
            'telescope_image'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        // Sanitasi tag HTML berbahaya sambil mempertahankan format WYSIWYG
        $cleanNotes = strip_tags(
            $validated['analysis_notes'] ?? '',
            '<p><br><strong><b><em><i><u><ul><ol><li><h2><h3><h4><blockquote><a>'
        );

        $imagePath = null;
        if ($request->hasFile('telescope_image')) {
            $imagePath = $request->file('telescope_image')->store('observations', 'public');
        }

        AsteroidObservation::create([
            'user_id'          => Auth::id(),
            'jpl_id'           => $validated['jpl_id'],
            'name'             => $validated['name'],
            'diameter_m'       => $validated['diameter_m'],
            'distance_km'      => $validated['distance_km'],
            'velocity_kmh'     => $validated['velocity_kmh'],
            'is_hazardous'     => $request->boolean('is_hazardous'),
            'priority_level'   => $validated['priority_level'],
            'observation_date' => $validated['observation_date'],
            'analysis_notes'   => $cleanNotes,
            'telescope_image'  => $imagePath,
        ]);

        return redirect()
            ->route('observations.index')
            ->with('status', 'Data observasi asteroid "' . $validated['name'] . '" berhasil diarsipkan!');
    }

    // UPDATE (Form): Menampilkan form edit data observasi
    public function edit(AsteroidObservation $observation)
    {
        return view('observations.edit', compact('observation'));
    }

    // UPDATE (Save): Memperbarui data observasi & catatan WYSIWYG
    public function update(Request $request, AsteroidObservation $observation)
    {
        $validated = $request->validate([
            'jpl_id'           => ['nullable', 'string', 'max:100'],
            'name'             => ['required', 'string', 'max:255'],
            'diameter_m'       => ['required', 'integer', 'min:1'],
            'distance_km'      => ['required', 'integer', 'min:1'],
            'velocity_kmh'     => ['required', 'integer', 'min:1'],
            'priority_level'   => ['required', 'in:LOW,NORMAL,HIGH,CRITICAL'],
            'observation_date' => ['required', 'date'],
            'analysis_notes'   => ['nullable', 'string'],
            'telescope_image'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $cleanNotes = strip_tags(
            $validated['analysis_notes'] ?? '',
            '<p><br><strong><b><em><i><u><ul><ol><li><h2><h3><h4><blockquote><a>'
        );

        $imagePath = $observation->telescope_image;

        // Jika operator mencentang hapus gambar tanpa mengunggah pengganti
        if ($request->boolean('remove_image') && $imagePath) {
            Storage::disk('public')->delete($imagePath);
            $imagePath = null;
        }

        // Jika operator mengunggah gambar baru, hapus file lama terlebih dahulu
        if ($request->hasFile('telescope_image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('telescope_image')->store('observations', 'public');
        }

        $observation->update([
            'jpl_id'           => $validated['jpl_id'],
            'name'             => $validated['name'],
            'diameter_m'       => $validated['diameter_m'],
            'distance_km'      => $validated['distance_km'],
            'velocity_kmh'     => $validated['velocity_kmh'],
            'is_hazardous'     => $request->boolean('is_hazardous'),
            'priority_level'   => $validated['priority_level'],
            'observation_date' => $validated['observation_date'],
            'analysis_notes'   => $cleanNotes,
            'telescope_image'  => $imagePath,
        ]);

        return redirect()
            ->route('observations.index')
            ->with('status', 'Arsip observasi "' . $observation->name . '" berhasil diperbarui!');
    }

    // DELETE: Menghapus data arsip observasi
    public function destroy(AsteroidObservation $observation)
    {
        $name = $observation->name;

        if ($observation->telescope_image) {
            Storage::disk('public')->delete($observation->telescope_image);
        }
        
        $observation->delete();

        return redirect()
            ->route('observations.index')
            ->with('status', 'Arsip observasi "' . $name . '" telah dihapus dari database.');
    }

    // EXPORT PDF: Mengunduh laporan arsip observasi dalam format PDF
    public function exportPdf()
    {
        $observations = AsteroidObservation::with('user')->latest()->get();

        $pdf = Pdf::loadView('observations.pdf', [
            'observations' => $observations,
            'printedBy'    => Auth::user()->name,
            'printedAt'    => now()->format('d M Y, H:i') . ' WIB',
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-observasi-neo-' . date('Y-m-d') . '.pdf');
    }

    // EXPORT EXCEL: Mengunduh arsip observasi dalam format XLSX
    public function exportExcel()
    {
        return Excel::download(
            new AsteroidObservationsExport,
            'arsip-observasi-neo-' . date('Y-m-d') . '.xlsx'
        );
    }
}