<?php

namespace Modules\Location\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Location\Entities\District;
use App\Http\Controllers\BaseController;
use Modules\Location\Http\Requests\LocationFormRequest;

class DistrictController extends BaseController
{
    public function __construct(District $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('district-access')){
            $this->setPageData('District','District','fas fa-map-marker-alt',[['name' => 'Location'],['name'=>'District']]);
            return view('location::district.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if (!empty($request->district_name)) {
                $this->model->setName($request->district_name);
            }

            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();//get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $action = '';
                if(permission('district-edit')){
                $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['Edit'].'</a>';
                }
                if(permission('district-delete')){
                    $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                }

                $row = [];
                if(permission('district-bulk-delete')){
                    $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                }
                $row[] = $no;
                $row[] = $value->name;
                $row[] = permission('district-edit') ? change_status($value->id,$value->status, $value->name) : STATUS_LABEL[$value->status];
                $row[] = action_button($action);//custom helper function for action button
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
             $this->model->count_filtered(), $data);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function store_or_update_data(LocationFormRequest $request)
    {
        if($request->ajax()){
            if (permission('district-add') || permission('district-edit')){
                $collection   = collect($request->validated());
                $collection = $collection->merge([
                    'parent_id' => 0,
                ]);
                $collection   = $this->track_data($collection,$request->update_id);
                $result       = $this->model->updateOrCreate(['id'=>$request->update_id],$collection->all());
                $output       = $this->store_message($result, $request->update_id);
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function edit(Request $request)
    {
        if($request->ajax()){
            if(permission('district-edit')){
                $data   = $this->model->findOrFail($request->id);
                $output = $this->data_message($data); //if data found then it will return data otherwise return error message
            }else{
                $output = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function delete(Request $request)
    {
        if($request->ajax()){
            if(permission('district-delete')){
                $district = $this->model->with('upazilas','routes')->find($request->id);
                if (!$district->upazilas->isEmpty() || !$district->routes->isEmpty()) {
                    $output = ['status'=>'error','message'=>'This district can\'t delete beacuse it\'s related with upazilas data'];
                } else {
                    $result = $district->delete();
                    $output = $this->delete_message($result);
                }
            }else{
                $output = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function bulk_delete(Request $request)
    {
        if($request->ajax()){
            if(permission('district-bulk-delete')){
                $delete_list = [];
                $undelete_list = [];
                foreach ($request->ids as $id) {
                    $district = $this->model->with('upazilas','routes')->find($id);
                    if(!$district->upazilas->isEmpty() || !$district->routes->isEmpty()){
                        array_push($undelete_list,$district->name);
                    }else{
                        array_push($delete_list,$id);
                    }
                } 
                if(!empty($delete_list)){
                        $result = $district->destroy($delete_list);
                        $output = $result ?  ['status'=>'success','message'=> 'Selected Data Has Been Deleted Successfully. '. (!empty($undelete_list) ? 'Except these menus('.implode(',',$undelete_list).')'.' because they are associated with others data.' : '')] 
                        : ['status'=>'error','message'=>'Failed To Delete Data.'];
                }else{
                    $output = ['status'=>'error','message'=> !empty($undelete_list) ? 'These districts('.implode(',',$undelete_list).')'.' 
                    can\'t delete because they are associated with others data.' : ''];
                }
            }else{
                $output = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function change_status(Request $request)
    {
        if($request->ajax()){
            if(permission('district-edit')){
                $result   = $this->model->find($request->id)->update(['status' => $request->status]);
                $output   = $result ? ['status' => 'success','message' => 'Status Has Been Changed Successfully']
                : ['status' => 'error','message' => 'Failed To Change Status'];
            }else{
                $output   = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
