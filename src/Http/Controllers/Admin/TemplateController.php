<?php

namespace Coderstm\Core\Http\Controllers\Admin;

use Coderstm\Core\Models\Template;
use Coderstm\Core\Models\WeekTemplate;
use Illuminate\Http\Request;
use Coderstm\Core\Models\ClassSchedule;
use Coderstm\Core\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TemplateController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Template::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Template $template)
    {
        $template = $template->query();

        if ($request->filled('filter')) {
            $template->where('label', 'like', "%{$request->filter}%");
        }

        if ($request->boolean('active')) {
            $template->onlyActive();
        }

        if ($request->boolean('deleted')) {
            $template->onlyTrashed();
        }

        $template = $template->orderBy(optional($request)->sortBy ?? 'created_at', optional($request)->direction ?? 'desc')
            ->paginate(optional($request)->rowsPerPage ?? 15);
        return new ResourceCollection($template);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Template $template)
    {
        $rules = [
            'label' => 'required',
        ];

        // Validate those rules
        $this->validate($request, $rules);

        // create the template
        $template = Template::create($request->input());

        if ($request->filled('schedules')) {
            $template->syncSchedules(collect($request->schedules));
        }

        return response()->json([
            'data' => $template->fresh('schedules'),
            'message' => 'Template has been created successfully!',
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Coderstm\Core\Models\Template  $template
     * @return \Illuminate\Http\Response
     */
    public function show(Template $template)
    {
        return response()->json($template->load('schedules'), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Coderstm\Core\Models\Template  $template
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Template $template)
    {

        $rules = [
            'label' => 'required',
        ];

        // Validate those rules
        $this->validate($request, $rules);

        // update the template
        $template->update($request->input());

        if ($request->filled('schedules')) {
            $template->syncSchedules(collect($request->schedules));
        }

        WeekTemplate::onlyActive()->whereTemplateId($template->id)->each(function ($weekTemplate) {
            $weekTemplate->template->schedules()->each(function ($schedule) use ($weekTemplate) {
                ClassSchedule::updateOrCreate([
                    'day' => $schedule->day ? $schedule->day->value : null,
                    'start_at' => $schedule->start_at,
                    'end_at' => $schedule->end_at,
                    'template_id' => $schedule->template_id,
                    'start_of_week' => $weekTemplate->start_of_week
                ], [
                    'class_id' => $schedule->class_id,
                    'location_id' => $schedule->location_id,
                    'instructor_id' => $schedule->instructor_id,
                ]);
            });
        });

        return response()->json([
            'data' => $template->fresh('schedules'),
            'message' => 'Template has been update successfully!',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Coderstm\Core\Models\Template  $template
     * @return \Illuminate\Http\Response
     */
    public function destroy(Template $template)
    {
        $template->delete();
        return response()->json([
            'message' => 'Template has been deleted successfully!',
        ], 200);
    }

    /**
     * Remove the selected resource from storage.
     *
     * @param  \Coderstm\Core\Models\Template  $template
     * @return \Illuminate\Http\Response
     */
    public function destroy_selected(Request $request, Template $template)
    {
        $this->validate($request, [
            'items' => 'required',
        ]);
        $template->whereIn('id', $request->items)->each(function ($item) {
            $item->delete();
        });
        return response()->json([
            'message' => 'Templates has been deleted successfully!',
        ], 200);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param  \Coderstm\Core\Models\Template  $template
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        Template::onlyTrashed()
            ->where('id', $id)->each(function ($item) {
                $item->restore();
            });
        return response()->json([
            'message' => 'Template has been restored successfully!',
        ], 200);
    }

    /**
     * Remove the selected resource from storage.
     *
     * @param  \Coderstm\Core\Models\Template  $template
     * @return \Illuminate\Http\Response
     */
    public function restore_selected(Request $request, Template $template)
    {
        $this->validate($request, [
            'items' => 'required',
        ]);
        $template->onlyTrashed()
            ->whereIn('id', $request->items)->each(function ($item) {
                $item->restore();
            });
        return response()->json([
            'message' => 'Templates has been restored successfully!',
        ], 200);
    }

    /**
     * Change active of specified resource from storage.
     *
     * @param  \Coderstm\Core\Models\Template  $template
     * @return \Illuminate\Http\Response
     */
    public function changeActive(Request $request, Template $template)
    {
        $template->update([
            'is_active' => !$template->is_active
        ]);

        return response()->json([
            'message' => $template->is_active ? 'Template marked as active successfully!' : 'Template marked as deactivated successfully!',
        ], 200);
    }

    /**
     * Make duplicate of specified resource from storage.
     *
     * @param  \Coderstm\Core\Models\Template  $template
     * @return \Illuminate\Http\Response
     */
    public function duplicate(Request $request, Template $template)
    {
        $template = $template->duplicate();

        return response()->json([
            'data' => $template->fresh('schedules'),
            'message' => 'Template has been duplicated successfully!',
        ], 200);
    }
}
