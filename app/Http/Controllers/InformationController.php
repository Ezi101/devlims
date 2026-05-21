<?php

namespace App\Http\Controllers;

use App\PTR;
use App\STR;
use App\User;
use App\Signature;
use App\PTR_STR_Approval;
use Illuminate\Http\Request;

class InformationController extends Controller
{
    public function showByPtr($ptr_no)
    {
        $business_id = request()->session()->get('user.business_id');

        // Retrieve approver IDs for the given PTR number
        $approver_ids_ptr = PTR_STR_Approval::where('ptr/str_no', $ptr_no)
            ->pluck('remark_by');

        // Ensure the approver IDs are unique
        $approver_ids = $approver_ids_ptr->unique();
        // dd($approver_ids);
        // Retrieve signatures of the approvers
        $signatures = Signature::whereIn('employee_id', $approver_ids)->pluck('unique_signature');
        // Retrieve the most recent approval time
        $approvalTime = PTR_STR_Approval::where('ptr/str_no', $ptr_no)
            ->orderBy('remark_date_time', 'desc')
            ->first(['remark_date_time']);

        // Retrieve the approver's ID and user object
        $approverRecord = PTR_STR_Approval::where('ptr/str_no', $ptr_no)
            ->where('remark_status', 'approved')
            ->orderBy('remark_date_time', 'desc')
            ->first(['remark_by']);

        $approverUser = $approverRecord ? User::find($approverRecord->remark_by) : null;

        // Pass the data to the view
        return view('information-page', compact('signatures', 'approvalTime', 'approverUser', 'ptr_no'));
    }


    public function showByStr($str_no)
    {
        $business_id = request()->session()->get('user.business_id');

        // Retrieve approver IDs for the given PTR number
        $approver_ids_str = PTR_STR_Approval::where('ptr/str_no', $str_no)
            ->pluck('remark_by');

        // Ensure the approver IDs are unique
        $approver_ids = $approver_ids_str->unique();

        // Retrieve signatures of the approvers
        $signatures = Signature::whereIn('employee_id', $approver_ids)->get();

        // Retrieve the most recent approval time
        $approvalTime = PTR_STR_Approval::where('ptr/str_no', $str_no)
            ->orderBy('remark_date_time', 'desc')
            ->first(['remark_date_time']);

        // Retrieve the approver's ID and user object
        $approverRecord = PTR_STR_Approval::where('ptr/str_no', $str_no)
            ->where('remark_status', 'approved')
            ->orderBy('remark_date_time', 'desc')
            ->first(['remark_by']);

        $approverUser = $approverRecord ? User::find($approverRecord->remark_by) : null;

        // Pass the data to the view
        return view('information-page', compact('signatures', 'approvalTime', 'approverUser', 'str_no'));
    }
}
