<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class StudentClassExists implements Rule
{
    protected $classId;
    protected $errorMessage;
    /**
     * Create a new rule instance.
     *
     * @param string $classId
     */
    public function __construct($classId, $errorMessage)
    {
        $this->classId = $classId;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Check if student class exists
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return DB::table('students_of_class')
            ->where('student_id', $value)
            ->where('class_id', $this->classId)
            ->exists();
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
