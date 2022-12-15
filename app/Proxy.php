<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Proxy extends Model
{
    protected $guarded = [];

    protected $appends = ['proxy', 'userpwd'];

    public function getProxyAttribute() {
        return $this->address . ':' . $this->port;
    }

    public function getUserpwdAttribute() {
        return $this->username . ':' . $this->password;
    }
}
