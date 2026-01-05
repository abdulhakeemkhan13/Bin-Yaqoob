<?php

namespace App\Http\Controllers;

use App\Models\ReminderType;
use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ReminderTypeController extends Controller
{
    public function index()
    {
        // Gate: list types (optional)
        // $this->authorize('viewAny', ReminderType::class);

        $types = ReminderType::orderBy('name')->get();

        return view('reminders.indexType', compact('types'));
    }

    // Return ONLY the modal body for your data-ajax-popup
    public function create()
    {
        // $this->authorize('create', ReminderType::class);
        return view('reminders.createType');
    }

    public function store(Request $request)
    {
        // $this->authorize('create', ReminderType::class);

        $data = $request->validate([
            'name' => 'required|string|max:120|unique:reminder_types,name',
        ]);

        $type = ReminderType::create([
            'name'       => $data['name'],
            'slug'       => Str::slug($data['name']),
            // If you keep ownership:
            // 'created_by' => Auth::user()->creatorId() ?? Auth::id(),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'id'      => $type->id,
                'name'    => $type->name,
            ]);
        }

        return redirect()->route('reminders.indexType')->with('success', __('Type created.'));
    }

    // Return ONLY the modal body for your data-ajax-popup
    public function edit(ReminderType $reminder_type)
    {
        // $this->authorize('update', $reminder_type);
        return view('reminders.editType', ['type' => $reminder_type]);
    }

    public function update(Request $request, ReminderType $reminder_type)
    {
        // $this->authorize('update', $reminder_type);

        $data = $request->validate([
            'name' => 'required|string|max:120|unique:reminder_types,name,' . $reminder_type->id,
        ]);

        $reminder_type->name = $data['name'];
        $reminder_type->slug = Str::slug($data['name']);
        $reminder_type->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'id'      => $reminder_type->id,
                'name'    => $reminder_type->name,
            ]);
        }

        return redirect()->route('reminder-types.index')->with('success', __('Type updated.'));
    }

    public function destroy(ReminderType $reminder_type)
    {
        // Match your Tax delete pattern:
        if (!Auth::user()->can('delete reminder type')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        // If you track ownership:
        // if (($reminder_type->created_by ?? Auth::id()) !== (Auth::user()->creatorId() ?? Auth::id())) {
        //     return redirect()->back()->with('error', __('Permission denied.'));
        // }

        // Prevent delete if used by Reminders
        $inUse = Reminder::where('reminder_type_id', $reminder_type->id)->exists();
        if ($inUse) {
            return redirect()->back()->with('error', __('This type is already used by one or more reminders. Please move or remove those reminders first.'));
        }

        // Optional activity log (if you have Utility::makeActivityLog)
        if (class_exists(\App\Models\Utility::class) && method_exists(\App\Models\Utility::class, 'makeActivityLog')) {
            \App\Models\Utility::makeActivityLog(
                Auth::id(), 'Reminder Type', $reminder_type->id, 'Delete Reminder Type', $reminder_type->name
            );
        }

        $reminder_type->delete();

        return redirect()->route('reminder-types.index')->with('success', __('Type successfully deleted.'));
    }
}
