<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Employee;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    public function index()
    {
        if(\Auth::user()->can('manage assets'))
        {
            $user = \Auth::user();
            if ($user->type == 'company') {
                $assets = Asset::where('created_by', '=', \Auth::user()->creatorId())->get();
            } else {
                $assets = Asset::where('owned_by', '=', \Auth::user()->ownedId())->get();
            }
            
            // Define the profile variable needed in the view
            $profile = Utility::getValByName('profile');
            
            return view('assets.index', compact('assets', 'profile'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        if(\Auth::user()->can('create assets'))
        {
            if (\Auth::user()->type == 'company') {
                $employee = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'user_id');
            } else {
                $employee = Employee::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'user_id');
            }
            return view('assets.create', compact('employee'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            if(!\Auth::user()->can('create assets')) {
                if($request->ajax()) {
                    return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
                }
                return redirect()->back()->with('error', __('Permission denied.'));
            }

            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required',
                    'purchase_date' => 'required',
                    'supported_date' => 'required',
                    'amount' => 'required',
                ]
            );
            
            if($validator->fails()) {
                $messages = $validator->getMessageBag();
                if($request->ajax()) {
                    DB::rollback();
                    return response()->json(['success' => false, 'message' => $messages->first()], 422);
                }
                return redirect()->back()->with('error', $messages->first());
            }

            $assets = new Asset();
            $assets->employee_id = !empty($request->employee_id) ? implode(',', $request->employee_id) : '';
            $assets->name = $request->name;
            $assets->purchase_date = $request->purchase_date;
            $assets->supported_date = $request->supported_date;
            $assets->amount = $request->amount;
            $assets->description = $request->description;
            $assets->created_by = \Auth::user()->creatorId();
            $assets->owned_by = \Auth::user()->ownedId();
            $assets->save(); 
            
            DB::commit();
            
            // Log activity
            Utility::makeActivityLog(\Auth::user()->id, 'Asset', $assets->id, 'Create Asset', $assets->name);
            
            if($request->ajax()) {
                // Reload the asset with any relationships needed for the view
                $asset = Asset::find($assets->id);
                
                // Define the profile variable needed in the view
                $profile = Utility::getValByName('profile');
                
                // Render the HTML for the new row
                $html = view('assets.appendrow', compact('asset', 'profile'))->render();
                
                $data = [
                    'datarow' => $html,
                    'table_id' => "asset-table",
                    'action' => 'add',
                    'row_id' => $assets->id,
                ];
                
                return response()->json([
                    'success' => true, 
                    'message' => __('Assets successfully created.'), 
                    'data' => $data
                ]);
            }
            
            return redirect()->route('account-assets.index')->with('success', __('Assets successfully created.'));
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Asset creation error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            if($request->ajax()) {
                return response()->json([
                    'success' => false, 
                    'message' => __('An error occurred: ') . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', __('An error occurred: ') . $e->getMessage());
        }
    }

    public function show(Asset $asset)
    {
        //
    }


    public function edit($id)
    {
        if(\Auth::user()->can('edit assets'))
        {
            $asset = Asset::find($id);
            if (\Auth::user()->type == 'company') {
                $employee = Employee::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            } else {
                $employee = Employee::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            }
            
            $asset->employee_id = explode(',', $asset->employee_id);

            return view('assets.edit', compact('asset', 'employee'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            if(!\Auth::user()->can('edit assets')) {
                if($request->ajax()) {
                    return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
                }
                return redirect()->back()->with('error', __('Permission denied.'));
            }
            
            $asset = Asset::find($id);
            if(!$asset || $asset->created_by != \Auth::user()->creatorId()) {
                if($request->ajax()) {
                    return response()->json(['success' => false, 'message' => __('Permission denied or asset not found.')], 404);
                }
                return redirect()->back()->with('error', __('Permission denied or asset not found.'));
            }
            
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required',
                    'purchase_date' => 'required',
                    'supported_date' => 'required',
                    'amount' => 'required',
                ]
            );
            
            if($validator->fails()) {
                $messages = $validator->getMessageBag();
                if($request->ajax()) {
                    DB::rollback();
                    return response()->json(['success' => false, 'message' => $messages->first()], 422);
                }
                return redirect()->back()->with('error', $messages->first());
            }

            $asset->name = $request->name;
            $asset->employee_id = !empty($request->employee_id) ? implode(',', $request->employee_id) : '';
            $asset->purchase_date = $request->purchase_date;
            $asset->supported_date = $request->supported_date;
            $asset->amount = $request->amount;
            $asset->description = $request->description;
            $asset->save();
            
            DB::commit();
            
            // Log activity
            Utility::makeActivityLog(\Auth::user()->id, 'Asset', $asset->id, 'Update Asset', $asset->name);
            
            if($request->ajax()) {
                // Define the profile variable needed in the view
                $profile = Utility::getValByName('profile');
                
                // Render the HTML for the updated row
                $html = view('assets.appendrow', compact('asset', 'profile'))->render();
                
                $data = [
                    'datarow' => $html,
                    'table_id' => "asset-table",
                    'action' => 'edit',
                    'row_id' => $asset->id,
                ];
                
                return response()->json([
                    'success' => true, 
                    'message' => __('Assets successfully updated.'), 
                    'data' => $data
                ]);
            }
            
            return redirect()->route('account-assets.index')->with('success', __('Assets successfully updated.'));
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Asset update error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            if($request->ajax()) {
                return response()->json([
                    'success' => false, 
                    'message' => __('An error occurred: ') . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', __('An error occurred: ') . $e->getMessage());
        }
    }


    public function destroy($id)
    {
        if(\Auth::user()->can('delete assets'))
        {
            $asset = Asset::find($id);
            if($asset && $asset->created_by == \Auth::user()->creatorId())
            {
                Utility::makeActivityLog(\Auth::user()->id, 'Asset', $asset->id, 'Delete Asset', $asset->name);
                $asset->delete();
                return redirect()->route('account-assets.index')->with('success', __('Assets successfully deleted.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied or asset not found.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
