<?php

namespace Coderstm\Core\Http\Controllers\Admin;

use Coderstm\Core\Models\Core\File;
use Coderstm\Core\Models\Instructor;
use Illuminate\Http\Request;
use Coderstm\Core\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\ResourceCollection;

class InstructorController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Instructor::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Instructor $instructor)
    {
        $instructor = $instructor->query();

        if ($request->filled('filter')) {
            $instructor->where('name', 'like', "%{$request->filter}%");
            $instructor->orWhere('email', 'like', "%{$request->filter}%");
        }

        if ($request->filled('is_pt')) {
            $instructor->where('is_pt', $request->is_pt);
        }

        if ($request->filled('class')) {
            $instructor->whereHas('classes', function ($query) use ($request) {
                $query->where('id', $request->class);
            });
        }

        if ($request->filled('status')) {
            $instructor->where('status', $request->input('status'));
        }

        if ($request->boolean('active')) {
            $instructor->onlyActive();
        }

        if ($request->boolean('deleted')) {
            $instructor->onlyTrashed();
        }

        $instructor = $instructor->orderBy(optional($request)->sortBy ?? 'created_at', optional($request)->direction ?? 'desc')
            ->paginate(optional($request)->rowsPerPage ?? 15);
        return new ResourceCollection($instructor);
    }

    /**
     * Display a options listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function options(Request $request, Instructor $instructor)
    {
        $request->merge([
            'option' => true
        ]);
        return $this->index($request, $instructor);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Instructor $instructor)
    {
        $rules = [
            'name' => 'required',
            'phone' => 'required',
            'email' => 'required',
            'status' => 'required',
        ];

        // Validate those rules
        $this->validate($request, $rules);

        // create the instructor
        $instructor = Instructor::create($request->input());

        if ($request->filled('classes')) {
            $instructor = $instructor->syncClasses(collect($request->classes));
        }

        if ($request->filled('avatar')) {
            $instructor->avatar()->sync([
                $request->input('avatar.id') => [
                    'type' => 'avatar'
                ]
            ]);
        }

        if ($request->filled('insurance_file')) {
            $instructor->insurance_file()->sync([
                $request->input('insurance_file.id') => [
                    'type' => 'insurance'
                ]
            ]);
        }

        if ($request->filled('qualification_file')) {
            $instructor->qualification_file()->sync([
                $request->input('qualification_file.id') => [
                    'type' => 'qualification'
                ]
            ]);
        }

        if ($request->filled('document_file')) {
            $instructor->document_file()->sync([
                $request->input('document_file.id') => [
                    'type' => 'document'
                ]
            ]);
        }

        return response()->json([
            'data' => $instructor->fresh('classes', 'avatar', 'insurance_file', 'qualification_file', 'document_file'),
            'message' => 'Instructor has been created successfully!',
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Coderstm\Core\Models\Instructor  $instructor
     * @return \Illuminate\Http\Response
     */
    public function show(Instructor $instructor)
    {
        return response()->json($instructor->load('classes', 'insurance_file', 'qualification_file', 'document_file'), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Coderstm\Core\Models\Instructor  $instructor
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Instructor $instructor)
    {

        $rules = [
            'name' => 'required',
            'phone' => 'required',
            'email' => 'required',
            'status' => 'required',
        ];

        // Validate those rules
        $this->validate($request, $rules);

        // update the instructor
        $instructor->update($request->input());

        if ($request->filled('classes')) {
            $instructor = $instructor->syncClasses(collect($request->classes));
        }

        if ($request->filled('avatar')) {
            $instructor->avatar()->sync([
                $request->input('avatar.id') => [
                    'type' => 'avatar'
                ]
            ]);
        }

        if ($request->filled('insurance_file')) {
            $instructor->insurance_file()->sync([
                $request->input('insurance_file.id') => [
                    'type' => 'insurance'
                ]
            ]);
        }

        if ($request->filled('qualification_file')) {
            $instructor->qualification_file()->sync([
                $request->input('qualification_file.id') => [
                    'type' => 'qualification'
                ]
            ]);
        }

        if ($request->filled('document_file')) {
            $instructor->document_file()->sync([
                $request->input('document_file.id') => [
                    'type' => 'document'
                ]
            ]);
        }

        return response()->json([
            'data' => $instructor->fresh('classes', 'insurance_file', 'qualification_file', 'document_file'),
            'message' => 'Instructor has been update successfully!',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Coderstm\Core\Models\Instructor  $instructor
     * @return \Illuminate\Http\Response
     */
    public function destroy(Instructor $instructor)
    {
        $instructor->delete();
        return response()->json([
            'message' => 'Instructor has been deleted successfully!',
        ], 200);
    }

    /**
     * Remove the selected resource from storage.
     *
     * @param  \Coderstm\Core\Models\Instructor  $instructor
     * @return \Illuminate\Http\Response
     */
    public function destroy_selected(Request $request, Instructor $instructor)
    {
        $this->validate($request, [
            'items' => 'required',
        ]);
        $instructor->whereIn('id', $request->items)->each(function ($item) {
            $item->delete();
        });
        return response()->json([
            'message' => 'Instructors has been deleted successfully!',
        ], 200);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param  \Coderstm\Core\Models\Instructor  $instructor
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        Instructor::onlyTrashed()
            ->where('id', $id)->each(function ($item) {
                $item->restore();
            });
        return response()->json([
            'message' => 'Instructor has been restored successfully!',
        ], 200);
    }

    /**
     * Remove the selected resource from storage.
     *
     * @param  \Coderstm\Core\Models\Instructor  $instructor
     * @return \Illuminate\Http\Response
     */
    public function restore_selected(Request $request, Instructor $instructor)
    {
        $this->validate($request, [
            'items' => 'required',
        ]);
        $instructor->onlyTrashed()
            ->whereIn('id', $request->items)->each(function ($item) {
                $item->restore();
            });
        return response()->json([
            'message' => 'Instructors has been restored successfully!',
        ], 200);
    }
}
