<?php

namespace Lia\Media\Database;

use Illuminate\Database\Eloquent\Model;

class LiaMedia extends Model
{
    protected $table = 'lia_media';

    protected $fillable = [];

    public function __construct($attributes = [])
    {
        $fills = [];
        foreach(config('lia-media.markers') as $key => $data)
            $fills[] = $key;

        $fillable = array_merge(['relate_id', 'type', 'preview', 'data', 'title', 'description', 'active'], $fills);
        $this->fillable($fillable);

        parent::__construct($attributes);
    }
}
