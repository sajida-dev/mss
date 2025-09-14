<?php

namespace Modules\Schools\App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SchoolDeleted
{
    use Dispatchable, SerializesModels;

    public $schoolId;

    /**
     * Create a new event instance.
     *
     * @param  int  $schoolId
     * @return void
     */
    public function __construct($schoolId)
    {
        $this->schoolId = $schoolId;
    }
}
