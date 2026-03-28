<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class StudentHasNoParent implements Rule
{
    protected $errorMessage;
    protected $parentId;

    /**
     * Create a new rule instance.
     *
     * @param string $errorMessage
     * @param int|null $parentId
     */
    public function __construct($errorMessage, $parentId = null)
    {
        $this->errorMessage = $errorMessage;
        $this->parentId = $parentId;
    }

    /**
     * Check if any student in the list has parent_id
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // If there is no student_ids then pass
        if (empty($value)) {
            return true;
        }

        // Get all students with parent_id in the list
        $query = DB::table('students')
            ->whereIn('id', $value)
            ->whereNotNull('parent_id');

        // If parentId is provided, exclude students that belong to this parent
        if ($this->parentId) {
            $query->where('parent_id', '!=', $this->parentId);
        }

        $studentsWithParent = $query->pluck('id')->toArray();

        // If any student has parent_id then fail
        return empty($studentsWithParent);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->errorMessage;
    }
}
