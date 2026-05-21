<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatabaseUpdateController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    // DatabaseUpdateController.php mein ye naya function add karein
    public function getProducts(Request $request)
    {
        $search = $request->get('q');

        // Product aur Samples dono se data fetch karein
        $products = DB::table('products') // Apne table ka sahi naam check kar lein
            ->where('name', 'LIKE', "%$search%")
            ->select('id', 'name as text')
            ->limit(10)
            ->get();

        return response()->json($products);
    }
    // public function showForm() {
    //     // Purane check ko hata kar apni nayi permission check lagayein
    //     if (!auth()->user()->can('product.replace_id') && auth()->user()->id !== 1) { 
    //         abort(403, 'You don\'t have permission to do this.');
    //     }
    //     return view('database_update.form');
    // }

    public function showForm()
    {
        // Permission check
        if (auth()->user()->id !== 1 && !auth()->user()->can('product.replace_id')) {
            abort(403, 'You don\'t have permission to access this page.');
        }

        // Sirf products fetch karein kyunke samples table nahi hai
        $items = DB::table('products')
            ->select('id', DB::raw("CONCAT(name, ' (ID: ', id, ')') as name"))
            ->pluck('name', 'id');

        return view('database_update.form', compact('items'));
    }

    // public function updateIds(Request $request) {
    //     $oldId = $request->old_id;
    //     $newId = $request->new_id;
    //     $totalAffected = 0;

    //     // Just those table where product_id and sample_id exist
    //     $tables = [
    //         'batch' => 'sample_id',
    //         'contracts' => 'sample_id',
    //         'deviations' => 'sample_id',
    //         'messageboxes' => 'product_id',
    //         'new_methods' => 'sample_id',
    //         'p_t_r_s' => 'sample_id',
    //         'pjt_projects' => 'product_id',
    //         'product_generic_name' => 'product_id',
    //         'ptr_str_approval' => 'product_id',
    //         'purchase_checklists' => 'product_id',
    //         'purchase_lines' => 'product_id',
    //         's_t_r' => 'sample_id',
    //         'sample_readings' => 'product_id',
    //         'sample_test_types' => 'sample_id',
    //         'samples_and_tests' => 'sample_id',
    //         'test_batches' => 'sample_id',
    //         'transaction_sell_lines' => 'product_id',
    //     ];

    //     DB::beginTransaction(); // For safety we use transaction
    //     try {
    //         DB::statement('SET FOREIGN_KEY_CHECKS = 0');

    //         foreach ($tables as $table => $column) {
    //             $affected = DB::table($table)
    //                 ->where($column, $oldId)
    //                 ->update([$column => $newId]);

    //             $totalAffected += $affected;
    //         }

    //         DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    //         DB::commit();

    //         if($totalAffected > 0) {
    //             return back()->with('success', "Success! $totalAffected records have been updated from ID $oldId to $newId.");
    //         } else {
    //             return back()->with('error', "No records were found for ID $oldId.");
    //         }

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    //         return back()->with('error', "Error: " . $e->getMessage());
    //     }
    // }
    // public function updateIds(Request $request)
    // {
    //     // Permission check for ID 1 and permission holder
    //     if (auth()->user()->id !== 1 && !auth()->user()->can('product.replace_id')) {
    //         return response()->json(['error' => 'Unauthorized'], 403);
    //     }

    //     $oldId = $request->old_id;
    //     $newId = $request->new_id;
    //     $results = [];

    //     $tables = [
    //         'batch' => 'sample_id',
    //         'contracts' => 'sample_id',
    //         'deviations' => 'sample_id',
    //         'messageboxes' => 'product_id',
    //         'new_methods' => 'sample_id',
    //         'p_t_r_s' => 'sample_id',
    //         'pjt_projects' => 'product_id',
    //         'product_generic_name' => 'product_id',
    //         'ptr_str_approval' => 'product_id',
    //         'purchase_checklists' => 'product_id',
    //         'purchase_lines' => 'product_id',
    //         's_t_r' => 'sample_id',
    //         'sample_readings' => 'product_id',
    //         'sample_test_types' => 'sample_id',
    //         'samples_and_tests' => 'sample_id',
    //         'test_batches' => 'sample_id',
    //         'transaction_sell_lines' => 'product_id',
    //     ];

    //     DB::beginTransaction();
    //     try {
    //         DB::statement('SET FOREIGN_KEY_CHECKS = 0');
    //         foreach ($tables as $table => $column) {
    //             $affected = DB::table($table)->where($column, $oldId)->update([$column => $newId]);
    //             $results[] = ['table' => $table, 'status' => 'success', 'affected' => $affected];
    //         }
    //         DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    //         DB::commit();

    //         return response()->json(['success' => true, 'details' => $results]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['success' => false, 'message' => $e->getMessage()]);
    //     }
    // }

    public function updateIds(Request $request)
    {
        // Permission check
        if (auth()->user()->id !== 1 && !auth()->user()->can('product.replace_id')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $oldId = $request->old_id;
        $newId = $request->new_id;
        $results = [];
        $logDetails = []; // Sirf affected tables store karne ke liye

        // 1. Logs ke liye products ka naam pehle hi nikal lein
        $oldProduct = DB::table('products')->where('id', $oldId)->first();
        $newProduct = DB::table('products')->where('id', $newId)->first();

        if (!$oldProduct || !$newProduct) {
            return response()->json(['success' => false, 'message' => 'Old or New Product not found!']);
        }

        $tables = [
            'batch' => 'sample_id',
            'contracts' => 'sample_id',
            'deviations' => 'sample_id',
            'messageboxes' => 'product_id',
            'new_methods' => 'sample_id',
            'p_t_r_s' => 'sample_id',
            'pjt_projects' => 'product_id',
            'product_generic_name' => 'product_id',
            'ptr_str_approval' => 'product_id',
            'purchase_checklists' => 'product_id',
            'purchase_lines' => 'product_id',
            's_t_r' => 'sample_id',
            'sample_readings' => 'product_id',
            'sample_test_types' => 'sample_id',
            'samples_and_tests' => 'sample_id',
            'test_batches' => 'sample_id',
            'transaction_sell_lines' => 'product_id',
        ];

        DB::beginTransaction();
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');

            foreach ($tables as $table => $column) {
                $affected = DB::table($table)->where($column, $oldId)->update([$column => $newId]);

                $results[] = ['table' => $table, 'status' => 'success', 'affected' => $affected];

                // Agar kisi table mein record update hua hai toh details mein add karein
                if ($affected > 0) {
                    $logDetails[] = "$table ($affected)";
                }
            }

            // 2. LOG ENTRY INSERT (Using your Model)
            \App\ProductIdReplacementLog::create([
                'old_product_id'   => $oldId,
                'old_product_name' => $oldProduct->name,
                'new_product_id'   => $newId,
                'new_product_name' => $newProduct->name,
                'update_details'   => !empty($logDetails) ? implode(', ', $logDetails) : 'No related records found',
                'updated_by'       => auth()->user()->id,
            ]);

            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            DB::commit();

            return response()->json(['success' => true, 'details' => $results]);
        } catch (\Exception $e) {
            DB::rollBack();
            DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // Ensure checks are back on
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getHistory()
    {
        if (auth()->user()->id !== 1 && !auth()->user()->can('product.replace_id')) {
            abort(403, 'You don\'t have permission to access this page.');
        }
        $logs = \App\ProductIdReplacementLog::with('user')->latest()->paginate(10);
        return view('database_update.history', compact('logs'));
    }
}
