<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use App\Models\ReminderType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class RemindersController extends Controller
{
    public function index()
    {
        $reminders = Reminder::with('type')
            ->where('user_id', Auth::id())
            ->orderByDesc('due_date')
            ->get();

        return view('reminders.index', compact('reminders'));
    }

    public function create()
    {
        $types = ReminderType::orderBy('name')->get();
        return view('reminders.create', compact('types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'reminder_type_id' => 'required|exists:reminder_types,id',
            'description'      => 'nullable|string',
            'due_date'         => 'required|date',
            'before_days'      => 'required|integer|min:0|max:365',
        ]);

        $data['user_id'] = Auth::id();
        $data['remind_from_date'] = Carbon::parse($data['due_date'])
            ->subDays((int) $data['before_days'])
            ->toDateString();
        $data['is_completed'] = false;
        $data['completed_at'] = null;

        Reminder::create($data);

        return redirect()->route('reminders.index')->with('success', 'Reminder created.');
    }

    public function edit(Reminder $reminder)
    {
        abort_unless($reminder->user_id === Auth::id(), 403);
        $types = ReminderType::orderBy('name')->get();
        return view('reminders.edit', ['r' => $reminder, 'types' => $types]);
    }

    public function update(Request $request, Reminder $reminder)
    {
        abort_unless($reminder->user_id === Auth::id(), 403);

        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'reminder_type_id' => 'required|exists:reminder_types,id',
            'description'      => 'nullable|string',
            'due_date'         => 'required|date',
            'before_days'      => 'required|integer|min:0|max:365',
            'is_completed'     => 'nullable|boolean',
        ]);

        $data['remind_from_date'] = Carbon::parse($data['due_date'])
            ->subDays((int) $data['before_days'])
            ->toDateString();

        if (!empty($data['is_completed'])) {
            $data['is_completed'] = true;
            $data['completed_at'] = now();
        } else {
            $data['is_completed'] = false;
            $data['completed_at'] = null;
        }

        $reminder->update($data);

        return redirect()->route('reminders.index')->with('success', 'Reminder updated.');
    }

    public function destroy(Reminder $reminder)
    {
        if (!\Auth::user()->can('delete reminders')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($reminder->user_id !== \Auth::id()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if (class_exists(\App\Models\Utility::class) && method_exists(\App\Models\Utility::class, 'makeActivityLog')) {
            \App\Models\Utility::makeActivityLog(
                \Auth::id(),
                'Reminder',
                $reminder->id,
                'Delete Reminder',
                $reminder->title
            );
        }

        $reminder->delete();

        return redirect()
            ->route('reminders.index')
            ->with('success', __('Reminder successfully deleted.'));
    }


    public function show(Reminder $reminder)
    {
        return redirect()->route('reminders.index');
    }
}
