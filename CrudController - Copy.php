<?php

namespace App\Http\Controllers;

use App\Support\Crud;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class CrudController extends Controller
{
    private function def(string $slug): array
    {
        $d = Crud::get($slug);
        abort_unless(auth()->user()->hasPerm($d['perm']), 403);

        return $d;
    }

    public function index(Request $r, string $slug)
    {
        $d = $this->def($slug);
        $q = $d['model']::query()->with($d['with']);

        if ($s = $r->q) {
            $q->where(function ($w) use ($d, $s) {
                foreach ($d['search'] as $c) {
                    $w->orWhere($c, 'like', "%$s%");
                }
            });
        }

        return view('crud.index', [
            'd'    => $d,
            'slug' => $slug,
            'rows' => $q->latest('id')->paginate(15)->withQueryString(),
        ]);
    }

    public function create(string $slug)
    {
        $d = $this->def($slug);

        return view('crud.form', ['d' => $d, 'slug' => $slug, 'row' => new $d['model']]);
    }

    public function store(Request $r, string $slug)
    {
        $d = $this->def($slug);
        $data = $this->data($r, $d, null);

        foreach ($d['auto'] as $field => $prefix) {
            if (empty($data[$field])) {
                $data[$field] = $prefix . str_pad(($d['model']::max('id') ?? 0) + 1, 5, '0', STR_PAD_LEFT);
            }
        }

        $d['model']::create($data);

        return redirect()->route('crud.index', $slug)->with('ok', 'Saved successfully.');
    }

    public function edit(string $slug, int $id)
    {
        $d = $this->def($slug);

        return view('crud.form', ['d' => $d, 'slug' => $slug, 'row' => $d['model']::findOrFail($id)]);
    }

    public function update(Request $r, string $slug, int $id)
    {
        $d = $this->def($slug);
        $row = $d['model']::findOrFail($id);
        $row->update($this->data($r, $d, $id));

        return redirect()->route('crud.index', $slug)->with('ok', 'Updated successfully.');
    }

    public function destroy(string $slug, int $id)
    {
        $d = $this->def($slug);

        if ($slug === 'users' && $id === auth()->id()) {
            return back()->withErrors(['delete' => 'You cannot delete your own account.']);
        }

        try {
            $d['model']::findOrFail($id)->delete();
        } catch (QueryException $e) {
            return back()->withErrors(['delete' => 'This record is in use and cannot be deleted.']);
        }

        return back()->with('ok', 'Deleted.');
    }

    private function data(Request $r, array $d, ?int $id): array
    {
        $rules = [];

        foreach ($d['fields'] as $f) {
            $rule = str_replace('{id}', $id ?? 'NULL', $f['rules'] ?? 'nullable');

            if ($f['type'] === 'password') {
                $rule = $id ? 'nullable|min:6' : 'required|min:6';
            }

            if ($f['type'] !== 'checkbox') {
                $rules[$f['name']] = $rule;
            }
        }

        $data = $r->validate($rules);

        foreach ($d['fields'] as $f) {
            if ($f['type'] === 'checkbox') {
                $data[$f['name']] = $r->boolean($f['name']);
            }
            if ($f['type'] === 'password' && empty($data[$f['name']])) {
                unset($data[$f['name']]);
            }
        }

        return $data;
    }
}
