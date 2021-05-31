<?php

// This method is part of a controller method, is responsible of rendering the index view so the table with the items can be loaded, there are over 150 different tables on the system and to render them this piece of code is needed
public function index()
{
    $data = [];
    $data['rightbar'] = true;
    $data['item_type'] = 'roa.companies';

    $departments = [];

    foreach (RoaDepartment::orderBy('label', 'asc')->get() as $dep) {
        $departments[$dep->id] = $dep->label;
    }

    // List of Dossiers
    $dossiers = [];

    foreach (RoaDossier::orderBy('label', 'asc')->get() as $dos) {
        $dossiers[$dos->id] = $dos->label;
    }

    $temp = [];
    $users = [];
    $users['__NULL__'] = '[UNASSIGNED]';
    $users['1'] = '[CAAG]';
    foreach (DB::table('roa_company_recognitions')->groupBy('awarded_by')->select(['awarded_by'])->get() as $row) {
        $temp[] = $row->awarded_by;
    }

    foreach (DB::table('roa_company_recognitions')->groupBy('created_by')->select(['created_by'])->get() as $row) {
        $temp[] = $row->created_by;
    }

    $temp = array_unique($temp);
    foreach ($temp as $user_id) {
        if ($user_id > 1) {
            // a separate query for each record is inefficient.
            // maybe all the code above can be a single query?
            // instead of 2 queries above, and N queries here
            $users[$user_id] = User::findAuthor($user_id);
        }
    }

    $columns = [];
    $columns[] = ['name' => 'id',           'label' => 'ID&nbsp;&nbsp;&nbsp;', 'width' => 40];
    $columns[] = ['name' => 'company',      'label' => 'Company'];
    $columns[] = ['name' => 'email',        'label' => 'Email', 'visible' => false];
    $columns[] = ['name' => 'country',      'label' => 'Island', 'sortable' => false, 'width' => 100];
    $columns[] = ['name' => 'status',       'label' => 'Status', 'width' => 100];
    $columns[] = ['name' => 'recog_first',  'label' => 'First Recog.', 'width' => 90];
    $columns[] = ['name' => 'recog_last',   'label' => 'Last Recog.', 'width' => 90, 'visible' => false];
    $columns[] = ['name' => 'recog_count',  'label' => 'Total Recog.', 'width' => 90, 'visible' => false];
    $columns[] = ['name' => 'responsible',  'label' => 'Responsible', 'sortable' => false, 'visible' => false];
    $columns[] = ['name' => 'has_relations','label' => 'Relations', 'width' => 100];
    $columns[] = ['name' => 'has_bpv',      'label' => 'BPV', 'width' => 100];
    $columns[] = ['name' => 'has_bpv_sig',  'label' => 'BPV Sign.', 'width' => 100];
    $columns[] = ['name' => 'has_job_trainers',  'label' => 'Job Trainers', 'width' => 100];
    $columns[] = ['name' => 'has_recognitions',  'label' => 'Recognitions', 'width' => 100];
    $columns[] = ['name' => 'has_plaque',        'label' => 'Plaque', 'width' => 100];


    // Datatables config
    $dt = [];
    $dt['name'] = 'roa.companies';
    $dt['url'] = "/roa/companies/datatable";
    $dt['columns'] = $columns;
    $data['datatableConfig'] = json_encode($dt);

    //----------------------------------------
    // Filter: Fields
    //----------------------------------------
    $items = [];
    $items['static-contact_id'] = ['label' => 'Company',    'group' => 'Company', 'operator_type' => 'multiselect-ajax', 'placeholder' => 'Select Company',  'url' => '/select2/companies'];
    $items['static-island'] =     ['label' => 'Island',     'group' => 'Company', 'operator_type' => 'multiselect_ids', 'options' => ['bq-b' => 'Bonaire', 'bq-e' => 'St. Eustatius', 'bq-s' => 'Saba']];
    $items['static-responsible'] =['label' => 'Responsible','group' => 'User',    'operator_type' => 'multiselect_ids', 'placeholder' => trans('fields.select_user'), 'options' => $users];
    $items['static-status'] =     ['label' => 'Status',     'group' => 'ROA',     'operator_type' => 'multiselect_ids', 'options' => ['prospect' => 'Prospect', 'inprogress' => 'In Progress', 'active' => 'Active', 'inactive' => 'Inactive']];
    $items['static-recog_first'] =['label' => 'First Recognition', 'group' => 'ROA', 'operator_type' => 'date'];
    $items['static-recog_last']  =['label' => 'Last Recognition', 'group' => 'ROA', 'operator_type' => 'date'];
    $items['dynamic-recog_obtained']  =['label' => 'Obtained a Recognition', 'group' => 'ROA', 'operator_type' => 'date'];
    $items['static-recog_count'] =['label' => 'Total Recognition', 'group' => 'ROA', 'operator_type' => 'number'];
    $items['static-has_relations'] =    ['label' => 'Has Relations', 'group' => 'Flags', 'operator_type' => 'boolean'];
    $items['static-has_bpv'] =          ['label' => 'Has BPV',       'group' => 'Flags', 'operator_type' => 'boolean'];
    $items['static-has_bpv_sig'] =      ['label' => 'Has BPV Sig.',  'group' => 'Flags', 'operator_type' => 'boolean'];
    $items['static-has_job_trainers'] = ['label' => 'Has Job Trainers', 'group' => 'Flags', 'operator_type' => 'boolean'];
    $items['static-has_recognitions'] = ['label' => 'Has Recognition',  'group' => 'Flags', 'operator_type' => 'boolean'];
    $items['static-has_plaque'] =       ['label' => 'Has Plaque',       'group' => 'Flags', 'operator_type' => 'boolean'];
    $items['dynamic-dossier_id'] =     ['label' => 'Kwalificatie Dossier','group' => 'Recognitions', 'operator_type' => 'multiselect_ids', 'options' => $dossiers];
    $items['dynamic-department_id'] =  ['label' => 'Opleidingsdomein','group' => 'Recognitions', 'operator_type' => 'multiselect_ids', 'options' => $departments];
    $items['dynamic-recognition_id'] = ['label' => 'Recognition','group' => 'Recognitions', 'operator_type' => 'multiselect-ajax', 'placeholder' => 'Select Recognition',  'url' => '/roa/recognition/select2'];

    // Filter Config
    $filter = [];
    $filter['item_type'] = $data['item_type'];
    $filter['fields'] = $items;
    $filter['configs'] = [];
    $data['filterconfig'] = json_encode($filter);

    //----------------------------------------
    // Saved Searches
    //----------------------------------------
    $data['saved_searches'] = json_encode(SavedSearch::prepareSearches($data['item_type']));

    return view('roa::companies.index')->with($data);
}
