<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VariableVersion extends Model
{
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * @param string $value
     * @return mixed|string
     */
    public function getValueAttribute($value)
    {
        return decrypt($value);
    }

    /**
     * @param string $value
     */
    public function setValueAttribute($value)
    {
        $this->attributes['value'] = encrypt($value);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function variable()
    {
        return $this->belongsTo(Variable::class);
    }
}
