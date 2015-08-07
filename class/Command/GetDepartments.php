<?php
namespace Intern\Command;

class getDepartments {

    public function execute()
    {
        // Get list of departments for the current user
        // If user is a Deity, then get all departments
        if (\Current_User::isDeity()) {
            $departments = \Intern\Department::getDepartmentsAssoc();
        } else {
            $departments = \Intern\Department::getDepartmentsAssocForUsername(\Current_User::getUsername());
        }

        $newDepts = array();

        /*
         * NB: Javascript objects are unordered. When the JSON data is
         * decode, numeric keys may be re-arraged. Making the keys into strings
         * (by pre-pending an underscore) will prevent the re-ordering.
         */
        foreach($departments as $key=>$value){
            $newDepts['_' . $key] = $value;
        }

        echo json_encode($newDepts);
        exit;
    }
}