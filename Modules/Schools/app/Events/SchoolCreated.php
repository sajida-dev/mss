<?php

namespace Modules\Schools\App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Schools\App\Models\School;

class SchoolCreated
{
    use Dispatchable, SerializesModels;

    public $school;

    /**
     * Create a new event instance.
     *
     * @param  \Modules\Schools\App\Models\School  $school
     * @return void
     */
    public function __construct(School $school)
    {
        $this->school = $school;
    }
}
