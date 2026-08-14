<?php
namespace App\Http\Traits;

use Illuminate\Http\Request;

trait Crud{

    public function index(){
        $model = $this->model;
        $data = $model::get();
        return view($this->folder.'.'.strtolower($this->title),compact('data'));
    }

    public function store(Request $request)
	{
        $model = $this->model;
        $except = $this->exceptStore;
        $validationExcept = $except;
        if(@$this->nullables){
            $validationExcept = array_merge($except, @$this->nullables);
        }
        $req = $request->except($except);
        $reqValidation = $request->except($validationExcept);
        if(!@$this->storeValidation){
            $validation = [];
            foreach($reqValidation as $k => $value){
                $validation[$k] = 'required';
            }
            $request->validate($validation);
        }else{
            $this->storeValidation($request);
        }

        $model = $model::create($req);
        if(@$this->hash){
            $hashField = $this->hash;
            $model->$hashField = Hash::make($req[$hashField]);
            $model->save();
        }

        $alert = ['success',$this->title.' created successfully'];
        return back()->withAlert($alert);
	}

	public function update(Request $request, $identifier)
	{
        $model = $this->model;
        $except = $this->exceptStore;
        $validationExcept = $except;
        if(@$this->nullables){
            $validationExcept = array_merge($except, @$this->nullables);
        }
        $req = $request->except($except);
        $reqValidation = $request->except($validationExcept);
        if(!@$this->storeValidation){
            $validation = [];
            foreach($reqValidation as $k => $value){
                $validation[$k] = 'required';
            }
            $request->validate($validation);
        }else{
            $this->updateValidation($request);
        }
        $model = $model::where($this->identifier,$identifier)->update($req);
        $alert = ['success',$this->title.' updated successfully'];
        return back()->withAlert($alert);
	}

	public function status($identifier)
	{
        $model = $this->model;
        $model = $model::where($this->identifier,$identifier)->firstOrFail();
        if($model->status == 1){
            $model->status = 0;
        }else{
            $model->status = 1;
        }
        $model->save();
        $alert = ['success',$this->title.' status changed successfully'];
        return back()->withAlert($alert);
	}

}
