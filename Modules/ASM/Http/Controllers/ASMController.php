<?php

namespace Modules\ASM\Http\Controllers;

use App\Models\Module;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use Modules\ASM\Entities\ASM;
use Modules\Location\Entities\District;
use App\Http\Controllers\BaseController;
use Modules\ASM\Http\Requests\ASMFormRequest;

class ASMController extends BaseController
{
    use UploadAble;
    public function __construct(ASM $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('asm-access')){
            $this->setPageData('ASM','ASM','fas fa-user-shield',[['name' => 'ASM']]);
            $data = [
                'districts' => District::where(['type'=>1,'status'=>1])->get(),
            ];
            return view('asm::index',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if (!empty($request->name)) {
                $this->model->setName($request->name);
            }
            // if (!empty($request->username)) {
            //     $this->model->setUsername($request->username);
            // }
            if (!empty($request->phone)) {
                $this->model->setPhone($request->phone);
            }
            if (!empty($request->email)) {
                $this->model->setEmail($request->email);
            }
            if (!empty($request->district_id)) {
                $this->model->setDistrictID($request->district_id);
            }
            if (!empty($request->status)) {
                $this->model->setStatus($request->status);
            }

            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();//get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $action = '';
                if(permission('asm-edit')){
                $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['Edit'].'</a>';
                }
                if(permission('asm-view')){
                $action .= ' <a class="dropdown-item view_data" data-id="' . $value->id . '" data-name="' . $value->name . '">'.self::ACTION_BUTTON['View'].'</a>';
                }
                // if(permission('asm-permission')){
                // $action .= ' <a class="dropdown-item" href="'.route('asm.permission',['id'=>$value->id]).'"><i class="fas fa-tasks text-success mr-2"></i> Permission</a>';
                // }
                if(permission('asm-delete')){
                    $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                }


                $row = [];
                if(permission('asm-bulk-delete')){
                    $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                }
                $row[] = $no;
                $row[] = $this->table_image(ASM_AVATAR_PATH,$value->avatar,$value->name,1);
                $row[] = $value->name;
                // $row[] = $value->username;
                $row[] = number_format($value->monthly_target_value,2,'.','');
                $row[] = $value->warehouse->name;
                $row[] = $value->district->name;
                $row[] = $value->phone;
                $row[] = $value->email ? $value->email : '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">No Email</span>';
                $row[] = permission('asm-edit') ? change_status($value->id,$value->status, $value->name) : STATUS_LABEL[$value->status];
                $row[] = action_button($action);//custom helper function for action button
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
             $this->model->count_filtered(), $data);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function store_or_update_data(ASMFormRequest $request)
    {
        if($request->ajax()){
            if(permission('asm-add') || permission('asm-edit')){

                $collection   = collect($request->validated())->except('password','password_confirmation');
                $collection   = $this->track_data($collection,$request->update_id);
                $avatar = !empty($request->old_avatar) ? $request->old_avatar : null;
                if($request->hasFile('avatar')){
                    $avatar  = $this->upload_file($request->file('avatar'),ASM_AVATAR_PATH);
                    if(!empty($request->old_avatar)){
                        $this->delete_file($request->old_avatar, ASM_AVATAR_PATH);
                    }  
                }
                $collection        = $collection->merge(compact('avatar'));
                // if(!empty($request->password)){
                //     $collection   = $collection->merge(['password'=>$request->password]);
                // }
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
            if(permission('asm-edit')){
                $data   = $this->model->findOrFail($request->id);
                $output = $this->data_message($data); //if data found then it will return data otherwise return error message
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function show(Request $request)
    {
        if($request->ajax()){
            if(permission('asm-view')){
                $asm   = $this->model->with('warehouse','district')->findOrFail($request->id);
                return view('asm::view-data',compact('asm'))->render();
            }
        }
    }

    public function delete(Request $request)
    {
        if($request->ajax()){
            if(permission('asm-delete')){
                $result   = $this->model->find($request->id)->delete();
                $output   = $this->delete_message($result);
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function bulk_delete(Request $request)
    {
        if($request->ajax()){
            if(permission('asm-bulk-delete')){
                $result   = $this->model->destroy($request->ids);
                $output   = $this->bulk_delete_message($result);
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function change_status(Request $request)
    {
        if($request->ajax()){
            if(permission('asm-edit')){
                $result   = $this->model->find($request->id)->update(['status' => $request->status]);
                $output   = $result ? ['status' => 'success','message' => 'Status Has Been Changed Successfully']
                : ['status' => 'error','message' => 'Failed To Change Status'];
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function district_id_wise_asm_list(int $id)
    {
        $asms = $this->model->district_id_wise_asm_list($id);
        return json_encode($asms);
    }

    public function permission(int $id)
    {
        if(permission('asm-permission')){
            $this->setPageData('ASM Permission','ASM Permission','fas fa-tasks',[['name' => 'ASM Permission']]);
            $asm = $this->model->with('module_asm','permission_asm')->find($id);
            $asm_module = [];
            if(!$asm->module_asm->isEmpty())
            {
                foreach ($asm->module_asm as $value) {
                    array_push($asm_module,$value->id);
                }
            }
            $asm_permission = [];
            if(!$asm->permission_asm->isEmpty())
            {
                foreach ($asm->permission_asm as $value) {
                    array_push($asm_permission,$value->id);
                }
            }

            $data = [
                'asm'                => $asm,
                'asm_module'         => $asm_module,
                'asm_permission'     => $asm_permission,
                'permission_modules' => Module::permission_module_list(2)
            ];
            
            return view('asm::permission',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function permission_store(Request $request)
    {
        if($request->ajax()){
            if(permission('asm-permission')){
                $asm = $this->model->with('module_asm','permission_asm')->find($request->asm_id);
                if($asm){
                    $asm->module_asm()->sync($request->module);
                    $asm->permission_asm()->sync($request->permission);
                }
                $output = $this->store_message($asm, $request->asm_id);
            }else{
                $output = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
