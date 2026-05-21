<?php

namespace App\Observers;

use Illuminate\Support\Facades\DB;

class GeneralObserver
{
    // public function created($model)
    // {
    //     $this->syncToBackup($model);
    // }

    // public function updated($model)
    // {
    //     $this->syncToBackup($model);
    // }

    // public function deleted($model)
    // {
    //     $this->syncToBackup($model, 'delete');
    // }

    // protected function syncToBackup($model, $action = 'update')
    // {
    //     // Assuming the model uses the same table name in the backup
    //     $table = $model->getTable();

    //     if ($action === 'delete') {
    //         DB::connection('mysql_second')->table($table)->where('id', $model->id)->delete();
    //     } else {
    //         DB::connection('mysql_second')->table($table)->updateOrInsert(
    //             ['id' => $model->id],
    //             $model->getAttributes()
    //         );
    //     }
    // }
}
