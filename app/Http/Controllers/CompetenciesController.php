<?php

namespace App\Http\Controllers;

use App\Models\Competencies;
use App\Models\PerformanceType;
use Illuminate\Http\Request;

class CompetenciesController extends Controller
{

    public function index()
    {
        if(\Auth::user()->can('Manage Competencies'))
        {
            $competencies = Competencies::where('created_by', \Auth::user()->creatorId())->get();

            return view('competencies.index', compact('competencies'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        $performance     = PerformanceType::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            return view('competencies.create', compact('performance'));

    }


    public function store(Request $request)
    {
        \DB::beginTransaction();
        try {
        if(\Auth::user()->can('Create Competencies'))
        {

            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required',
                                   'type' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $competencies             = new Competencies();
            $competencies->name       = $request->name;
            $competencies->type       = $request->type;
            $competencies->created_by = \Auth::user()->creatorId();
            $competencies->save();
            \DB::commit();

            // return redirect()->route('competencies.index')->with('success', __('Competencies  successfully created.'));
            $html = view('competencies.appendrow', ['competency' => $competencies])->render();
            $data = [
                'datarow' => $html,
                'table_id' => "competencies-table",
                'action' => 'add',
                'row_id' => $competencies->id,
            ];
            return response()->json(['success' => true, 'message' => __('Competencies  successfully created.'), 'data' => $data]);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        } catch (\Exception $e) {
            \DB::rollback();
            return redirect()->back()->with('error', $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        }
    }


    public function show(Competencies $competencies)
    {
        //
    }


    public function edit($id)
    {
        $competencies = Competencies::find($id);
        $performance     = PerformanceType::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        return view('competencies.edit', compact('performance', 'competencies'));

    }


    public function update(Request $request, $id)
    {
        \DB::beginTransaction();
        try {
        if(\Auth::user()->can('Edit Competencies'))
        {

            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required',
                                   'type' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $competencies       = Competencies::find($id);
            $competencies->name = $request->name;
            $competencies->type = $request->type;
            $competencies->save();
            \DB::commit();
            // return redirect()->route('competencies.index')->with('success', __('Competencies  successfully updated.'));
            $html = view('competencies.appendrow', ['competency' => $competencies])->render();
            $data = [
                'datarow' => $html,
                'table_id' => "competencies-table",
                'action' => 'edit',
                'row_id' => $competencies->id,
            ];
            return response()->json(['success' => true, 'message' => __('Competencies  successfully updated.'), 'data' => $data]);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        } catch (\Exception $e) {
            \DB::rollback();
            return redirect()->back()->with('error', $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        }
    }


    public function destroy($id)
    {
        if(\Auth::user()->can('Delete Competencies'))
        {
            $competencies = Competencies::find($id);
            $competencies->delete();

            return redirect()->route('competencies.index')->with('success', __('Competencies  successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
