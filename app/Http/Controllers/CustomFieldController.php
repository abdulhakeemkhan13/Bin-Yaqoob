<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use App\Models\Utility;
use Illuminate\Http\Request;

class CustomFieldController extends Controller
{
    public function __construct()
    {

    }

    public function index(Request $request)
    {
        // dd($request->all());
        if (\Auth::user()->can('manage constant custom field')) {
            $custom_fields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->get();
            $modules = CustomField::$modules;
            if (!empty($request->module)) {
                $custom_fields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', $request->module)->get();
            }
            return view('customFields.index', compact('custom_fields', 'modules'));
        } else {
            response()->json(['error' => __('Permission Denied.')], 401);
        }
    }


    public function create()
    {
        if (\Auth::user()->can('create constant custom field')) {
            $types = CustomField::$fieldTypes;
            $is_required = CustomField::$is_required;
            $modules = CustomField::$modules;

            return view('customFields.create', compact('types', 'modules', 'is_required'));
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }


    public function store(Request $request)
    {
        \DB::beginTransaction();
        try {
        if (\Auth::user()->can('create constant custom field')) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required|max:40',
                    'type' => 'required',
                    'module' => 'required',
                    'is_required' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return response()->json(['error' => $messages->first()], 400);
            }

            $custom_field = new CustomField();
            $custom_field->name = $request->name;
            $custom_field->type = $request->type;
            $custom_field->module = $request->module;
            $custom_field->is_required = $request->is_required;
            $custom_field->values = $request->values;
            $custom_field->created_by = \Auth::user()->creatorId();
            $custom_field->save();
            \DB::commit();
            $html = view('customFields.appendrow', compact('custom_field'))->render();
            $data = [
                'datarow' => $html,
                'table_id' => "custom-field-table",
                'action' => 'add',
                'row_id' => $custom_field->id,
            ];
            return response()->json(['success' => true, 'message' => __('Custom Field successfully created.'), 'data' => $data]);
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['error' => __('An error occurred.'), 'exception' => $e->getMessage()], 500);
        }
    }


    public function show(CustomField $customField)
    {
        return redirect()->route('custom-field.index');
    }

    public function edit(CustomField $customField)
    {
        if (\Auth::user()->can('edit constant custom field')) {
            if ($customField->created_by == \Auth::user()->creatorId()) {
                $types = CustomField::$fieldTypes;
                $is_required = CustomField::$is_required;
                $modules = CustomField::$modules;

                return view('customFields.edit', compact('customField', 'types', 'modules', 'is_required'));
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }


    public function update(Request $request, CustomField $customField)
    {
        \DB::beginTransaction();
        try {
            if (\Auth::user()->can('edit constant custom field')) {

                if ($customField->created_by == \Auth::user()->creatorId()) {

                    $validator = \Validator::make(
                        $request->all(),
                        [
                            'name' => 'required|max:40',
                        ]
                    );

                    if ($validator->fails()) {
                        $messages = $validator->getMessageBag();

                        return redirect()->route('custom-field.index')->with('error', $messages->first());
                    }

                    $customField->name = $request->name;
                    $customField->is_required = $request->is_required;
                    $customField->save();

                    \DB::commit();
                    \App\Models\Utility::makeActivityLog(\Auth::user()->id, 'Custom Field', $customField->id, 'Update Custom Field', $customField->name);
                    
                    // Make sure we're using the correct variable name
                    $custom_field = $customField; // Add this line to ensure variable name consistency
                    $html = view('customFields.appendrow', compact('custom_field'))->render();
                    
                    $data = [
                        'datarow' => $html,
                        'table_id' => "custom-field-table",
                        'action' => 'edit',
                        'row_id' => $customField->id,
                    ];
                    return response()->json(['success' => true, 'message' => __('Custom Field successfully updated.'), 'data' => $data]);

                } else {
                    return response()->json(['error' => __('Permission Denied.')], 401);
                }
            } else {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['error' => __('An error occurred.'), 'exception' => $e->getMessage()], 500);
        }
    }


    public function destroy(CustomField $customField)
    {
        if (\Auth::user()->can('delete constant custom field')) {
            if ($customField->created_by == \Auth::user()->creatorId()) {
                $customField->delete();

                return redirect()->route('custom-field.index')->with('success', __('Custom Field successfully deleted!'));
            } else {
                response()->json(['error' => __('Permission Denied.')], 401);
            }
        } else {
            response()->json(['error' => __('Permission Denied.')], 401);
        }
    }
    public function getFieldOptions(Request $request,$id)
    {
        $field = CustomField::find($id);

        if ($field) {
            $options = explode(',', $field->values);
            return response()->json(['success' => true, 'options' => $options]);
        }

        return response()->json(['success' => false, 'message' => 'Field not found.']);
    }

}
