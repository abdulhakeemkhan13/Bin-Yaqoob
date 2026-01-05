<?php

namespace App\Http\Controllers;

use App\Models\CustomQuestion;
use App\Models\Utility;
use DB;
use Illuminate\Http\Request;

class CustomQuestionController extends Controller
{

    public function index()
    {
        if(\Auth::user()->can('manage custom question'))
        {
            $questions = CustomQuestion::where('created_by', \Auth::user()->creatorId())->get();

            return view('customQuestion.index', compact('questions'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        $is_required = CustomQuestion::$is_required;

        return view('customQuestion.create', compact('is_required'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
        if(\Auth::user()->can('create custom question'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'question' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $question              = new CustomQuestion();
            $question->question    = $request->question;
            $question->is_required = $request->is_required;
            $question->created_by  = \Auth::user()->creatorId();
            $question->save();

            DB::commit();
            Utility::makeActivityLog(\Auth::user()->id, 'Custom Question', $question->id, 'Create Custom Question', $question->question);
            $html = view('customQuestion.appendrow', compact('question'))->render();
            $data = [
                'datarow' => $html,
                'table_id' => "custom-question-table",
                'action' => 'add',
                'row_id' => $question->id,
            ];
            return response()->json(['success' => true, 'message' => __('Question successfully created.'), 'data' => $data]);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show(CustomQuestion $customQuestion)
    {
        //
    }

    public function edit(CustomQuestion $customQuestion)
    {
        $is_required = CustomQuestion::$is_required;
        return view('customQuestion.edit', compact('customQuestion','is_required'));
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            if(\Auth::user()->can('edit custom question')) {
                $validator = \Validator::make(
                    $request->all(), [
                        'question' => 'required',
                        'is_required' => 'required',
                    ]
                );
                if($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $customQuestion = CustomQuestion::find($id);
                $customQuestion->question    = $request->question;
                $customQuestion->is_required = $request->is_required;
                $customQuestion->save();

                DB::commit();
                Utility::makeActivityLog(\Auth::user()->id, 'Custom Question', $customQuestion->id, 'Update Custom Question', $customQuestion->question);
                
                $html = view('customQuestion.appendrow', compact('customQuestion'))->render();
                $data = [
                    'datarow' => $html,
                    'table_id' => "custom-question-table",
                    'action' => 'edit',
                    'row_id' => $customQuestion->id,
                ];
                return response()->json(['success' => true, 'message' => __('Question successfully updated.'), 'data' => $data]);
            }
            else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(CustomQuestion $customQuestion)
    {
        if(\Auth::user()->can('delete custom question'))
        {
            $customQuestion->delete();

            return redirect()->back()->with('success', __('Question successfully deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
