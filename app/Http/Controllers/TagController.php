<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\TagGroup;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        $groupId = session('active_family_group_id');

        $tags = Tag::where('family_group_id', $groupId)
            ->with(['transactions', 'paymentItems'])
            ->orderBy('name')
            ->get();

        $tags->each(function ($tag) {
            $tag->tx_count     = $tag->transactions->count();
            $tag->py_count     = $tag->paymentItems->count();
            $tag->tx_amount    = (float) $tag->transactions->where('currency', 'ARS')->where('type', 'expense')->sum('amount');
            $tag->py_amount    = (float) $tag->paymentItems->where('currency', 'ARS')->sum('amount');
            $tag->total_amount = $tag->tx_amount + $tag->py_amount;
        });

        $grandTotal = $tags->sum('total_amount');

        $tags->each(function ($tag) use ($grandTotal) {
            $tag->percentage = $grandTotal > 0 ? round($tag->total_amount / $grandTotal * 100, 1) : 0;
        });

        $tagGroups = TagGroup::where('family_group_id', $groupId)
            ->with('tags')
            ->orderBy('name')
            ->get();

        return view('tags.index', compact('tags', 'grandTotal', 'tagGroups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => ['required', 'string', 'max:50'],
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $groupId = session('active_family_group_id');

        $tag = Tag::create([
            'family_group_id' => $groupId,
            'name'            => $request->name,
            'color'           => $request->color,
        ]);

        if ($request->wantsJson()) {
            return response()->json($tag);
        }

        return back()->with('success', 'Etiqueta creada.');
    }

    public function update(Request $request, Tag $tag)
    {
        $this->authorizeTag($tag);

        $request->validate([
            'name'  => ['required', 'string', 'max:50'],
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $tag->update($request->only('name', 'color'));

        return back()->with('success', 'Etiqueta actualizada.');
    }

    public function destroy(Tag $tag)
    {
        $this->authorizeTag($tag);
        $tag->delete();

        return back()->with('success', 'Etiqueta eliminada.');
    }

    private function authorizeTag(Tag $tag): void
    {
        abort_if(
            $tag->family_group_id !== session('active_family_group_id'),
            403,
            'No tenés permiso para modificar esta etiqueta.'
        );
    }
}
