<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ChirpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Chirps/Index', [
            'chirps' => Chirp::with('user:id,name')->latest()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('chirps', 'public'); // Simpan gambar
        }

        $request->user()->chirps()->create($validated);

        return redirect(route('chirps.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Chirp $chirp)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Chirp $chirp)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Chirp $chirp): RedirectResponse
    {
        // Cek otorisasi pengguna
        Gate::authorize('update', $chirp);

        // Validasi data
        $validated = $request->validate([
            'message' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Perbarui data pesan
        if ($request->hasFile('image')) {

            // Jika ada file gambar baru, simpan dan ganti gambar lama
            $validated['image'] = $request->file('image')->store('chirps', 'public');
        }

        $chirp->update($validated);

       return redirect()->back()->with('message', 'Chirp updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Chirp $chirp): RedirectResponse
    {
        // Cek otorisasi pengguna
        Gate::authorize('delete', $chirp);

        // Hapus data
        $chirp->delete();

        return redirect(route('chirps.index'))->with('success', 'Chirp deleted successfully.');
    }
}
