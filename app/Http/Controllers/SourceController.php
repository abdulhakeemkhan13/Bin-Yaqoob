<?php

namespace App\Http\Controllers;

use App\Models\Source;
use App\Models\Utility;
use Illuminate\Http\Request;

class SourceController extends Controller
{
    public function __construct()
    {
        $this->middleware(
            [
                'auth',
                'XSS',
            ]
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(\Auth::user()->can('manage source'))
        {
            if (\Auth::user()->type == 'company') {
                $sources = Source::where('created_by', '=', \Auth::user()->ownerId())->get();
            } else {
                $sources = Source::where('owned_by', '=', \Auth::user()->ownedId())->get();
            }
            return view('sources.index')->with('sources', $sources);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(\Auth::user()->can('create source'))
        {
            return view('sources.create');
        }
        else
        {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        \DB::beginTransaction();
        try {
        if(\Auth::user()->can('create source'))
        {

            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required|max:20',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->route('sources.index')->with('error', $messages->first());
            }

            $source             = new Source();
            $source->name       = $request->name;
            $source->created_by = \Auth::user()->ownerId();
            $source->owned_by = \Auth::user()->ownedId();
            $source->save();
            \DB::commit();
            Utility::makeActivityLog(\Auth::user()->id,'Source',$source->id,'Create Source',$source->name);
            $html = view('sources.appendrow', compact('source'))->render();
            $data = [
                'datarow' => $html,
                'table_id' => "source-table",
                'action' => 'add',
                'row_id' => $source->id,
            ];
            return response()->json(['success' => true, 'message' => __('Source successfully created.'), 'data' => $data]);
        }
        else
        {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Source $source
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Source $source)
    {
        return redirect()->route('sources.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Source $source
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Source $source)
    {
        if(\Auth::user()->can('edit source'))
        {
            if($source->created_by == \Auth::user()->ownerId())
            {
                return view('sources.edit', compact('source'));
            }
            else
            {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        }
        else
        {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Source $source
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Source $source)
    {
        \DB::beginTransaction();
        try {
        if(\Auth::user()->can('edit source'))
        {
            if($source->created_by == \Auth::user()->ownerId())
            {
                $validator = \Validator::make(
                    $request->all(), [
                                       'name' => 'required|max:20',
                                   ]
                );

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return response()->json(['error' => $messages->first()], 422);
                }

                $source->name = $request->name;
                $source->save();
                \DB::commit();
                Utility::makeActivityLog(\Auth::user()->id,'Source',$source->id,'Update Source',$source->name);
                $html = view('sources.appendrow', compact('source'))->render();
                $data = [
                    'datarow' => $html,
                    'table_id' => "source-table",
                    'action' => 'edit',
                    'row_id' => $source->id,
                ];
                return response()->json(['success' => true, 'message' => __('Source successfully updated.'), 'data' => $data]);
            }
            else
            {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        }
        else
        {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Source $source
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Source $source)
    {
        if(\Auth::user()->can('delete source'))
        {
            if($source->created_by == \Auth::user()->ownerId())
            {
                Utility::makeActivityLog(\Auth::user()->id,'Source',$source->id,'Delete Source',$source->name);
                $source->delete();

                return redirect()->route('sources.index')->with('success', __('Source successfully deleted!'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
}
