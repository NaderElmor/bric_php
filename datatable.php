<?php
// This method is responsible of loading the data of the datatables, for each table in the system we have a method like this, PS: This is a controller method
public function datatable()
{
    $grid = GridTable::getGrid('datatable');
    $grid->total = DB::table('roa_companies')->whereNull('deleted_at')->count();

    $query = $this->query = DB::table('roa_companies as rc')->whereNull('rc.deleted_at');
    $query->leftJoin('contacts AS c', 'c.id', '=', 'rc.contact_id');
    $query->select([
        'rc.id', 'rc.contact_id', 'rc.status', 'rc.recog_first', 'rc.recog_last', 'rc.recog_count', 'rc.responsible', 'rc.created_by',
        'rc.has_relations', 'rc.has_bpv', 'rc.has_bpv_sig', 'rc.has_job_trainers', 'rc.has_recognitions', 'rc.has_plaque',
        'c.label AS contact', 'c.country', 'c.email AS contact_email'
    ]);

    //----------------------------------------
    // WHERE/LIKE
    //----------------------------------------
    if (Request::filled('global_search') != false) {
        $query->where('c.label', 'LIKE', '%' . Request::get('global_search') . '%');
    }

    // retrieves the filter search input as arrays
    $filters = Request::get('filters', []);

    // individiually apply each filter (if multiple filters exist)
    foreach ($filters as $filter) {
        $this->applyDtFilter($filter);
    }

    //----------------------------------------
    // Sort By
    //----------------------------------------
    foreach ($grid->post['order'] as $ord) {
        $col = $grid->colsInv[$ord['column']];
        $sort = $ord['dir'];

        // and array from with key $col and value 'rc.id' etc would be better
        switch ($col) {
            case 'id':
                $query->orderBy('rc.id', $sort);
                break;
            case 'company':
                $query->orderBy('c.label', $sort);
                break;
            case 'status':
                $query->orderBy('rc.status', $sort);
                break;
            case 'recog_first':
                $query->orderBy('rc.recog_first', $sort);
                break;
            case 'recog_last':
                $query->orderBy('rc.recog_last', $sort);
                break;
            case 'recog_count':
                $query->orderBy('rc.recog_count', $sort);
                break;
            case 'responsible':
                $query->orderBy('rc.responsible', $sort);
                break;
            case 'has_relations':
                $query->orderBy('rc.has_relations', $sort);
                break;
            case 'has_bpv':
                $query->orderBy('rc.has_bpv', $sort);
                break;
            case 'has_bpv_sig':
                $query->orderBy('rc.has_bpv_sig', $sort);
                break;
            case 'has_job_trainers':
                $query->orderBy('rc.has_job_trainers', $sort);
                break;
            case 'has_recognitions':
                $query->orderBy('rc.has_recognitions', $sort);
                break;
            case 'has_plaque':
                $query->orderBy('rc.has_plaque', $sort);
                break;
        }
    }

    if (empty($sorts)) {
        $query->orderBy('rc.id', 'desc');
    }

    //----------------------------------------
    // OFFSET & LIMIT & EXECUTE!
    //----------------------------------------
    $grid->setLimitOffset($query);
    $grid->getQueryTotal($query);
    $results = $query->get();

    // same point about coupling.
    $users = User::getList();

    //----------------------------------------
    // Loop Over all
    //----------------------------------------
    foreach ($results as $row) {
        $data = new \stdClass;
        $data->id = "<a href='/contacts/{$row->contact_id}/category/2'>{$row->id}</a>";
        $data->company = $row->contact;
        $data->email = $row->contact_email;
        $data->country = $row->country;
        $data->status = $row->status;
        $data->recog_first = Localize::convertDate($row->recog_first, true, true);
        $data->recog_last = Localize::convertDate($row->recog_last, true, true);
        $data->recog_count = $row->recog_count;
        $data->responsible = isset($users[$row->responsible]) ? $users[$row->responsible] : User::findAuthor($row->responsible);
        $data->created_by = isset($users[$row->created_by]) ? $users[$row->created_by] : User::findAuthor($row->created_by);

        // function that converts boolean to 'Yes' 'No' better than all this repetition
        $data->has_relations = $row->has_relations ? 'Yes' : 'No';
        $data->has_bpv = $row->has_bpv ? 'Yes' : 'No';
        $data->has_bpv_sig = $row->has_bpv_sig ? 'Yes' : 'No';
        $data->has_job_trainers = $row->has_job_trainers ? 'Yes' : 'No';
        $data->has_recognitions = $row->has_recognitions ? 'Yes' : 'No';
        $data->has_plaque = $row->has_plaque ? 'Yes' : 'No';

        $data->country = 'N/A';

        // array with key => value pairs would be btter here
        if ($row->country == 'bq-b') {
            $data->country = 'Bonaire';
        } elseif ($row->country == 'bq-s') {
            $data->country = 'Saba';
        } elseif ($row->country == 'bq-e') {
            $data->country = 'St. Eustatius';
        } elseif ($row->country == 'cw') {
            $data->country = 'Curacao';
        }


        // Custom TD Classes
        $data->TD_attr = [];
        $data->TD_attr['has_relations']['class'] = $row->has_relations ? 'roa-success' : 'roa-danger';
        $data->TD_attr['has_bpv']['class'] = $row->has_bpv ? 'roa-success' : 'roa-danger';
        $data->TD_attr['has_bpv_sig']['class'] = $row->has_bpv_sig ? 'roa-success' : 'roa-danger';
        $data->TD_attr['has_job_trainers']['class'] = $row->has_job_trainers ? 'roa-success' : 'roa-danger';
        $data->TD_attr['has_recognitions']['class'] = $row->has_recognitions ? 'roa-success' : 'roa-danger';
        $data->TD_attr['has_plaque']['class'] = $row->has_plaque ? 'roa-success' : 'roa-danger';

        // Status
        // again, use array to map, bettern than if/else and switch.
        switch ($row->status) {
            case 'prospect':
                $data->status = 'Prospect';
                $data->TD_attr['status']['class'] = 'roa-warning';
                break;
            case 'inprogress':
                $data->status = 'In Progress';
                $data->TD_attr['status']['class'] = 'roa-info';
                break;
            case 'active':
                $data->status = 'Active';
                $data->TD_attr['status']['class'] = 'roa-success';
                break;
            case 'inactive':
                $data->status = 'Inactive';
                $data->TD_attr['status']['class'] = 'roa-danger';
                break;
            default:
                $data->status = '';
                break;
        }

        // Add to data
        $grid->addRow($data);
    }

    return response()->json($grid);
}
