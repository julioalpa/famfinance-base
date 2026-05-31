<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\TagGroup;
use Illuminate\Http\Request;

class TagGroupController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        TagGroup::create([
            'family_group_id' => session('active_family_group_id'),
            'name'            => $request->name,
            'color'           => $request->color,
        ]);

        return back()->with('group_success', 'Grupo creado.');
    }

    public function update(Request $request, TagGroup $tagGroup)
    {
        $this->authorize($tagGroup);

        $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $tagGroup->update($request->only('name', 'color'));

        return back()->with('group_success', 'Grupo actualizado.');
    }

    public function destroy(TagGroup $tagGroup)
    {
        $this->authorize($tagGroup);
        $tagGroup->delete();

        return back()->with('group_success', 'Grupo eliminado.');
    }

    public function syncTags(Request $request, TagGroup $tagGroup): \Illuminate\Http\JsonResponse
    {
        $this->authorize($tagGroup);

        $request->validate([
            'tags'   => ['array'],
            'tags.*' => ['integer'],
        ]);

        $groupId  = session('active_family_group_id');
        $tagIds   = collect($request->input('tags', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0);

        $validIds = Tag::where('family_group_id', $groupId)
            ->whereIn('id', $tagIds)
            ->pluck('id');

        $tagGroup->tags()->sync($validIds);

        return response()->json([
            'tags' => $tagGroup->fresh()->tags->map(fn ($t) => [
                'id'    => $t->id,
                'name'  => $t->name,
                'color' => $t->color,
            ]),
        ]);
    }

    private function authorize(TagGroup $tagGroup): void
    {
        abort_if(
            $tagGroup->family_group_id !== session('active_family_group_id'),
            403,
            'No tenés permiso para modificar este grupo.'
        );
    }
}
