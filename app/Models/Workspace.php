<?php

namespace App\Models;

use App\Models\Forms\Form;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'icon',
        'user_id',
    ];

    protected $appends = [
        'is_pro',
        'is_enterprise'
    ];

    public function getIsProAttribute()
    {
        if(is_null(config('cashier.key'))){
            return true;    // If no paid plan so TRUE for ALL
        }

        // Make sure at least one owner is pro
        foreach ($this->owners as $owner) {
            if ($owner->is_subscribed) {
                return true;
            }
        }
        return false;
    }

    public function getIsEnterpriseAttribute()
    {
        if(is_null(config('cashier.key'))){
            return true;    // If no paid plan so TRUE for ALL
        }

        foreach ($this->owners as $owner) {
            if ($owner->has_enterprise_subscription) {
                return true;
            }
        }
        return false;
    }

    public function getIsRiskyAttribute()
    {
        // A workspace is risky if all of his users are risky
        foreach ($this->owners as $owner) {
            if (!$owner->is_risky) {
                return false;
            }
        }

        return true;
    }

    public function getSubmissionsCountAttribute()
    {
        $total = 0;
        foreach ($this->forms as $form) {
            $total += $form->submissions_count;
        }

        return $total;
    }

    /**
     * Relationships
     */

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function owners()
    {
        return $this->users()->wherePivot('role', 'admin');
    }

    public function forms()
    {
        return $this->hasMany(Form::class);
    }
}
