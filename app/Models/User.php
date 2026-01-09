<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'profile_pic',
        'date_of_birth',
        'phone_number',
        'job_title',
        'rate',
        'overtime_rate',
        'address',
        'gender',
        'team_leader_id',
        'category'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
     public function shifts()
    {
        return $this->hasMany(PersonShift::class, 'user_id');
    }

    public function images()
    {
        return $this->hasMany(TaskImage::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }
    public function taskPerformances()
    {
        return $this->hasMany(TaskPerform::class);
    }
    
    public function teamLeader()
    {
        return $this->belongsTo(User::class, 'team_leader_id');
    }
    
    // Each user may have many members (users assigned under them)
    public function teamMembers()
    {
        return $this->hasMany(User::class, 'team_leader_id');
    }
    
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }
    
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }
    
    public function sentAttachments()
    {
        return $this->hasMany(Attachment::class, 'sender_id');
    }
    
    public function receivedAttachments()
    {
        return $this->hasMany(Attachment::class, 'receiver_id');
    }

}
