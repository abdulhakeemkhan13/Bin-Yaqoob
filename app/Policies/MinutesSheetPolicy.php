<?php

namespace App\Policies;

use App\Models\MinutesSheet;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Auth\Access\HandlesAuthorization;

class MinutesSheetPolicy
{
    use HandlesAuthorization;

    /**
     * Get the user's designation from their employee record
     */
    protected function getUserDesignation(User $user): ?string
    {
        $employee = Employee::where('user_id', $user->id)->first();
        if ($employee && $employee->designation_id) {
            $designation = \App\Models\Designation::find($employee->designation_id);
            return $designation ? $designation->name : null;
        }
        return null;
    }

    /**
     * Determine whether the user can view any minutes sheets.
     */
    public function viewAny(User $user): bool
    {
        $designation = $this->getUserDesignation($user);
        return $designation && in_array($designation, MinutesSheet::APPROVAL_CHAIN);
    }

    /**
     * Determine whether the user can view the minutes sheet.
     */
    public function view(User $user, MinutesSheet $minutesSheet): bool
    {
        $designation = $this->getUserDesignation($user);
        
        if (!$designation) {
            return false;
        }

        // Creator can always view their own
        if ($minutesSheet->created_by === $user->id) {
            return true;
        }

        return $minutesSheet->canBeViewedBy($designation);
    }

    /**
     * Determine whether the user can create minutes sheets.
     */
    public function create(User $user): bool
    {
        $designation = $this->getUserDesignation($user);
        return $designation && MinutesSheet::canCreate($designation);
    }

    /**
     * Determine whether the user can update the minutes sheet.
     */
    public function update(User $user, MinutesSheet $minutesSheet): bool
    {
        // Only creator can update, and only when pending
        return $minutesSheet->created_by === $user->id 
            && $minutesSheet->status === 'pending'
            && $minutesSheet->designation === $minutesSheet->current_stage; // Not yet forwarded to next stage
    }

    /**
     * Determine whether the user can delete the minutes sheet.
     */
    public function delete(User $user, MinutesSheet $minutesSheet): bool
    {
        // Only creator can delete, and only when pending at first stage
        return $minutesSheet->created_by === $user->id 
            && $minutesSheet->status === 'pending'
            && $minutesSheet->designation === $minutesSheet->current_stage;
    }

    /**
     * Determine whether the user can approve the minutes sheet.
     */
    public function approve(User $user, MinutesSheet $minutesSheet): bool
    {
        $designation = $this->getUserDesignation($user);
        return $designation && $minutesSheet->isCurrentApprover($designation);
    }

    /**
     * Determine whether the user can reject the minutes sheet.
     */
    public function reject(User $user, MinutesSheet $minutesSheet): bool
    {
        $designation = $this->getUserDesignation($user);
        return $designation && $minutesSheet->isCurrentApprover($designation);
    }
}
