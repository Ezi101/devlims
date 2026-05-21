<?php

namespace App\Http\Controllers;

use App\Utils\ModuleUtil;
use App\SellingPriceGroup;
use App\Helpers\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $moduleUtil;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('roles.view')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $roles = Role::where('business_id', $business_id)
                ->select(['name', 'id', 'is_default', 'business_id']);

            return DataTables::of($roles)
                ->addColumn('action', function ($row) {
                    if (!$row->is_default || $row->name == 'Cashier#' . $row->business_id) {
                        $action = '';
                        if (auth()->user()->can('roles.update')) {
                            $action .= '<a href="' . action([\App\Http\Controllers\RoleController::class, 'edit'], [$row->id]) . '" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a>';
                        }
                        if (auth()->user()->can('roles.delete')) {
                            $action .= '&nbsp
                                <button data-href="' . action([\App\Http\Controllers\RoleController::class, 'destroy'], [$row->id]) . '" class="btn btn-xs btn-danger delete_role_button"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        }

                        return $action;
                    } else {
                        return '';
                    }
                })
                ->editColumn('name', function ($row) use ($business_id) {
                    $role_name = str_replace('#' . $business_id, '', $row->name);
                    if (in_array($role_name, ['Admin', 'Cashier'])) {
                        $role_name = __('lang_v1.' . $role_name);
                    }

                    return $role_name;
                })
                ->removeColumn('id')
                ->removeColumn('is_default')
                ->removeColumn('business_id')
                ->rawColumns([1])
                ->make(false);
        }

        return view('role.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('roles.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $selling_price_groups = SellingPriceGroup::where('business_id', $business_id)
            ->active()
            ->get();

        $module_permissions = $this->moduleUtil->getModuleData('user_permissions');

        $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
        $created_permissions = Permission::where('id', '>', '102')->get();
        $created_permissions = DB::table('permissions')->where('id', '>', 102)
            ->select(DB::raw("SUBSTRING_INDEX(name, '.', 1) AS permission_group"))
            ->selectRaw("GROUP_CONCAT(name) AS permissions_in_group")
            ->groupBy('permission_group')
            ->get();

        return view('role.create')
            ->with(compact('selling_price_groups', 'module_permissions', 'common_settings', 'created_permissions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('roles.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $role_name = $request->input('name');
            $permissions = $request->input('permissions');
            $business_id = $request->session()->get('user.business_id');

            $count = Role::where('name', $role_name . '#' . $business_id)
                ->where('business_id', $business_id)
                ->count();
            if ($count == 0) {
                $is_service_staff = 0;
                if ($request->input('is_service_staff') == 1) {
                    $is_service_staff = 1;
                }

                $role = Role::create([
                    'name' => $role_name . '#' . $business_id,
                    'business_id' => $business_id,
                    'is_service_staff' => $is_service_staff,
                ]);
                AuditLogger::log('created', 'Role', 'Role Name: ' . $role->name);

                //Include selling price group permissions
                $spg_permissions = $request->input('radio_option');
                if (!empty($spg_permissions)) {
                    foreach ($spg_permissions as $spg_permission) {
                        $permissions[] = $spg_permission;
                    }
                }

                $radio_options = $request->input('radio_option');
                if (!empty($radio_options)) {
                    foreach ($radio_options as $key => $value) {
                        $permissions[] = $value;
                    }
                }

                $this->__createPermissionIfNotExists($permissions);

                if (!empty($permissions)) {
                    $role->syncPermissions($permissions);
                }
                $output = [
                    'success' => 1,
                    'msg' => __('user.role_added'),
                ];
            } else {
                $output = [
                    'success' => 0,
                    'msg' => __('user.role_already_exists'),
                ];
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect('roles')->with('status', $output);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('roles.update')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $role = Role::where('business_id', $business_id)
            ->with(['permissions'])
            ->find($id);
        $role_permissions = [];
        foreach ($role->permissions as $role_perm) {
            $role_permissions[] = $role_perm->name;
        }

        $selling_price_groups = SellingPriceGroup::where('business_id', $business_id)
            ->active()
            ->get();

        $module_permissions = $this->moduleUtil->getModuleData('user_permissions');

        $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
        $exising_permissions = Permission::pluck('name', 'id')->toArray();
        $created_permissions = DB::table('permissions')
        ->where('id', '>', 129)
        ->where('name', 'not like', 'location.%') // This excludes any values related to 'location'
        ->select(DB::raw("SUBSTRING_INDEX(name, '.', 1) AS permission_group"))
        ->selectRaw("GROUP_CONCAT(name) AS permissions_in_group")
        ->groupBy('permission_group')
        ->get();
    

        return view('role.edit')
            ->with(compact('role', 'role_permissions', 'selling_price_groups', 'module_permissions', 'common_settings', 'exising_permissions', 'created_permissions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('roles.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $role_name = $request->input('name');
            $permissions = $request->input('permissions');
            $business_id = $request->session()->get('user.business_id');

            $count = Role::where('name', $role_name . '#' . $business_id)
                ->where('id', '!=', $id)
                ->where('business_id', $business_id)
                ->count();
            if ($count == 0) {
                $role = Role::findOrFail($id);
                AuditLogger::log('updated', 'Role', 'Role Name: <b>' . $role->name . '</b> was updated');

                if (!$role->is_default || $role->name == 'Cashier#' . $business_id) {
                    if ($role->name == 'Cashier#' . $business_id) {
                        $role->is_default = 0;
                    }

                    $is_service_staff = 0;
                    if ($request->input('is_service_staff') == 1) {
                        $is_service_staff = 1;
                    }
                    $role->is_service_staff = $is_service_staff;
                    $role->name = $role_name . '#' . $business_id;
                    $role->save();

                    //Include selling price group permissions
                    $spg_permissions = $request->input('spg_permissions');
                    if (!empty($spg_permissions)) {
                        foreach ($spg_permissions as $spg_permission) {
                            $permissions[] = $spg_permission;
                        }
                    }

                    $radio_options = $request->input('radio_option');
                    if (!empty($radio_options)) {
                        foreach ($radio_options as $key => $value) {
                            $permissions[] = $value;
                        }
                    }

                    $this->__createPermissionIfNotExists($permissions);

                    if (!empty($permissions)) {
                        $role->syncPermissions($permissions);
                    }

                    $output = [
                        'success' => 1,
                        'msg' => __('user.role_updated'),
                    ];
                } else {
                    $output = [
                        'success' => 0,
                        'msg' => __('user.role_is_default'),
                    ];
                }
            } else {
                $output = [
                    'success' => 0,
                    'msg' => __('user.role_already_exists'),
                ];
            }
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return back()->with('status', $output);
        // return redirect('roles')->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('roles.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->user()->business_id;

                $role = Role::where('business_id', $business_id)->find($id);
                AuditLogger::log('deleted', 'Role', 'Role Name: ' . $role->name);

                if (!$role->is_default || $role->name == 'Cashier#' . $business_id) {
                    $role->delete();
                    $output = [
                        'success' => true,
                        'msg' => __('user.role_deleted'),
                    ];
                } else {
                    $output = [
                        'success' => 0,
                        'msg' => __('user.role_is_default'),
                    ];
                }
            } catch (\Exception $e) {
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Creates new permission if doesn't exist
     *
     * @param  array  $permissions
     * @return void
     */
    private function __createPermissionIfNotExists($permissions)
    {
        $exising_permissions = Permission::whereIn('name', $permissions)
            ->pluck('name')
            ->toArray();

        $non_existing_permissions = array_diff($permissions, $exising_permissions);

        if (!empty($non_existing_permissions)) {
            foreach ($non_existing_permissions as $new_permission) {
                $time_stamp = \Carbon::now()->toDateTimeString();
                Permission::create([
                    'name' => $new_permission,
                    'guard_name' => 'web',
                ]);
            }
        }
    }

    public function permission_create()
    {
        if (!auth()->user()->can('roles.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('role.partials.permission');
    }
    public function permission_store(Request $request)
    {
        if (!auth()->user()->can('roles.create')) {
            abort(403, 'Unauthorized action.');
        }
        // dd($request->request);
        try {
            $time_stamp = now()->toDateTimeString();
            $permission = new Permission;
            $permission->name = $request->permission;
            $permission->guard_name = 'web';
            $permission->save();

            // Log creation event
            AuditLogger::log('created', 'Permission', 'Permission ID: ' . $permission->id);

            return back()->with('status', [
                'success' => 1,
                'msg' => __('role.permission'),
            ]);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            return back()->with('status', [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ]);
        }
    }
}
