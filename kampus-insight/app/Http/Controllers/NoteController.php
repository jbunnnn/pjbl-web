<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoteController extends Controller
{
    public function index(Request $request): View
    {
        return view('profile.notes', [
            'notes' => $request->user()->notes()->latest('updated_at')->paginate(10),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'content' => ['required', 'string', 'max:10000'],
        ]);

        $request->user()->notes()->create($validated);

        return back()->with('status', 'Catatan berhasil disimpan.');
    }

    public function update(Request $request, Note $note): RedirectResponse
    {
        $ownedNote = $request->user()->notes()->findOrFail($note->getKey());
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'content' => ['required', 'string', 'max:10000'],
        ]);

        $ownedNote->update($validated);

        return back()->with('status', 'Catatan berhasil diperbarui.');
    }

    public function destroy(Request $request, Note $note): RedirectResponse
    {
        $request->user()->notes()->findOrFail($note->getKey())->delete();

        return back()->with('status', 'Catatan berhasil dihapus.');
    }
}
