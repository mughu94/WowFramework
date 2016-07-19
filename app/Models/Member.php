<?php
    namespace Wow\Models;

    use Wow\Database\Model;

    class Member extends Model {
        protected $database = "DefaultConnection";
        protected $table    = "members";
        protected $pk       = "id";
    }