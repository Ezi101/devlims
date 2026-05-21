<?php

namespace App\Http\Controllers;

use Log;
use App\Messagebox;
use App\Utils\Util;
use App\Utils\ModuleUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Project\Entities\Project;
use Modules\Project\Utils\ProjectUtil;
use Modules\Project\Entities\ProjectTask;
use Yajra\DataTables\Exceptions\Exception;
use Modules\Project\Entities\ProjectMember;

class MessageboxController extends Controller
{
    protected $commonUtil;

    protected $projectUtil;

    protected $moduleUtil;


    public function __construct(Util $commonUtil, ProjectUtil $projectUtil, ModuleUtil $moduleUtil)
    {
        $this->commonUtil = $commonUtil;
        $this->projectUtil = $projectUtil;
        $this->moduleUtil = $moduleUtil;
        $this->priority_colors = ProjectTask::priorityColors();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $id = $request->input('id');
        $project_id = $id;
        $business_id = request()->session()->get('user.business_id');
        $project_members = ProjectMember::projectMembersDropdown($project_id);
        $priorities = ProjectTask::prioritiesDropdown();
        $statuses = ProjectTask::taskStatuses();
        $projects = Project::with('customer', 'members', 'lead', 'categories')->where('business_id', $business_id)->where('product_id', '=', $project_id)->pluck('name', 'id');
        return view('product.product dashbord view.model.marked_to')
            ->with(compact('priorities', 'project_id', 'project_members', 'statuses', 'projects'));
    }

    public function getProjectMembers($projectId)
    {
        $project_members = ProjectMember::projectMembersDropdown($projectId);
        return response()->json($project_members);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        try {

            $messagebox = Messagebox::create([
                "business_id" => $business_id,
                "product_id" => $request->product_id,
                "project_id" => $request->project_id,
                "title" => $request->title,
                "note" => $request->description,
                "marked_by" => Auth::user()->id,
                "marked_to" => $request->user_id,
            ]);

            $input = $request->only('project_id', 'description', 'priority', 'custom_field_1', 'custom_field_2', 'custom_field_3', 'custom_field_4', 'status');
            $input['start_date'] = !empty($request->input('start_date')) ? $this->commonUtil->uf_date($request->input('start_date')) : null;
            $input['due_date'] = !empty($request->input('due_date')) ? $this->commonUtil->uf_date($request->input('due_date')) : null;
            $input['created_by'] = $request->user()->id;
            $input['subject'] = $request->title;
            $input['business_id'] = request()->session()->get('user.business_id');
            $input['task_id'] = $this->projectUtil->generateTaskId($input['business_id'], $input['project_id']);
            $members = $request->input('user_id');

            $project_task = ProjectTask::create($input);
            $task_members = $project_task->members()->sync($members);

            // send notification to task members
            if (!empty($task_members['attached'])) {
                //check if user is a creator then don't notify him
                foreach ($task_members['attached'] as $key => $value) {
                    if ($value == $project_task->created_by) {
                        unset($task_members['attached'][$key]);
                    }
                }

                //Used for broadcast notification
                $project_task['title'] = __('project::lang.task');
                $project_task['body'] = strip_tags(__(
                    'project::lang.new_task_assgined_notification',
                    [
                        'created_by' => $request->user()->user_full_name,
                        'subject' => $project_task->subject,
                        'task_id' => $project_task->task_id,
                    ]
                ));
                $project_task['link'] = action([\Modules\Project\Http\Controllers\ProjectController::class, 'show'], [$project_task->project_id]);

                $this->projectUtil->notifyUsersAboutAssignedTask($task_members['attached'], $project_task);
            }


            DB::commit();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.success'),
            ];
        } catch (Exception $e) {
            dd($e);
            DB::rollBack();

            Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Messagebox  $messagebox
     * @return \Illuminate\Http\Response
     */
    public function show(Messagebox $messagebox)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Messagebox  $messagebox
     * @return \Illuminate\Http\Response
     */
    public function edit(Messagebox $messagebox)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Messagebox  $messagebox
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Messagebox $messagebox)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Messagebox  $messagebox
     * @return \Illuminate\Http\Response
     */
    public function destroy(Messagebox $messagebox)
    {
        //
    }
}
